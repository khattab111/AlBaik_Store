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
            'shipping_method_id' => ['required', Rule::exists('shipping_methods', 'id')->where('is_active', true)],
            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('is_active', true)],
            'payment_receipt' => ['nullable', 'image', 'max:4096'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
