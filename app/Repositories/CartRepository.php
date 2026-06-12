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
        $quantity = $this->normalizedQuantity($product, $user, $quantity);
        $price = $this->pricing->getPriceForUser($product->loadMissing('priceTiers'), $user, $quantity);
        $unitPrice = $price->price;

        return CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'item_type' => 'product', 'product_id' => $product->id, 'variant_id' => $variantId],
            [
                'offer_id' => null,
                'title' => null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'original_total_price' => null,
                'savings_amount' => null,
                'components_snapshot' => null,
                'price_type' => $price->priceType,
                'applied_tier_id' => $price->appliedTierId,
                'applied_flash_offer_id' => $price->appliedFlashOfferId,
            ]
        );
    }

    public function addItemWithPrice(
        Cart $cart,
        Product $product,
        int $quantity,
        float $unitPrice,
        string $priceType,
        ?int $appliedFlashOfferId = null,
        ?int $variantId = null,
    ): CartItem {
        if ($variantId) {
            $product->variants()->findOrFail($variantId);
        }

        return CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'item_type' => 'product', 'product_id' => $product->id, 'variant_id' => $variantId],
            [
                'offer_id' => null,
                'title' => null,
                'quantity' => max(1, $quantity),
                'unit_price' => round($unitPrice, 2),
                'original_total_price' => null,
                'savings_amount' => null,
                'components_snapshot' => null,
                'price_type' => $priceType,
                'applied_tier_id' => null,
                'applied_flash_offer_id' => $appliedFlashOfferId,
            ]
        );
    }

    public function items(Cart $cart): Collection
    {
        return $cart->items()->with(['product', 'variant', 'offer'])->get();
    }

    private function normalizedQuantity(Product $product, ?User $user, int $quantity): int
    {
        $quantity = max(1, $quantity);

        if ($user?->isWholesaleCustomer() && $product->is_wholesale_available) {
            return max($quantity, (int) ($product->wholesale_minimum_quantity ?: 1));
        }

        return $quantity;
    }
}
