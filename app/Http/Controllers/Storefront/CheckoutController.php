<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\City;
use App\Models\PaymentMethod;
use App\Repositories\CartRepository;
use App\Services\ShippingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request, CartRepository $carts, ShippingService $shipping): View
    {
        $cart = $carts->findForUser($request->user()->id)->load(['items.product.images', 'items.variant']);
        $subtotal = $cart->items->sum(fn ($item) => $item->quantity * (float) $item->unit_price);
        $defaultAddress = $request->user()->addresses()->with('city')->where('is_active', true)->orderByDesc('is_default')->latest()->first();
        $defaultCity = $defaultAddress?->city;

        return view('storefront.checkout', [
            'cart' => $cart,
            'items' => $cart->items,
            'addresses' => $request->user()->addresses()->with('city')->where('is_active', true)->latest()->get(),
            'cities' => City::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'availableCarriers' => $defaultCity ? $shipping->formatCarriersForCheckout($defaultCity, $cart, $subtotal) : collect(),
            'requiresShipping' => $shipping->requiresShipping($cart),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
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

        return redirect()->route('account.orders.show', $order)->with('status', __('Order created successfully.'));
    }
}
