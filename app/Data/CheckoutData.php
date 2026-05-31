<?php

namespace App\Data;

use App\Http\Requests\Api\StoreOrderRequest;

readonly class CheckoutData
{
    public function __construct(
        public int $userId,
        public int $paymentMethodId,
        public string $addressMode,
        public ?int $shippingCarrierId = null,
        public ?int $userAddressId = null,
        public ?int $shippingCityId = null,
        public ?array $temporaryAddress = null,
        public bool $saveAddress = false,
        public ?string $addressLabel = null,
        public ?int $billingAddressId = null,
        public ?string $couponCode = null,
        public ?string $notes = null,
        public ?string $paymentReceiptPath = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            userId: $request->user()->id,
            paymentMethodId: (int) $request->input('payment_method_id'),
            addressMode: $request->input('address_mode', 'saved'),
            shippingCarrierId: $request->filled('shipping_carrier_id') ? (int) $request->input('shipping_carrier_id') : null,
            userAddressId: $request->filled('user_address_id') ? (int) $request->input('user_address_id') : null,
            shippingCityId: $request->filled('city_id') ? (int) $request->input('city_id') : null,
            temporaryAddress: $request->input('address'),
            saveAddress: $request->boolean('save_address'),
            addressLabel: $request->input('address_label'),
            billingAddressId: $request->filled('billing_address_id') ? (int) $request->input('billing_address_id') : null,
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
        );
    }
}
