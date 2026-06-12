<?php

namespace App\Services;

use App\Data\FlashOfferPriceData;
use App\Models\Cart;
use App\Models\FlashOffer;
use App\Models\FlashOfferItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FlashOfferService
{
    public function getActiveOffers(?string $audience = FlashOffer::AUDIENCE_RETAIL): Collection
    {
        $query = FlashOffer::query()
            ->with(['items.product.images'])
            ->currentlyValid();

        if ($audience !== null) {
            $query->forAudience($audience);
        }

        return $query
            ->orderByDesc('priority')
            ->orderBy('ends_at')
            ->get();
    }

    public function calculateProductOffer(Product $product, int $quantity, ?float $basePrice = null, ?string $audience = FlashOffer::AUDIENCE_RETAIL): ?FlashOfferPriceData
    {
        if ($audience === FlashOffer::AUDIENCE_WHOLESALE && ! $product->is_wholesale_available) {
            return null;
        }

        $quantity = max(1, $quantity);
        $basePrice ??= (float) $product->retail_price;

        $offers = FlashOffer::query()
            ->with(['items' => fn ($query) => $query->where('product_id', $product->id)])
            ->currentlyValid()
            ->when($audience !== null, fn ($query) => $query->forAudience($audience))
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->whereNotIn('type', [
                FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
                FlashOffer::TYPE_BUY_X_GET_Y,
            ])
            ->orderByDesc('priority')
            ->orderBy('ends_at')
            ->get();

        return $offers
            ->map(fn (FlashOffer $offer): ?FlashOfferPriceData => $this->priceForOffer($offer, $product, $quantity, $basePrice))
            ->filter()
            ->sortBy([
                ['offerPrice', 'asc'],
            ])
            ->first();
    }

    public function applyOfferToCart(Cart $cart): void
    {
        $cart->loadMissing(['user', 'items.product.priceTiers']);
        $audience = $cart->user?->isWholesaleCustomer()
            ? FlashOffer::AUDIENCE_WHOLESALE
            : FlashOffer::AUDIENCE_RETAIL;

        foreach ($cart->items as $item) {
            if (($item->item_type ?? 'product') !== 'product' || ! $item->product) {
                continue;
            }

            $offer = $this->calculateProductOffer($item->product, $item->quantity, (float) $item->unit_price, $audience);

            if (! $offer) {
                continue;
            }

            $item->update([
                'unit_price' => $offer->offerPrice,
                'price_type' => 'flash_offer',
                'applied_flash_offer_id' => $offer->offer->id,
            ]);
        }
    }

    public function isOfferValid(FlashOffer $offer, ?string $audience = null): bool
    {
        if ($offer->status !== FlashOffer::STATUS_ACTIVE) {
            return false;
        }

        if ($offer->starts_at && $offer->starts_at->isFuture()) {
            return false;
        }

        if ($offer->ends_at && $offer->ends_at->isPast()) {
            return false;
        }

        if ($offer->max_quantity !== null && $offer->sold_quantity >= $offer->max_quantity) {
            return false;
        }

        if ($audience !== null && ! in_array($offer->audience ?? FlashOffer::AUDIENCE_RETAIL, $this->allowedAudiences($audience), true)) {
            return false;
        }

        return true;
    }

    public function reserveOfferQuantity(FlashOffer $offer, int $quantity): void
    {
        DB::transaction(function () use ($offer, $quantity): void {
            $lockedOffer = FlashOffer::query()->lockForUpdate()->findOrFail($offer->id);

            if (! $this->isOfferValid($lockedOffer)) {
                throw ValidationException::withMessages([
                    'flash_offer' => __('The selected flash offer is no longer available.'),
                ]);
            }

            if ($lockedOffer->max_quantity !== null && $lockedOffer->sold_quantity + $quantity > $lockedOffer->max_quantity) {
                throw ValidationException::withMessages([
                    'flash_offer' => __('The selected quantity exceeds the remaining flash offer quantity.'),
                ]);
            }

            $lockedOffer->increment('sold_quantity', $quantity);
        });
    }

    private function priceForOffer(FlashOffer $offer, Product $product, int $quantity, float $basePrice): ?FlashOfferPriceData
    {
        if (! $this->isOfferValid($offer)) {
            return null;
        }

        if ($offer->max_quantity !== null && $quantity > $offer->remainingQuantity()) {
            throw ValidationException::withMessages([
                'quantity' => __('The requested quantity is not available for this flash offer.'),
            ]);
        }

        /** @var FlashOfferItem|null $item */
        $item = $offer->items->firstWhere('product_id', $product->id);

        if (! $item || $item->is_free_item) {
            return null;
        }

        $original = (float) ($item->original_price ?: $basePrice ?: $product->retail_price);
        $price = match ($offer->type) {
            FlashOffer::TYPE_PERCENTAGE_DISCOUNT => $original - ($original * ((float) $offer->discount_value / 100)),
            FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT => $original - (float) $offer->discount_value,
            FlashOffer::TYPE_FIXED_PRICE_QUANTITY => (float) ($item->offer_price ?: $offer->fixed_price ?: $original),
            FlashOffer::TYPE_BUNDLE_FIXED_PRICE => (float) ($item->offer_price ?: $original),
            FlashOffer::TYPE_FREE_SHIPPING_PRODUCT => (float) ($item->offer_price ?: $original),
            FlashOffer::TYPE_BUY_X_GET_Y => (float) ($item->offer_price ?: $original),
            default => $original,
        };

        $price = round(max(0, $price), 2);

        if ($price >= $basePrice && ! $offer->free_shipping) {
            return null;
        }

        return new FlashOfferPriceData(
            offer: $offer,
            item: $item,
            originalPrice: $original,
            offerPrice: $price,
            freeShipping: (bool) $offer->free_shipping,
        );
    }

    private function allowedAudiences(string $audience): array
    {
        return $audience === FlashOffer::AUDIENCE_WHOLESALE
            ? [FlashOffer::AUDIENCE_WHOLESALE, FlashOffer::AUDIENCE_BOTH]
            : [FlashOffer::AUDIENCE_RETAIL, FlashOffer::AUDIENCE_BOTH];
    }
}
