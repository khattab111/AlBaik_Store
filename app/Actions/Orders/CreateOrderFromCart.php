<?php

namespace App\Actions\Orders;

use App\Data\CheckoutData;
use App\Events\OrderPlaced;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Coupon;
use App\Models\FlashOffer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentMethod;
use App\Models\ShippingCarrier;
use App\Models\User;
use App\Models\UserAddress;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
use App\Services\OfferCartService;
use App\Services\PaymentService;
use App\Services\FlashOfferService;
use App\Services\ProductPricingService;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderFromCart
{
    public function __construct(
        private readonly CartRepository $carts,
        private readonly InventoryService $inventory,
        private readonly ShippingService $shipping,
        private readonly OfferCartService $offerCart,
        private readonly PaymentService $payments,
        private readonly ProductPricingService $pricing,
        private readonly FlashOfferService $flashOffers,
    ) {}

    public function handle(CheckoutData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = $this->carts->findForUser($data->userId)->load(['items.product.priceTiers', 'items.variant', 'items.appliedFlashOffer', 'items.offer.items.product.images']);
            $user = User::findOrFail($data->userId);

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Cart is empty.']);
            }

            $paymentMethod = PaymentMethod::where('is_active', true)->findOrFail($data->paymentMethodId);
            [$addressSnapshot, $savedAddress] = $this->resolveAddress($data);
            $city = City::where('is_active', true)->findOrFail($addressSnapshot['city_id']);

            foreach ($cart->items as $item) {
                if ($item->item_type === 'offer') {
                    $this->offerCart->validateOfferAvailability($item->offer, (int) $item->quantity);

                    continue;
                }

                $this->inventory->assertAvailableForUpdate($item->product, $item->quantity, $item->variant_id);
            }

            $pricedItems = $cart->items->map(function (CartItem $item) use ($user): array {
                if ($item->item_type === 'offer') {
                    return [
                        'item' => $item,
                        'price' => new \App\Data\ProductPriceData(
                            price: (float) $item->unit_price,
                            priceType: 'flash_offer',
                            appliedFlashOfferId: $item->offer_id,
                            flashOffer: $item->offer,
                            originalPrice: (float) $item->original_total_price,
                            freeShipping: in_array($item->offer?->free_shipping_scope, [FlashOffer::FREE_SHIPPING_OFFER, FlashOffer::FREE_SHIPPING_CART], true),
                        ),
                        'subtotal' => round($item->quantity * (float) $item->unit_price, 2),
                    ];
                }

                $price = $item->appliedFlashOffer
                    && in_array($item->appliedFlashOffer->type, [FlashOffer::TYPE_BUNDLE_FIXED_PRICE, FlashOffer::TYPE_BUY_X_GET_Y], true)
                    && $this->flashOffers->isOfferValid($item->appliedFlashOffer)
                        ? new \App\Data\ProductPriceData(
                            price: (float) $item->unit_price,
                            priceType: 'flash_offer',
                            appliedFlashOfferId: $item->applied_flash_offer_id,
                            flashOffer: $item->appliedFlashOffer,
                            originalPrice: (float) $item->product->retail_price,
                            freeShipping: (bool) $item->appliedFlashOffer->free_shipping,
                        )
                        : $this->pricing->getPriceForUser($item->product, $user, $item->quantity);

                return [
                    'item' => $item,
                    'price' => $price,
                    'subtotal' => round($item->quantity * $price->price, 2),
                ];
            });

            $subtotal = $pricedItems->sum('subtotal');
            $discount = $this->discountAmount($data->couponCode, $subtotal);
            $cartFreeShipping = $pricedItems->contains(fn (array $pricedItem): bool => $pricedItem['item']->item_type === 'offer'
                    ? $pricedItem['price']->flashOffer?->free_shipping_scope === FlashOffer::FREE_SHIPPING_CART
                    : ((bool) $pricedItem['price']->freeShipping && $pricedItem['price']->flashOffer?->type !== FlashOffer::TYPE_FREE_SHIPPING_PRODUCT));
            $freeShippingProductIds = $pricedItems
                ->filter(fn (array $pricedItem): bool => (bool) $pricedItem['price']->freeShipping
                    && $pricedItem['item']->item_type === 'product'
                    && $pricedItem['price']->flashOffer?->type === FlashOffer::TYPE_FREE_SHIPPING_PRODUCT)
                ->map(fn (array $pricedItem): int => (int) $pricedItem['item']->product_id)
                ->values()
                ->all();
            $carrier = null;

            if ($this->shipping->requiresShipping($cart)) {
                if (! $data->shippingCarrierId) {
                    throw ValidationException::withMessages([
                        'shipping_carrier_id' => __('Please choose a shipping carrier.'),
                    ]);
                }

                $carrier = ShippingCarrier::where('status', ShippingCarrier::STATUS_ACTIVE)->findOrFail($data->shippingCarrierId);
                $shippingQuote = $this->shipping->calculateShippingCost($city, $carrier, $cart, $subtotal, $cartFreeShipping, $freeShippingProductIds);
            } else {
                $shippingQuote = [
                    'cost' => 0.0,
                    'weight' => 0.0,
                    'estimated_delivery_time' => null,
                    'is_free_shipping' => true,
                    'no_shipping_required' => true,
                    'free_shipping_reason' => 'no_shipping_required',
                ];
            }

            $shippingCost = (float) $shippingQuote['cost'];
            $paymentFee = (float) $paymentMethod->fee;
            $total = max(0, $subtotal + $shippingCost + $paymentFee - $discount);

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'user_id' => $data->userId,
                'currency_id' => $cart->currency_id,
                'payment_method_id' => $paymentMethod->id,
                'shipping_address_id' => null,
                'billing_address_id' => null,
                'shipping_city_id' => $city->id,
                'shipping_city_name' => $city->name,
                'shipping_carrier_id' => $carrier?->id,
                'shipping_carrier_name' => $carrier?->name,
                'shipping_recipient_name' => $addressSnapshot['recipient_name'],
                'shipping_phone' => $addressSnapshot['phone'],
                'shipping_address_line' => $addressSnapshot['address_line'],
                'shipping_building_number' => $addressSnapshot['building_number'] ?? null,
                'shipping_floor' => $addressSnapshot['floor'] ?? null,
                'shipping_apartment' => $addressSnapshot['apartment'] ?? null,
                'shipping_landmark' => $addressSnapshot['landmark'] ?? null,
                'shipping_notes' => $addressSnapshot['notes'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'shipping_weight' => $shippingQuote['weight'],
                'shipping_delivery_time' => $shippingQuote['estimated_delivery_time'],
                'shipping_address_text' => $this->addressText($addressSnapshot, $city),
                'is_free_shipping' => $shippingQuote['is_free_shipping'],
                'discount_amount' => $discount,
                'payment_fee' => $paymentFee,
                'total' => $total,
                'status' => 'pending',
                'notes' => $data->notes,
                'customer_phone' => $addressSnapshot['phone'],
                'customer_whatsapp' => $addressSnapshot['phone'],
                'shipping_country' => $city->country,
                'shipping_city' => $city->name,
                'shipping_town' => null,
                'shipping_street' => $addressSnapshot['address_line'],
            ]);

            foreach ($pricedItems as $pricedItem) {
                /** @var CartItem $item */
                $item = $pricedItem['item'];
                $price = $pricedItem['price'];

                if ($item->item_type === 'offer') {
                    $this->offerCart->createOfferOrderItem($order, $item);
                    $this->offerCart->reduceOfferStock($item->offer, (int) $item->quantity);

                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'product',
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $price->price,
                    'price_type' => $price->priceType,
                    'applied_tier_id' => $price->appliedTierId,
                    'applied_flash_offer_id' => $price->appliedFlashOfferId,
                    'subtotal' => $pricedItem['subtotal'],
                    'total_price' => $pricedItem['subtotal'],
                ]);

                if ($price->flashOffer) {
                    $this->flashOffers->reserveOfferQuantity($price->flashOffer, $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $data->userId,
                'to_status' => 'pending',
                'note' => 'Order created from cart.',
            ]);

            $payment = $this->payments->createPayment($order, $paymentMethod);

            if ($data->paymentReceiptPath) {
                $payment->update([
                    'receipt_image' => $data->paymentReceiptPath,
                    'submitted_at' => now(),
                    'status' => 'manual_review',
                    'payload' => array_merge($payment->payload ?? [], [
                        'receipt_uploaded_by' => 'customer',
                    ]),
                ]);
            }

            $this->inventory->deductForOrder($order);
            $cart->items()->delete();

            event(new OrderPlaced($order));

            return $order->load(['items.product', 'items.offer', 'paymentMethod', 'shippingCarrier', 'payments', 'timeline']);
        });
    }

    private function discountAmount(?string $couponCode, float $subtotal): float
    {
        if (! $couponCode) {
            return 0.0;
        }

        $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->lockForUpdate()->first();

        if (! $coupon || $subtotal < (float) $coupon->minimum_order_amount) {
            return 0.0;
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return 0.0;
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return 0.0;
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return 0.0;
        }

        $coupon->increment('used_count');

        return $coupon->type === 'percentage'
            ? round($subtotal * ((float) $coupon->value / 100), 2)
            : min($subtotal, (float) $coupon->value);
    }

    private function nextOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.str_pad((string) (Order::whereDate('created_at', today())->count() + 1), 5, '0', STR_PAD_LEFT);
    }

    private function resolveAddress(CheckoutData $data): array
    {
        if ($data->addressMode === 'saved') {
            $address = UserAddress::where('user_id', $data->userId)
                ->where('is_active', true)
                ->findOrFail($data->userAddressId);

            return [$address->snapshot(), $address];
        }

        $snapshot = array_merge([
            'recipient_name' => null,
            'phone' => null,
            'city_id' => $data->shippingCityId,
            'address_line' => null,
            'building_number' => null,
            'floor' => null,
            'apartment' => null,
            'landmark' => null,
            'notes' => null,
        ], $data->temporaryAddress ?? []);

        $snapshot['city_id'] = $data->shippingCityId;

        if ($data->saveAddress) {
            $address = UserAddress::create([
                'user_id' => $data->userId,
                'label' => $data->addressLabel ?: __('Address'),
                'recipient_name' => $snapshot['recipient_name'],
                'phone' => $snapshot['phone'],
                'city_id' => $snapshot['city_id'],
                'address_line' => $snapshot['address_line'],
                'building_number' => $snapshot['building_number'],
                'floor' => $snapshot['floor'],
                'apartment' => $snapshot['apartment'],
                'landmark' => $snapshot['landmark'],
                'notes' => $snapshot['notes'],
                'is_default' => false,
                'is_active' => true,
            ]);

            return [$snapshot, $address];
        }

        return [$snapshot, null];
    }

    private function addressText(array $address, City $city): string
    {
        return collect([
            $city->country,
            $city->name,
            $address['address_line'] ?? null,
            $address['building_number'] ?? null,
            $address['floor'] ?? null,
            $address['apartment'] ?? null,
            $address['landmark'] ?? null,
        ])->filter()->implode(' / ');
    }
}
