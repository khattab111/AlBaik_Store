<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\Product;

class DiscountService
{
    public function productPrice(Product $product, int $quantity, ?float $basePrice = null): float
    {
        $price = $basePrice ?? $product->getPriceForQuantity($quantity);
        $flashSale = $product->flashSales()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->first();

        if (! $flashSale instanceof FlashSale) {
            return round($price, 2);
        }

        $pivot = $flashSale->pivot;

        if ($pivot->quantity_limit !== null && $pivot->sold_count >= $pivot->quantity_limit) {
            return round($price, 2);
        }

        $discounted = $pivot->discount_type === 'fixed'
            ? $price - (float) $pivot->discount_value
            : $price - ($price * ((float) $pivot->discount_value / 100));

        return round(max(0, $discounted), 2);
    }
}
