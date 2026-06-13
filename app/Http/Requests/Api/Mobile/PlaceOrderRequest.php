<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)->where('is_active', true)],
            'shipping_company_id' => ['nullable', 'integer', 'exists:shipping_carriers,id'],
            'payment_method' => ['required', 'string', 'max:100'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
