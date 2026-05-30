<?php

namespace App\Http\Requests\Storefront;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['required', Rule::exists('addresses', 'id')->where('user_id', $this->user()?->id)],
            'billing_address_id' => ['nullable', Rule::exists('addresses', 'id')->where('user_id', $this->user()?->id)],
            'shipping_city_id' => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
            'shipping_carrier_id' => ['nullable', Rule::exists('shipping_carriers', 'id')->where('status', 'active')],
            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('is_active', true)],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'price' => ['prohibited'],
            'unit_price' => ['prohibited'],
            'retail_price' => ['prohibited'],
            'wholesale_price' => ['prohibited'],
            'subtotal' => ['prohibited'],
            'shipping_cost' => ['prohibited'],
            'shipping_method_id' => ['prohibited'],
            'payment_fee' => ['prohibited'],
            'discount_amount' => ['prohibited'],
            'total' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $method = PaymentMethod::find($this->input('payment_method_id'));

                if ($method && in_array($method->type, ['manual', 'bank_transfer'], true) && ! $this->hasFile('payment_receipt')) {
                    $validator->errors()->add('payment_receipt', __('Please upload the payment receipt image.'));
                }
            },
        ];
    }
}
