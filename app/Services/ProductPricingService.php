<?php

namespace App\Services;

use App\Data\ProductPriceData;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\User;

class ProductPricingService
{
    public function __construct(private readonly FlashOfferService $flashOffers) {}

    public function getPriceForUser(Product $product, ?User $user, int $quantity): ProductPriceData
    {
        $quantity = max(1, $quantity);
        $basePriceData = null;

        if ($user?->isWholesaleCustomer() && $product->is_wholesale_available) {
            $wholesaleTier = $this->bestTier($product, 'wholesale', $quantity);

            if ($wholesaleTier) {
                $basePriceData = new ProductPriceData(
                    price: (float) $wholesaleTier->price,
                    priceType: 'wholesale',
                    appliedTierId: $wholesaleTier->id,
                    tier: $wholesaleTier,
                );
            }

            if (! $basePriceData && (float) $product->wholesale_price > 0 && $quantity >= (int) ($product->wholesale_minimum_quantity ?: 1)) {
                $basePriceData = new ProductPriceData(
                    price: (float) $product->wholesale_price,
                    priceType: 'wholesale',
                );
            }
        }

        if (! $basePriceData) {
            $retailTier = $this->bestTier($product, 'retail', $quantity);

            if ($retailTier) {
                $basePriceData = new ProductPriceData(
                    price: (float) $retailTier->price,
                    priceType: 'retail',
                    appliedTierId: $retailTier->id,
                    tier: $retailTier,
                );
            }
        }

        $basePriceData ??= new ProductPriceData(
            price: (float) $product->retail_price,
            priceType: 'retail',
        );

        $audience = $user?->isWholesaleCustomer()
            ? FlashOffer::AUDIENCE_WHOLESALE
            : FlashOffer::AUDIENCE_RETAIL;

        $flashOffer = $this->flashOffers->calculateProductOffer($product, $quantity, $basePriceData->price, $audience);

        if ($flashOffer) {
            return new ProductPriceData(
                price: $flashOffer->offerPrice,
                priceType: 'flash_offer',
                appliedTierId: $basePriceData->appliedTierId,
                tier: $basePriceData->tier,
                appliedFlashOfferId: $flashOffer->offer->id,
                flashOffer: $flashOffer->offer,
                originalPrice: $flashOffer->originalPrice,
                freeShipping: $flashOffer->freeShipping,
            );
        }

        return $basePriceData;
    }

    public function getBasePriceForUser(Product $product, ?User $user, int $quantity): ProductPriceData
    {
        $quantity = max(1, $quantity);

        if ($user?->isWholesaleCustomer() && $product->is_wholesale_available) {
            $wholesaleTier = $this->bestTier($product, 'wholesale', $quantity);

            if ($wholesaleTier) {
                return new ProductPriceData(
                    price: (float) $wholesaleTier->price,
                    priceType: 'wholesale',
                    appliedTierId: $wholesaleTier->id,
                    tier: $wholesaleTier,
                );
            }

            if ((float) $product->wholesale_price > 0 && $quantity >= (int) ($product->wholesale_minimum_quantity ?: 1)) {
                return new ProductPriceData(
                    price: (float) $product->wholesale_price,
                    priceType: 'wholesale',
                );
            }
        }

        $retailTier = $this->bestTier($product, 'retail', $quantity);

        if ($retailTier) {
            return new ProductPriceData(
                price: (float) $retailTier->price,
                priceType: 'retail',
                appliedTierId: $retailTier->id,
                tier: $retailTier,
            );
        }

        return new ProductPriceData(
            price: (float) $product->retail_price,
            priceType: 'retail',
        );
    }

    private function bestTier(Product $product, string $type, int $quantity): ?ProductPriceTier
    {
        $tiers = $product->relationLoaded('priceTiers')
            ? $product->priceTiers
            : $product->priceTiers()->get();

        return $tiers
            ->where('is_active', true)
            ->where('type', $type)
            ->where('min_quantity', '<=', $quantity)
            ->sortBy([
                ['min_quantity', 'desc'],
                ['sort_order', 'asc'],
            ])
            ->first();
    }
}
