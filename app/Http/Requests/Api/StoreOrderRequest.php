<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => [
                'required',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()?->id),
            ],
            'billing_address_id' => [
                'nullable',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()?->id),
            ],
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'coupon_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
