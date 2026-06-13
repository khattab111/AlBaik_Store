<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashOfferResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized($this->resource, 'title'),
            'slug' => $this->slug,
            'description' => $this->localized($this->resource, 'description'),
            'type' => $this->type,
            'audience' => $this->audience,
            'discount_value' => $this->discount_value ? (float) $this->discount_value : null,
            'fixed_price' => $this->fixed_price ? (float) $this->fixed_price : null,
            'max_quantity' => $this->max_quantity,
            'sold_quantity' => (int) $this->sold_quantity,
            'remaining_quantity' => $this->remainingQuantity(),
            'ends_at' => $this->ends_at?->toISOString(),
        ];
    }
}
