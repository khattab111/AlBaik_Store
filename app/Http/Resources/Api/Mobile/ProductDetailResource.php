<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        $user = $request->user();
        $pricing = $this->productPricing($this->resource, $user);

        return [
            'id' => $this->id,
            'name' => $this->localized($this->resource, 'name'),
            'slug' => $this->slug,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $this->imageUrl($image->path),
                'alt' => $image->localized('alt_text'),
                'is_primary' => (bool) $image->is_primary,
            ])->values()),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'short_description' => $this->localized($this->resource, 'short_description'),
            'description' => $this->localized($this->resource, 'description'),
            'specifications' => [],
            ...$pricing,
            'wholesale_price' => $user?->isWholesaleCustomer() && $this->is_wholesale_available ? (float) $this->wholesale_price : null,
            'min_wholesale_quantity' => (int) ($this->wholesale_minimum_quantity ?: 1),
            'stock_status' => $this->availableStock() > 0 ? 'in_stock' : 'out_of_stock',
            'average_rating' => (float) $this->average_rating,
            'reviews_count' => (int) $this->reviews_count,
            'approved_reviews' => ProductReviewResource::collection($this->whenLoaded('approvedProductReviews')),
            'related_products' => ProductResource::collection($this->whenLoaded('relatedProducts')),
            'active_offers' => FlashOfferResource::collection($this->whenLoaded('activeOffers')),
        ];
    }
}
