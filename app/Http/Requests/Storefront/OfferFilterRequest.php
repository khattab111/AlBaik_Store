<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => filled($this->query('category')) ? trim((string) $this->query('category')) : null,
            'brand' => filled($this->query('brand')) ? trim((string) $this->query('brand')) : null,
            'sort' => $this->query('sort', 'latest'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:160', Rule::exists('categories', 'slug')->where('status', true)],
            'brand' => ['nullable', 'string', 'max:160', Rule::exists('brands', 'slug')->where('status', true)],
            'sort' => ['nullable', Rule::in(['latest', 'price_desc', 'price_asc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return collect($this->validated())
            ->except('page')
            ->filter(fn ($value): bool => filled($value))
            ->all();
    }
}
