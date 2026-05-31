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
            'address_mode' => ['required', Rule::in(['saved', 'new'])],
            'user_address_id' => ['required_if:address_mode,saved', 'nullable', Rule::exists('user_addresses', 'id')->where('user_id', $this->user()?->id)->where('is_active', true)],
            'payment_method_id' => 'required|exists:payment_methods,id',
            'city_id' => 'required_if:address_mode,new|nullable|exists:cities,id',
            'shipping_carrier_id' => 'nullable|exists:shipping_carriers,id',
            'address.recipient_name' => 'required_if:address_mode,new|nullable|string|max:255',
            'address.phone' => 'required_if:address_mode,new|nullable|string|max:50',
            'address.address_line' => 'required_if:address_mode,new|nullable|string|max:255',
            'save_address' => 'nullable|boolean',
            'address_label' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
