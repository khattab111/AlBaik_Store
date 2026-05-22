<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Currency;
use App\Models\Product;
use App\Services\DiscountService;
use Illuminate\Database\Eloquent\Collection;

class CartRepository
{
    public function findForUser(int $userId): Cart
    {
        $currencyId = Currency::where('is_default', true)->where('status', true)->value('id')
            ?? Currency::where('status', true)->value('id')
            ?? Currency::query()->value('id');

        return Cart::firstOrCreate(['user_id' => $userId], ['currency_id' => $currencyId]);
    }

    public function addItem(Cart $cart, Product $product, int $quantity, ?int $variantId = null): CartItem
    {
        $variant = $variantId ? $product->variants()->findOrFail($variantId) : null;
        $basePrice = $variant && (float) $variant->price > 0 ? (float) $variant->price : null;
        $unitPrice = app(DiscountService::class)->productPrice($product, $quantity, $basePrice);

        return CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id, 'variant_id' => $variantId],
            ['quantity' => $quantity, 'unit_price' => $unitPrice]
        );
    }

    public function items(Cart $cart): Collection
    {
        return $cart->items()->with(['product', 'variant'])->get();
    }
}
