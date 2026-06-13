<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        $user = $request->user();
        $primaryImage = $this->whenLoaded('images', fn () => $this->images->firstWhere('is_primary', true) ?: $this->images->first());
        $pricing = $this->productPricing($this->resource, $user);

        return [
            'id' => $this->id,
            'name' => $this->localized($this->resource, 'name'),
            'slug' => $this->slug,
            'image' => $this->imageUrl($primaryImage?->path),
            'brand' => $this->whenLoaded('brand', fn () => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->localized('name'),
                'slug' => $this->brand->slug,
            ] : null),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->localized('name'),
                'slug' => $this->category->slug,
            ] : null),
            ...$pricing,
            'average_rating' => (float) $this->average_rating,
            'reviews_count' => (int) $this->reviews_count,
            'is_favorite' => $user
                ? Wishlist::query()->where('user_id', $user->id)->where('product_id', $this->id)->exists()
                : false,
            'is_available' => (bool) $this->status && $this->availableStock() > 0,
            'stock_status' => $this->availableStock() > 0 ? 'in_stock' : 'out_of_stock',
        ];
    }
}
