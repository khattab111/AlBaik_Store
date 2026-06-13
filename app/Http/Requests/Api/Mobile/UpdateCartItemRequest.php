<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
        ];
    }
}
