<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $productId = $this->input('product_id');

        return [
            'product_id' => ['required_without:offer_id', 'nullable', 'integer', Rule::exists('products', 'id')->where('status', true)],
            'offer_id' => ['required_without:product_id', 'nullable', 'integer', Rule::exists('flash_offers', 'id')],
            'variant_id' => ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $productId)],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'price' => ['prohibited'],
            'unit_price' => ['prohibited'],
            'retail_price' => ['prohibited'],
            'wholesale_price' => ['prohibited'],
            'total' => ['prohibited'],
            'subtotal' => ['prohibited'],
            'price_type' => ['prohibited'],
        ];
    }
}
