<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => filled($this->query('search')) ? trim((string) $this->query('search')) : null,
            'category' => filled($this->query('category')) ? trim((string) $this->query('category')) : null,
            'brand' => filled($this->query('brand')) ? trim((string) $this->query('brand')) : null,
            'view' => in_array($this->query('view'), ['grid', 'list'], true) ? $this->query('view') : 'grid',
            'sort' => $this->query('sort', 'latest'),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:160', Rule::exists('categories', 'slug')->where('status', true)],
            'brand' => ['nullable', 'string', 'max:160', Rule::exists('brands', 'slug')->where('status', true)],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:999999999', 'gte:min_price'],
            'in_stock' => ['nullable', 'boolean'],
            'on_sale' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['latest', 'price_desc', 'price_asc', 'best_selling', 'top_rated'])],
            'view' => ['nullable', Rule::in(['grid', 'list'])],
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
