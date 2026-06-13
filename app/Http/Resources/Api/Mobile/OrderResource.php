<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'discount_amount' => (float) $this->discount_amount,
            'payment_fee' => (float) $this->payment_fee,
            'total' => (float) $this->total,
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            'shipping' => [
                'city_id' => $this->shipping_city_id,
                'city_name' => $this->shipping_city_name,
                'carrier_id' => $this->shipping_carrier_id,
                'carrier_name' => $this->shipping_carrier_name,
                'address_text' => $this->shipping_address_text,
                'is_free_shipping' => (bool) $this->is_free_shipping,
            ],
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'product_id' => $item->product_id,
                'offer_id' => $item->offer_id,
                'title' => $item->item_type === 'offer' ? $item->offer_title : $item->product?->localized('name'),
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
                'total_price' => (float) $item->total_price,
            ])->values()),
            'payment_method' => $this->whenLoaded('paymentMethod', fn () => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->localized('name'),
                'slug' => $this->paymentMethod->slug,
                'type' => $this->paymentMethod->type,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
