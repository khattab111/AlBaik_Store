<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\FlashOffer;
use App\Models\FlashOfferItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OfferCartService
{
    public function __construct(private readonly FlashOfferService $flashOffers) {}

    public function addOfferToCart(Cart $cart, FlashOffer $offer, int $quantity): CartItem
    {
        $quantity = max(1, $quantity);
        $payload = $this->payload($offer, $quantity);

        return CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'item_type' => 'offer', 'offer_id' => $offer->id],
            array_merge($payload, [
                'product_id' => null,
                'variant_id' => null,
                'applied_tier_id' => null,
                'applied_flash_offer_id' => $offer->id,
            ])
        );
    }

    public function addOfferToSessionPayload(FlashOffer $offer, int $quantity): array
    {
        return array_merge($this->payload($offer, $quantity), [
            'item_type' => 'offer',
            'offer_id' => $offer->id,
            'product_id' => null,
            'variant_id' => null,
            'applied_flash_offer_id' => $offer->id,
        ]);
    }

    public function validateOfferAvailability(FlashOffer $offer, int $quantity): void
    {
        $offer->loadMissing('items.product.images');

        if (! $this->flashOffers->isOfferValid($offer)) {
            throw ValidationException::withMessages([
                'offer' => __('The selected offer is no longer available.'),
            ]);
        }

        if ($offer->remainingQuantity() !== null && $quantity > $offer->remainingQuantity()) {
            throw ValidationException::withMessages([
                'quantity' => __('The requested quantity exceeds the remaining flash offer quantity.'),
            ]);
        }

        foreach ($offer->items as $item) {
            if (! $item->product) {
                continue;
            }

            $required = max(1, (int) $item->quantity) * $quantity;
            $stock = (int) Product::whereKey($item->product_id)->value('stock_quantity');

            if ($stock < $required) {
                throw ValidationException::withMessages([
                    'quantity' => __('Insufficient stock for :product. Available: :stock.', [
                        'product' => $item->product->name,
                        'stock' => $stock,
                    ]),
                ]);
            }
        }
    }

    public function getOfferComponents(FlashOffer $offer): Collection
    {
        return $this->snapshot($offer);
    }

    public function calculateOfferShippingWeight(FlashOffer $offer, int $quantity): float
    {
        $offer->loadMissing('items.product');

        if ($this->freeShippingScope($offer) === FlashOffer::FREE_SHIPPING_OFFER || $this->freeShippingScope($offer) === FlashOffer::FREE_SHIPPING_CART) {
            return 0.0;
        }

        return round($offer->items->sum(function (FlashOfferItem $item) use ($quantity): float {
            if (! $item->product || ! (bool) $item->product->requires_shipping || (bool) $item->product->free_shipping) {
                return 0.0;
            }

            return (float) $item->product->weight * max(1, (int) $item->quantity) * max(1, $quantity);
        }), 3);
    }

    public function createOfferOrderItem(Order $order, CartItem $cartItem): OrderItem
    {
        $offer = $cartItem->offer()->firstOrFail();
        $this->validateOfferAvailability($offer, (int) $cartItem->quantity);

        return OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'offer',
            'product_id' => null,
            'offer_id' => $offer->id,
            'offer_title' => $cartItem->title ?: $offer->title,
            'offer_type' => $offer->type,
            'offer_price' => $cartItem->unit_price,
            'original_total_price' => $cartItem->original_total_price,
            'savings_amount' => $cartItem->savings_amount,
            'components_snapshot' => $cartItem->components_snapshot,
            'variant_id' => null,
            'quantity' => $cartItem->quantity,
            'unit_price' => $cartItem->unit_price,
            'price_type' => 'flash_offer',
            'applied_tier_id' => null,
            'applied_flash_offer_id' => $offer->id,
            'subtotal' => round((float) $cartItem->unit_price * (int) $cartItem->quantity, 2),
            'total_price' => round((float) $cartItem->unit_price * (int) $cartItem->quantity, 2),
        ]);
    }

    public function reduceOfferStock(FlashOffer $offer, int $quantity): void
    {
        $this->validateOfferAvailability($offer, $quantity);

        foreach ($offer->items as $item) {
            if (! $item->product) {
                continue;
            }

            $required = max(1, (int) $item->quantity) * $quantity;
            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();

            if ($product->stock_quantity < $required) {
                throw ValidationException::withMessages([
                    'quantity' => __('Insufficient stock for :product. Available: :stock.', [
                        'product' => $product->name,
                        'stock' => $product->stock_quantity,
                    ]),
                ]);
            }

            $product->decrement('stock_quantity', $required);
        }

        $this->flashOffers->reserveOfferQuantity($offer, $quantity);
    }

    public function finalOfferPrice(FlashOffer $offer, ?Collection $snapshot = null): float
    {
        $snapshot ??= $this->snapshot($offer);
        $original = (float) $snapshot->sum(fn (array $item): float => (float) $item['original_price'] * (int) $item['quantity']);

        return round(max(0, match ($offer->type) {
            FlashOffer::TYPE_PERCENTAGE_DISCOUNT => $original - ($original * ((float) $offer->discount_value / 100)),
            FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT => $original - (float) $offer->discount_value,
            FlashOffer::TYPE_FIXED_PRICE_QUANTITY,
            FlashOffer::TYPE_BUNDLE_FIXED_PRICE => (float) ($offer->fixed_price ?: $original),
            FlashOffer::TYPE_BUY_X_GET_Y => (float) ($offer->fixed_price ?: $original),
            FlashOffer::TYPE_FREE_SHIPPING_PRODUCT,
            FlashOffer::TYPE_CART_FREE_SHIPPING => $original,
            default => $original,
        }), 2);
    }

    private function payload(FlashOffer $offer, int $quantity): array
    {
        $quantity = max(1, $quantity);
        $this->validateOfferAvailability($offer, $quantity);

        $snapshot = $this->snapshot($offer);
        $price = $this->finalOfferPrice($offer, $snapshot);
        $original = (float) $snapshot->sum(fn (array $item): float => (float) $item['original_price'] * (int) $item['quantity']);

        return [
            'item_type' => 'offer',
            'offer_id' => $offer->id,
            'title' => $offer->title,
            'quantity' => $quantity,
            'unit_price' => $price,
            'original_total_price' => $original,
            'savings_amount' => max(0, $original - $price),
            'components_snapshot' => $snapshot->values()->all(),
            'price_type' => 'flash_offer',
        ];
    }

    public function freeShippingScope(FlashOffer $offer): string
    {
        if ($offer->free_shipping_scope) {
            return $offer->free_shipping_scope;
        }

        if (! $offer->free_shipping) {
            return FlashOffer::FREE_SHIPPING_NONE;
        }

        return $offer->type === FlashOffer::TYPE_CART_FREE_SHIPPING
            ? FlashOffer::FREE_SHIPPING_CART
            : FlashOffer::FREE_SHIPPING_OFFER;
    }

    private function snapshot(FlashOffer $offer): Collection
    {
        $offer->loadMissing('items.product.images');

        return $offer->items
            ->filter(fn (FlashOfferItem $item): bool => (bool) $item->product)
            ->map(fn (FlashOfferItem $item): array => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_image' => $item->product->images->first()?->path,
                'quantity' => max(1, (int) $item->quantity),
                'original_price' => (float) ($item->original_price ?: $item->product->retail_price),
                'offer_price' => $item->offer_price !== null ? (float) $item->offer_price : null,
                'is_free_item' => (bool) $item->is_free_item,
                'requires_shipping' => (bool) $item->product->requires_shipping,
                'free_shipping' => (bool) $item->product->free_shipping,
                'weight' => (float) $item->product->weight,
            ])
            ->values();
    }
}
