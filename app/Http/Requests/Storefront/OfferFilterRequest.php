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
            'type' => filled($this->query('type')) ? trim((string) $this->query('type')) : null,
            'sort' => $this->query('sort', 'latest'),
            'per_page' => in_array((int) $this->query('per_page'), [12, 24, 48], true) ? (int) $this->query('per_page') : 12,
            'view' => in_array($this->query('view'), ['grid', 'list'], true) ? $this->query('view') : 'grid',
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:160', Rule::exists('categories', 'slug')->where('status', true)],
            'brand' => ['nullable', 'string', 'max:160', Rule::exists('brands', 'slug')->where('status', true)],
            'type' => ['nullable', Rule::in([
                'percentage_discount',
                'fixed_amount_discount',
                'fixed_price_quantity',
                'bundle_fixed_price',
                'free_shipping_product',
                'buy_x_get_y',
                'cart_free_shipping',
            ])],
            'sort' => ['nullable', Rule::in(['latest', 'highest_discount', 'ending_soon', 'best_selling', 'price_desc', 'price_asc'])],
            'per_page' => ['nullable', 'integer', Rule::in([12, 24, 48])],
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
