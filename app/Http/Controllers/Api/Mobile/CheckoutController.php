<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Http\Controllers\Api\Mobile\Concerns\CalculatesMobileCart;
use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\CheckoutSummaryRequest;
use App\Http\Requests\Api\Mobile\PlaceOrderRequest;
use App\Http\Resources\Api\Mobile\CartResource;
use App\Http\Resources\Api\Mobile\OrderResource;
use App\Http\Resources\Api\Mobile\PaymentMethodResource;
use App\Models\City;
use App\Models\PaymentMethod;
use App\Models\ShippingCarrier;
use App\Repositories\CartRepository;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    use CalculatesMobileCart;
    use RespondsToMobile;

    public function summary(CheckoutSummaryRequest $request, CartRepository $carts, ShippingService $shipping): JsonResponse
    {
        $user = $request->user();
        $cart = $carts->findForUser($user->id)->load(['items.product.images', 'items.variant', 'items.offer.items.product.images']);
        $summary = $this->cartSummary($cart, $user);
        $paymentMethod = $this->resolvePaymentMethod($request->input('payment_method'), false);
        $shippingQuote = null;
        $availableCarriers = collect();

        if ($request->filled('address_id')) {
            $address = $user->addresses()->whereKey($request->input('address_id'))->where('is_active', true)->firstOrFail();
            $city = City::query()->where('is_active', true)->findOrFail($address->city_id);
            $availableCarriers = $shipping->formatCarriersForCheckout(
                $city,
                $cart,
                $summary['subtotal'],
                $summary['cart_free_shipping'],
                $summary['free_shipping_product_ids'],
            );

            if ($request->filled('shipping_company_id')) {
                $carrier = ShippingCarrier::query()->findOrFail($request->input('shipping_company_id'));
                $shippingQuote = $shipping->calculateShippingCost(
                    $city,
                    $carrier,
                    $cart,
                    $summary['subtotal'],
                    $summary['cart_free_shipping'],
                    $summary['free_shipping_product_ids'],
                );
            }
        }

        $paymentFee = $paymentMethod ? (float) $paymentMethod->fee : 0.0;
        $shippingCost = (float) ($shippingQuote['cost'] ?? 0);

        return $this->success([
            'cart' => new CartResource($cart),
            'subtotal' => $summary['subtotal'],
            'discounts' => $summary['discounts'],
            'shipping' => $shippingQuote,
            'available_shipping_companies' => $availableCarriers,
            'payment_method' => $paymentMethod ? new PaymentMethodResource($paymentMethod) : null,
            'payment_methods' => PaymentMethodResource::collection(PaymentMethod::query()->where('is_active', true)->orderBy('id')->get()),
            'payment_fee' => $paymentFee,
            'total' => round(max(0, $summary['subtotal'] + $shippingCost + $paymentFee), 2),
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request, CreateOrderFromCart $createOrder): JsonResponse
    {
        $address = $request->user()->addresses()->whereKey($request->validated('address_id'))->where('is_active', true)->firstOrFail();
        $paymentMethod = $this->resolvePaymentMethod($request->validated('payment_method'), true);

        $order = $createOrder->handle(new CheckoutData(
            userId: $request->user()->id,
            paymentMethodId: $paymentMethod->id,
            addressMode: 'saved',
            shippingCarrierId: $request->filled('shipping_company_id') ? (int) $request->input('shipping_company_id') : null,
            userAddressId: $address->id,
            shippingCityId: $address->city_id,
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
        ));

        return $this->success(new OrderResource($order), __('Order placed successfully.'), 201);
    }

    private function resolvePaymentMethod(?string $value, bool $required): ?PaymentMethod
    {
        if (blank($value)) {
            if ($required) {
                throw ValidationException::withMessages(['payment_method' => __('Please choose a payment method.')]);
            }

            return null;
        }

        $method = PaymentMethod::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->where('slug', $value)
                ->orWhere('type', $value)
                ->orWhere('id', is_numeric($value) ? (int) $value : 0))
            ->first();

        if (! $method && $required) {
            throw ValidationException::withMessages(['payment_method' => __('The selected payment method is not available.')]);
        }

        return $method;
    }
}
