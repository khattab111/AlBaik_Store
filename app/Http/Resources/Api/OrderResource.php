<?php

namespace App\Http\Resources\Api;

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
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'discount_amount' => $this->discount_amount,
            'payment_fee' => $this->payment_fee,
            'total' => $this->total,
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items'),
            'payment_method' => $this->whenLoaded('paymentMethod'),
            'shipping_carrier' => $this->whenLoaded('shippingCarrier'),
            'payments' => $this->whenLoaded('payments'),
            'timeline' => $this->whenLoaded('timeline'),
            'created_at' => $this->created_at,
        ];
    }
}
