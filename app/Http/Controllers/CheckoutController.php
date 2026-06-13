<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\City;
use App\Models\FlashOffer;
use App\Models\PaymentMethod;
use App\Models\ShippingCarrier;
use App\Repositories\CartRepository;
use App\Services\ProductPricingService;
use App\Services\ShippingService;
use App\Services\WalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request, CartRepository $carts, ShippingService $shipping, ProductPricingService $pricing, WalletService $wallets): View
    {
        $cart = $carts->findForUser($request->user()->id)->load(['items.product.images', 'items.product.priceTiers', 'items.variant', 'items.offer']);
        $subtotal = $cart->items->sum(fn ($item) => $item->quantity * (float) $item->unit_price);
        $defaultAddress = $request->user()->addresses()->with('city')->where('is_active', true)->orderByDesc('is_default')->latest()->first();
        $defaultCity = $defaultAddress?->city;
        $freeShipping = $this->freeShippingContext($cart, $pricing, $request->user());

        return view('checkout.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'addresses' => $request->user()->addresses()->with('city')->where('is_active', true)->latest()->get(),
            'cities' => City::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'availableCarriers' => $defaultCity ? $shipping->formatCarriersForCheckout($defaultCity, $cart, $subtotal, $freeShipping['cart'], $freeShipping['products']) : collect(),
            'requiresShipping' => $shipping->requiresShipping($cart),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'wallet' => $wallets->getOrCreateWallet($request->user()),
            'subtotal' => $subtotal,
        ]);
    }

    public function store(CheckoutRequest $request, CreateOrderFromCart $checkout): RedirectResponse
    {
        $receiptPath = $request->hasFile('payment_receipt')
            ? $request->file('payment_receipt')->store('payment-receipts', 'public')
            : null;

        $order = $checkout->handle(new CheckoutData(
            userId: $request->user()->id,
            paymentMethodId: (int) $request->input('payment_method_id'),
            addressMode: $request->input('address_mode'),
            shippingCarrierId: $request->filled('shipping_carrier_id') ? (int) $request->input('shipping_carrier_id') : null,
            userAddressId: $request->filled('user_address_id') ? (int) $request->input('user_address_id') : null,
            shippingCityId: $request->filled('city_id') ? (int) $request->input('city_id') : null,
            temporaryAddress: $request->input('address', []),
            saveAddress: $request->boolean('save_address'),
            addressLabel: $request->input('address_label'),
            billingAddressId: $request->filled('billing_address_id') ? (int) $request->input('billing_address_id') : null,
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
            paymentReceiptPath: $receiptPath,
        ));

        return redirect()->route('checkout.success', $order)->with('status', __('Order created successfully.'));
    }

    public function carriers(Request $request, CartRepository $carts, ShippingService $shipping, ProductPricingService $pricing): JsonResponse
    {
        $data = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
        ]);

        $cart = $carts->findForUser($request->user()->id)->load(['items.product', 'items.product.priceTiers', 'items.offer']);
        $city = City::where('is_active', true)->findOrFail($data['city_id']);
        $subtotal = $cart->items->sum(fn ($item) => $item->quantity * (float) $item->unit_price);
        $freeShipping = $this->freeShippingContext($cart, $pricing, $request->user());

        return response()->json([
            'requires_shipping' => $shipping->requiresShipping($cart),
            'carriers' => $shipping->formatCarriersForCheckout($city, $cart, $subtotal, $freeShipping['cart'], $freeShipping['products']),
        ]);
    }

    public function quote(Request $request, CartRepository $carts, ShippingService $shipping, ProductPricingService $pricing): JsonResponse
    {
        $data = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'shipping_carrier_id' => ['nullable', 'exists:shipping_carriers,id'],
        ]);

        $cart = $carts->findForUser($request->user()->id)->load(['items.product', 'items.product.priceTiers', 'items.offer']);
        $city = City::where('is_active', true)->findOrFail($data['city_id']);
        $subtotal = $cart->items->sum(fn ($item) => $item->quantity * (float) $item->unit_price);
        $freeShipping = $this->freeShippingContext($cart, $pricing, $request->user());

        if (! $shipping->requiresShipping($cart)) {
            return response()->json([
                'cost' => 0,
                'weight' => 0,
                'is_free_shipping' => true,
                'no_shipping_required' => true,
                'free_shipping_reason' => 'no_shipping_required',
            ]);
        }

        $carrier = ShippingCarrier::where('status', ShippingCarrier::STATUS_ACTIVE)->findOrFail($data['shipping_carrier_id']);

        return response()->json($shipping->calculateShippingCost($city, $carrier, $cart, $subtotal, $freeShipping['cart'], $freeShipping['products']));
    }

    private function freeShippingContext($cart, ProductPricingService $pricing, $user): array
    {
        $cartFreeShipping = false;
        $freeShippingProductIds = [];

        foreach ($cart->items as $item) {
            if ($item->item_type === 'offer') {
                if ($item->offer?->free_shipping_scope === FlashOffer::FREE_SHIPPING_CART) {
                    $cartFreeShipping = true;
                }

                continue;
            }

            $price = $pricing->getPriceForUser($item->product, $user, $item->quantity);

            if (! (bool) $price->freeShipping || ! $price->flashOffer) {
                continue;
            }

            if ($price->flashOffer->type === FlashOffer::TYPE_FREE_SHIPPING_PRODUCT) {
                $freeShippingProductIds[] = (int) $item->product_id;
            } else {
                $cartFreeShipping = true;
            }
        }

        return ['cart' => $cartFreeShipping, 'products' => array_values(array_unique($freeShippingProductIds))];
    }
}
