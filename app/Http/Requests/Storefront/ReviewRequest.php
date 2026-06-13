<?php

namespace App\Http\Requests\Storefront;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:3000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Product|null $product */
                $product = $this->route('product');

                if (! $product || ! $this->user()) {
                    return;
                }

                if (ProductReview::where('product_id', $product->id)->where('user_id', $this->user()->id)->exists()) {
                    $validator->errors()->add('rating', __('You have already reviewed this product.'));

                    return;
                }

                if (! $this->eligibleOrder($product)) {
                    $validator->errors()->add('rating', __('You can review this product after receiving an order that contains it.'));
                }
            },
        ];
    }

    public function eligibleOrder(Product $product): ?Order
    {
        return Order::query()
            ->where('user_id', $this->user()?->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->where(function ($query) use ($product): void {
                $query->whereHas('items', fn ($item) => $item->where('product_id', $product->id))
                    ->orWhereHas('items', function ($item) use ($product): void {
                        $item->whereNotNull('components_snapshot')
                            ->where('components_snapshot', 'like', '%"product_id":'.$product->id.'%');
                    });
            })
            ->latest()
            ->first();
    }
}
