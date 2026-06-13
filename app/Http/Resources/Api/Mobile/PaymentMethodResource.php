<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized($this->resource, 'name'),
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->localized($this->resource, 'description'),
            'image' => $this->imageUrl($this->image),
            'fee' => (float) $this->fee,
        ];
    }
}
