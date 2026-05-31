<?php

namespace App\Presenters;

use App\Models\FlashOffer;
use App\Models\Product;
use App\Services\OfferCartService;
use Illuminate\Support\Collection;

class FlashOfferPresenter
{
    public function forProduct(FlashOffer $offer, Product $product, int $quantity = 1): array
    {
        $offer->loadMissing(['items.product.images']);
        $item = $offer->items->firstWhere('product_id', $product->id);
        $original = (float) ($item?->original_price ?: $product->retail_price);
        $offerPrice = $this->offerPrice($offer, $original, $item?->offer_price);
        $saving = max(0, ($original - $offerPrice) * max(1, $quantity));

        return [
            'id' => $offer->id,
            'slug' => $offer->slug,
            'title' => $offer->title,
            'description' => $offer->description,
            'type' => $offer->type,
            'offer_scope' => $offer->offer_scope,
            'starts_at' => $offer->starts_at,
            'badge' => FlashOffer::typeOptions()[$offer->type] ?? __('Flash Offer'),
            'summary' => $this->summary($offer, $original, $offerPrice, $item?->quantity),
            'details' => $this->details($offer, $original, $offerPrice, $saving),
            'items' => $this->items($offer),
            'original_price' => $original,
            'offer_price' => $offerPrice,
            'saving' => $saving,
            'discount_percentage' => $original > 0 ? round(($saving / $original) * 100, 1) : 0,
            'remaining_quantity' => $offer->remainingQuantity(),
            'ends_at' => $offer->ends_at,
            'free_shipping_scope' => $offer->free_shipping
                ? ($offer->type === FlashOffer::TYPE_FREE_SHIPPING_PRODUCT ? __('This product only') : __('Whole order'))
                : null,
        ];
    }

    public function forOffer(FlashOffer $offer): array
    {
        $offer->loadMissing(['items.product.images']);
        $original = (float) $offer->items->sum(fn ($item): float => (float) ($item->original_price ?: $item->product?->retail_price ?: 0) * max(1, (int) $item->quantity));
        $offerPrice = app(OfferCartService::class)->finalOfferPrice($offer);
        $saving = max(0, $original - $offerPrice);

        return [
            'id' => $offer->id,
            'slug' => $offer->slug,
            'title' => $offer->title,
            'description' => $offer->description,
            'type' => $offer->type,
            'offer_scope' => $offer->offer_scope,
            'starts_at' => $offer->starts_at,
            'badge' => FlashOffer::typeOptions()[$offer->type] ?? __('Flash Offer'),
            'summary' => $this->summary($offer, $original, $offerPrice),
            'details' => $this->details($offer, $original, $offerPrice, $saving),
            'items' => $this->items($offer),
            'original_price' => $original,
            'offer_price' => $offerPrice,
            'saving' => $saving,
            'discount_percentage' => $original > 0 ? round(($saving / $original) * 100, 1) : 0,
            'remaining_quantity' => $offer->remainingQuantity(),
            'ends_at' => $offer->ends_at,
            'free_shipping_scope' => $offer->free_shipping_scope,
        ];
    }

    private function summary(FlashOffer $offer, float $original, float $offerPrice, ?int $quantity = null): string
    {
        return match ($offer->type) {
            FlashOffer::TYPE_PERCENTAGE_DISCOUNT => __(':value% discount', ['value' => number_format((float) $offer->discount_value, 0)]),
            FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT => __('Save USD :value', ['value' => number_format((float) $offer->discount_value, 2)]),
            FlashOffer::TYPE_FIXED_PRICE_QUANTITY => __('Special price for :quantity pieces', ['quantity' => $quantity ?: $offer->max_quantity ?: 1]),
            FlashOffer::TYPE_BUNDLE_FIXED_PRICE => __('Bundle price USD :price', ['price' => number_format($offerPrice, 2)]),
            FlashOffer::TYPE_FREE_SHIPPING_PRODUCT => __('Free shipping for selected product'),
            FlashOffer::TYPE_BUY_X_GET_Y => __('Buy selected quantity and get free items'),
            FlashOffer::TYPE_CART_FREE_SHIPPING => __('Free shipping for the whole cart'),
            default => __('Limited time offer'),
        };
    }

    private function details(FlashOffer $offer, float $original, float $offerPrice, float $saving): array
    {
        $details = [
            __('Original price: USD :price', ['price' => number_format($original, 2)]),
            __('Offer price: USD :price', ['price' => number_format($offerPrice, 2)]),
        ];

        if ($saving > 0) {
            $details[] = __('You save: USD :amount', ['amount' => number_format($saving, 2)]);
        }

        if ($offer->remainingQuantity() !== null) {
            $details[] = __('Remaining offer quantity: :quantity', ['quantity' => $offer->remainingQuantity()]);
        }

        if ($offer->ends_at) {
            $details[] = __('Ends at: :date', ['date' => $offer->ends_at->format('Y-m-d H:i')]);
        }

        if ($offer->starts_at) {
            $details[] = __('Starts at: :date', ['date' => $offer->starts_at->format('Y-m-d H:i')]);
        }

        if ($offer->free_shipping_scope && $offer->free_shipping_scope !== FlashOffer::FREE_SHIPPING_NONE) {
            $details[] = $offer->free_shipping_scope === FlashOffer::FREE_SHIPPING_CART
                ? __('Free shipping applies to the whole cart.')
                : __('Free shipping applies only to this offer.');
        }

        return $details;
    }

    private function items(FlashOffer $offer): Collection
    {
        return $offer->items->map(fn ($item): array => [
            'product' => $item->product,
            'name' => $item->product?->name,
            'image' => $item->product?->images?->first()?->path,
            'quantity' => $item->quantity,
            'original_price' => (float) ($item->original_price ?: $item->product?->retail_price ?: 0),
            'offer_price' => $item->offer_price !== null ? (float) $item->offer_price : null,
            'is_free_item' => (bool) $item->is_free_item,
        ]);
    }

    private function offerPrice(FlashOffer $offer, float $original, mixed $itemOfferPrice = null): float
    {
        return round(max(0, match ($offer->type) {
            FlashOffer::TYPE_PERCENTAGE_DISCOUNT => $original - ($original * ((float) $offer->discount_value / 100)),
            FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT => $original - (float) $offer->discount_value,
            FlashOffer::TYPE_FIXED_PRICE_QUANTITY => (float) ($itemOfferPrice ?: $offer->fixed_price ?: $original),
            FlashOffer::TYPE_BUNDLE_FIXED_PRICE => (float) ($itemOfferPrice ?: $original),
            FlashOffer::TYPE_FREE_SHIPPING_PRODUCT => (float) ($itemOfferPrice ?: $original),
            FlashOffer::TYPE_BUY_X_GET_Y => (float) ($itemOfferPrice ?: $original),
            default => $original,
        }), 2);
    }
}
