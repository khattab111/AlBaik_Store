<?php

namespace App\Data;

use App\Http\Requests\Api\StoreOrderRequest;

readonly class CheckoutData
{
    public function __construct(
        public int $userId,
        public int $shippingAddressId,
        public int $paymentMethodId,
        public int $shippingCityId,
        public ?int $shippingCarrierId = null,
        public ?int $billingAddressId = null,
        public ?string $couponCode = null,
        public ?string $notes = null,
        public ?string $paymentReceiptPath = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            userId: $request->user()->id,
            shippingAddressId: (int) $request->input('shipping_address_id'),
            paymentMethodId: (int) $request->input('payment_method_id'),
            shippingCityId: (int) $request->input('shipping_city_id'),
            shippingCarrierId: $request->filled('shipping_carrier_id') ? (int) $request->input('shipping_carrier_id') : null,
            billingAddressId: $request->filled('billing_address_id') ? (int) $request->input('billing_address_id') : null,
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
        );
    }
}
