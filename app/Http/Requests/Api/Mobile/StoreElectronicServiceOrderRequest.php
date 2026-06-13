<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class StoreElectronicServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fields' => ['required', 'array'],
            'fields.*' => ['nullable'],
            'price' => ['prohibited'],
            'amount' => ['prohibited'],
            'total' => ['prohibited'],
            'provider_cost' => ['prohibited'],
        ];
    }
}
