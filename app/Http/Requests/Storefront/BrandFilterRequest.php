<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => filled($this->query('search')) ? trim((string) $this->query('search')) : null,
            'sort' => in_array($this->query('sort'), ['name', 'products_desc', 'latest'], true) ? $this->query('sort') : 'name',
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['name', 'products_desc', 'latest'])],
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
