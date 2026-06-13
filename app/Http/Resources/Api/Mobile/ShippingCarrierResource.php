<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingCarrierResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized($this->resource, 'name'),
            'slug' => $this->slug,
            'logo' => $this->imageUrl($this->logo),
            'cost' => isset($this->cost) ? (float) $this->cost : null,
            'weight' => isset($this->weight) ? (float) $this->weight : null,
            'estimated_delivery_time' => $this->estimated_delivery_time ?? null,
            'is_free_shipping' => isset($this->is_free_shipping) ? (bool) $this->is_free_shipping : null,
            'free_shipping_reason' => $this->free_shipping_reason ?? null,
        ];
    }
}
