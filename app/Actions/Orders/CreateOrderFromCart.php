<?php

namespace App\Actions\Orders;

use App\Data\CheckoutData;
use App\Events\OrderPlaced;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentMethod;
use App\Models\ShippingCarrier;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
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
        private readonly PaymentService $payments,
        private readonly ProductPricingService $pricing,
        private readonly FlashOfferService $flashOffers,
    ) {}

    public function handle(CheckoutData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = $this->carts->findForUser($data->userId)->load(['items.product.priceTiers', 'items.variant']);
            $user = User::findOrFail($data->userId);

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Cart is empty.']);
            }

            $paymentMethod = PaymentMethod::where('is_active', true)->findOrFail($data->paymentMethodId);
            $shippingAddress = Address::where('user_id', $data->userId)->findOrFail($data->shippingAddressId);
            $city = City::where('is_active', true)->findOrFail($data->shippingCityId);

            if ((int) $shippingAddress->city_id !== $city->id) {
                throw ValidationException::withMessages([
                    'shipping_city_id' => __('The selected city does not match the selected address.'),
                ]);
            }

            foreach ($cart->items as $item) {
                $this->inventory->assertAvailableForUpdate($item->product, $item->quantity, $item->variant_id);
            }

            $pricedItems = $cart->items->map(function (CartItem $item) use ($user): array {
                $price = $this->pricing->getPriceForUser($item->product, $user, $item->quantity);

                return [
                    'item' => $item,
                    'price' => $price,
                    'subtotal' => round($item->quantity * $price->price, 2),
                ];
            });

            $subtotal = $pricedItems->sum('subtotal');
            $quantity = $cart->items->sum('quantity');
            $weight = $cart->items->sum(fn (CartItem $item) => $item->quantity * (float) $item->product->weight);
            $discount = $this->discountAmount($data->couponCode, $subtotal);
            $hasFreeShippingOffer = $pricedItems->contains(fn (array $pricedItem): bool => (bool) $pricedItem['price']->freeShipping);
            $carrier = null;

            if ($this->shipping->requiresShipping($cart)) {
                if (! $data->shippingCarrierId) {
                    throw ValidationException::withMessages([
                        'shipping_carrier_id' => __('Please choose a shipping carrier.'),
                    ]);
                }

                $carrier = ShippingCarrier::where('status', ShippingCarrier::STATUS_ACTIVE)->findOrFail($data->shippingCarrierId);
                $shippingQuote = $this->shipping->calculateShippingCost($city, $carrier, $cart, $subtotal, $hasFreeShippingOffer);
            } else {
                $shippingQuote = [
                    'cost' => 0.0,
                    'weight' => 0.0,
                    'estimated_delivery_time' => null,
                    'is_free_shipping' => true,
                    'no_shipping_required' => true,
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
                'shipping_address_id' => $data->shippingAddressId,
                'billing_address_id' => $data->billingAddressId ?? $data->shippingAddressId,
                'shipping_city_id' => $city->id,
                'shipping_city_name' => $city->name,
                'shipping_carrier_id' => $carrier?->id,
                'shipping_carrier_name' => $carrier?->name,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'shipping_weight' => $shippingQuote['weight'],
                'shipping_delivery_time' => $shippingQuote['estimated_delivery_time'],
                'shipping_address_text' => $this->addressText($shippingAddress),
                'is_free_shipping' => $shippingQuote['is_free_shipping'],
                'discount_amount' => $discount,
                'payment_fee' => $paymentFee,
                'total' => $total,
                'status' => 'pending',
                'notes' => $data->notes,
                'customer_phone' => $shippingAddress->phone,
                'customer_whatsapp' => $shippingAddress->whatsapp,
                'shipping_country' => $city->country,
                'shipping_city' => $city->name,
                'shipping_town' => $shippingAddress->town,
                'shipping_street' => $shippingAddress->street,
            ]);

            foreach ($pricedItems as $pricedItem) {
                /** @var CartItem $item */
                $item = $pricedItem['item'];
                $price = $pricedItem['price'];

                OrderItem::create([
                    'order_id' => $order->id,
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

            return $order->load(['items.product', 'paymentMethod', 'shippingCarrier', 'payments', 'timeline']);
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

    private function addressText(Address $address): string
    {
        return collect([
            $address->country,
            $address->city,
            $address->town,
            $address->street,
            $address->postal_code,
        ])->filter()->implode(' / ');
    }
}
