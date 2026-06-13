<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicServiceResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized($this->resource, 'name'),
            'slug' => $this->slug,
            'description' => $this->localized($this->resource, 'description'),
            'image' => $this->imageUrl($this->image),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->localized('name'),
                'slug' => $this->category?->slug,
            ]),
            'price' => $this->priceForUser($request->user()),
            'price_type' => $request->user()?->isWholesaleCustomer() ? 'wholesale' : 'retail',
            'is_available' => (bool) $this->is_available,
        ];
    }
}
