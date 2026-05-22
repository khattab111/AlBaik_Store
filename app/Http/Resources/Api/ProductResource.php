<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'retail_price' => $this->retail_price,
            'wholesale_price' => $this->wholesale_price,
            'wholesale_minimum_quantity' => $this->wholesale_minimum_quantity,
            'stock_quantity' => $this->stock_quantity,
            'is_featured' => $this->is_featured,
            'brand' => $this->whenLoaded('brand'),
            'supplier' => $this->whenLoaded('supplier'),
            'category' => $this->whenLoaded('category'),
            'images' => $this->whenLoaded('images'),
            'variants' => $this->whenLoaded('variants'),
            'reviews' => $this->whenLoaded('reviews'),
        ];
    }
}
