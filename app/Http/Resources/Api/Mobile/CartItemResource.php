<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        $isOffer = $this->item_type === 'offer';
        $firstComponent = $isOffer ? collect($this->components_snapshot)->first() : null;
        $image = $isOffer
            ? ($firstComponent['product_image'] ?? null)
            : $this->product?->images?->first()?->path;

        return [
            'id' => $this->id,
            'item_type' => $this->item_type,
            'product_id' => $this->product_id,
            'offer_id' => $this->offer_id,
            'variant_id' => $this->variant_id,
            'title' => $isOffer
                ? ($this->title ?: $this->offer?->localized('title'))
                : $this->product?->localized('name'),
            'image' => $this->imageUrl($image),
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => round((float) $this->unit_price * (int) $this->quantity, 2),
            'price_type' => $this->price_type,
            'original_total_price' => $this->original_total_price ? (float) $this->original_total_price : null,
            'savings_amount' => $this->savings_amount ? (float) $this->savings_amount : null,
            'components' => $isOffer ? $this->components_snapshot : null,
        ];
    }
}
