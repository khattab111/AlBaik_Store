<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductReview;

class ProductReviewRatingService
{
    public function recalculate(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }

        $stats = ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', ProductReview::STATUS_APPROVED)
            ->selectRaw('COALESCE(AVG(rating), 0) as average_rating, COUNT(*) as reviews_count')
            ->first();

        Product::whereKey($productId)->update([
            'average_rating' => round((float) ($stats?->average_rating ?? 0), 2),
            'reviews_count' => (int) ($stats?->reviews_count ?? 0),
        ]);
    }
}
