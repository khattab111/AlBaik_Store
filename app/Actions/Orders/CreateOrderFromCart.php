<?php

namespace App\Actions\Orders;

use App\Data\CheckoutData;
use App\Events\OrderPlaced;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
use App\Services\PaymentService;
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
            $shippingMethod = ShippingMethod::where('is_active', true)->findOrFail($data->shippingMethodId);
            $shippingAddress = Address::where('user_id', $data->userId)->findOrFail($data->shippingAddressId);

            foreach ($cart->items as $item) {
                $this->inventory->assertAvailable($item->product, $item->quantity, $item->variant_id);
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
            $shippingCost = $this->shipping->calculate($shippingMethod, $subtotal, $quantity, $weight, $shippingAddress->country, $shippingAddress->city, $shippingAddress->town);
            $paymentFee = (float) $paymentMethod->fee;
            $total = max(0, $subtotal + $shippingCost + $paymentFee - $discount);

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'user_id' => $data->userId,
                'currency_id' => $cart->currency_id,
                'shipping_method_id' => $shippingMethod->id,
                'payment_method_id' => $paymentMethod->id,
                'shipping_address_id' => $data->shippingAddressId,
                'billing_address_id' => $data->billingAddressId ?? $data->shippingAddressId,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discount,
                'payment_fee' => $paymentFee,
                'total' => $total,
                'status' => 'pending',
                'notes' => $data->notes,
                'customer_phone' => $shippingAddress->phone,
                'customer_whatsapp' => $shippingAddress->whatsapp,
                'shipping_country' => $shippingAddress->country,
                'shipping_city' => $shippingAddress->city,
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
                    'subtotal' => $pricedItem['subtotal'],
                    'total_price' => $pricedItem['subtotal'],
                ]);
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

            return $order->load(['items.product', 'paymentMethod', 'shippingMethod', 'payments', 'timeline']);
        });
    }

    private function discountAmount(?string $couponCode, float $subtotal): float
    {
        if (! $couponCode) {
            return 0.0;
        }

        $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();

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
}
