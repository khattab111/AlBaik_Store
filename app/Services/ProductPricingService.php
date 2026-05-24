<?php

namespace App\Services;

use App\Data\ProductPriceData;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\User;

class ProductPricingService
{
    public function getPriceForUser(Product $product, ?User $user, int $quantity): ProductPriceData
    {
        $quantity = max(1, $quantity);

        if ($user?->isWholesaleCustomer()) {
            $wholesaleTier = $this->bestTier($product, 'wholesale', $quantity);

            if ($wholesaleTier) {
                return new ProductPriceData(
                    price: (float) $wholesaleTier->price,
                    priceType: 'wholesale',
                    appliedTierId: $wholesaleTier->id,
                    tier: $wholesaleTier,
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
