<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'price' => ['prohibited'],
            'unit_price' => ['prohibited'],
            'retail_price' => ['prohibited'],
            'wholesale_price' => ['prohibited'],
            'total' => ['prohibited'],
            'subtotal' => ['prohibited'],
            'price_type' => ['prohibited'],
            'applied_tier_id' => ['prohibited'],
            'applied_flash_offer_id' => ['prohibited'],
        ];
    }
}
