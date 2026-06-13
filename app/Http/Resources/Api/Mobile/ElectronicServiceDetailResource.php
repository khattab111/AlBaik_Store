<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicServiceDetailResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized($this->resource, 'name'),
            'slug' => $this->slug,
            'description' => $this->localized($this->resource, 'description'),
            'instructions' => $this->localized($this->resource, 'instructions'),
            'image' => $this->imageUrl($this->image),
            'category' => new ElectronicServiceCategoryResource($this->whenLoaded('category')),
            'price' => $this->priceForUser($request->user()),
            'price_type' => $request->user()?->isWholesaleCustomer() ? 'wholesale' : 'retail',
            'fields' => $this->visibleFields(),
            'is_available' => (bool) $this->is_available,
        ];
    }
}
