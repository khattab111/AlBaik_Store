<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->input('product_id') ?? $this->route('product')?->id;

        return [
            'product_id' => ['nullable', 'exists:products,id'],
            'variant_id' => ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $productId)],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
