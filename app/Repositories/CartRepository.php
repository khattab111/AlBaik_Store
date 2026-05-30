<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductPricingService;
use Illuminate\Database\Eloquent\Collection;

class CartRepository
{
    public function __construct(private readonly ProductPricingService $pricing) {}

    public function findForUser(int $userId): Cart
    {
        $currencyId = Currency::where('is_default', true)->where('status', true)->value('id')
            ?? Currency::where('status', true)->value('id')
            ?? Currency::query()->value('id');

        return Cart::firstOrCreate(['user_id' => $userId], ['currency_id' => $currencyId]);
    }

    public function addItem(Cart $cart, Product $product, int $quantity, ?int $variantId = null): CartItem
    {
        if ($variantId) {
            $product->variants()->findOrFail($variantId);
        }

        $user = $cart->relationLoaded('user') ? $cart->user : User::find($cart->user_id);
        $price = $this->pricing->getPriceForUser($product->loadMissing('priceTiers'), $user, $quantity);
        $unitPrice = $price->price;

        return CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id, 'variant_id' => $variantId],
            [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'price_type' => $price->priceType,
                'applied_tier_id' => $price->appliedTierId,
                'applied_flash_offer_id' => $price->appliedFlashOfferId,
            ]
        );
    }

    public function items(Cart $cart): Collection
    {
        return $cart->items()->with(['product', 'variant'])->get();
    }
}
