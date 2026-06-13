<?php

namespace App\Http\Resources\Api\Mobile\Concerns;

use App\Models\Product;
use App\Models\User;
use App\Services\ProductPricingService;

trait FormatsMobileValues
{
    protected function localized($model, string $field, ?string $fallback = null): ?string
    {
        if (method_exists($model, 'localized')) {
            return $model->localized($field, $fallback);
        }

        return $model->{$field} ?? $fallback;
    }

    protected function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    protected function productPricing(Product $product, ?User $user = null, int $quantity = 1): array
    {
        $pricing = app(ProductPricingService::class)->getPriceForUser($product, $user, $quantity);
        $oldPrice = $pricing->originalPrice ?: null;

        return [
            'price' => round($pricing->price, 2),
            'old_price' => $oldPrice ? round($oldPrice, 2) : null,
            'discount_percentage' => $oldPrice && $oldPrice > $pricing->price
                ? round((($oldPrice - $pricing->price) / $oldPrice) * 100)
                : null,
            'price_type' => $pricing->priceType,
            'applied_flash_offer_id' => $pricing->appliedFlashOfferId,
        ];
    }
}
