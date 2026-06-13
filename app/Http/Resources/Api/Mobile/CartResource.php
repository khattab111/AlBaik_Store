<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items', $this->items);
        $subtotal = $items ? $items->sum(fn ($item) => (float) $item->unit_price * (int) $item->quantity) : 0.0;
        $discounts = $items ? $items->sum(fn ($item) => (float) ($item->savings_amount ?? 0) * (int) $item->quantity) : 0.0;

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'subtotal' => round($subtotal, 2),
            'discounts' => round($discounts, 2),
            'shipping_estimate' => null,
            'total' => round($subtotal, 2),
            'price_type' => $request->user()?->isWholesaleCustomer() ? 'wholesale' : 'retail',
            'items_count' => $items ? $items->sum('quantity') : 0,
        ];
    }
}
