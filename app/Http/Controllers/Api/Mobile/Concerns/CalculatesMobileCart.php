<?php

namespace App\Http\Controllers\Api\Mobile\Concerns;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\FlashOffer;
use App\Models\User;
use App\Services\FlashOfferService;
use App\Services\ProductPricingService;
use Illuminate\Support\Collection;

trait CalculatesMobileCart
{
    protected function pricedCartItems(Cart $cart, User $user): Collection
    {
        $cart->loadMissing(['items.product.priceTiers', 'items.product.images', 'items.variant', 'items.appliedFlashOffer', 'items.offer.items.product.images']);

        return $cart->items->map(function (CartItem $item) use ($user): array {
            if ($item->item_type === 'offer') {
                return [
                    'item' => $item,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => round((float) $item->unit_price * (int) $item->quantity, 2),
                    'price_type' => 'flash_offer',
                    'free_shipping' => in_array($item->offer?->free_shipping_scope, [FlashOffer::FREE_SHIPPING_OFFER, FlashOffer::FREE_SHIPPING_CART], true),
                    'flash_offer' => $item->offer,
                ];
            }

            $price = app(ProductPricingService::class)->getPriceForUser($item->product, $user, (int) $item->quantity);

            return [
                'item' => $item,
                'unit_price' => $price->price,
                'subtotal' => round($price->price * (int) $item->quantity, 2),
                'price_type' => $price->priceType,
                'free_shipping' => (bool) $price->freeShipping,
                'flash_offer' => $price->flashOffer,
            ];
        });
    }

    protected function cartSummary(Cart $cart, User $user): array
    {
        $pricedItems = $this->pricedCartItems($cart, $user);
        $subtotal = (float) $pricedItems->sum('subtotal');

        return [
            'priced_items' => $pricedItems,
            'subtotal' => round($subtotal, 2),
            'discounts' => round($cart->items->sum(fn (CartItem $item): float => (float) ($item->savings_amount ?? 0) * (int) $item->quantity), 2),
            'cart_free_shipping' => $pricedItems->contains(fn (array $pricedItem): bool => $pricedItem['item']->item_type === 'offer'
                ? $pricedItem['flash_offer']?->free_shipping_scope === FlashOffer::FREE_SHIPPING_CART
                : ((bool) $pricedItem['free_shipping'] && $pricedItem['flash_offer']?->type !== FlashOffer::TYPE_FREE_SHIPPING_PRODUCT)),
            'free_shipping_product_ids' => $pricedItems
                ->filter(fn (array $pricedItem): bool => (bool) $pricedItem['free_shipping']
                    && $pricedItem['item']->item_type === 'product'
                    && $pricedItem['flash_offer']?->type === FlashOffer::TYPE_FREE_SHIPPING_PRODUCT)
                ->map(fn (array $pricedItem): int => (int) $pricedItem['item']->product_id)
                ->values()
                ->all(),
        ];
    }
}
