<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\CartRepository;
use Illuminate\Support\Collection;

class GuestCartService
{
    private const SESSION_KEY = 'guest_cart.items';

    public function add(Product $product, int $quantity, ?int $variantId = null): void
    {
        $items = $this->rawItems();
        $key = $this->itemKey($product->id, $variantId);

        $items[$key] = [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'quantity' => max(1, $quantity),
        ];

        session()->put(self::SESSION_KEY, $items);
    }

    public function update(Product $product, int $quantity): void
    {
        $items = $this->rawItems();

        foreach ($items as $key => $item) {
            if ((int) $item['product_id'] === $product->id) {
                $items[$key]['quantity'] = max(1, $quantity);
            }
        }

        session()->put(self::SESSION_KEY, $items);
    }

    public function remove(Product $product): void
    {
        $items = collect($this->rawItems())
            ->reject(fn (array $item): bool => (int) $item['product_id'] === $product->id)
            ->all();

        session()->put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return collect($this->rawItems())->sum(fn (array $item): int => (int) $item['quantity']);
    }

    public function items(): Collection
    {
        $rawItems = collect($this->rawItems());
        $products = Product::with(['brand', 'images', 'variants', 'flashSales'])
            ->whereIn('id', $rawItems->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        return $rawItems
            ->map(function (array $item) use ($products) {
                $product = $products->get((int) $item['product_id']);

                if (! $product) {
                    return null;
                }

                $variant = $item['variant_id'] ? $product->variants->firstWhere('id', (int) $item['variant_id']) : null;
                $price = app(ProductPricingService::class)->getPriceForUser($product, null, (int) $item['quantity']);

                return (object) [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $price->price,
                    'price_type' => $price->priceType,
                    'applied_tier_id' => $price->appliedTierId,
                ];
            })
            ->filter()
            ->values();
    }

    public function mergeToUser(int $userId): void
    {
        $cartRepository = app(CartRepository::class);
        $cart = $cartRepository->findForUser($userId);

        foreach ($this->items() as $item) {
            $cartRepository->addItem($cart, $item->product, $item->quantity, $item->variant?->id);
        }

        $this->clear();
    }

    private function rawItems(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    private function itemKey(int $productId, ?int $variantId): string
    {
        return $productId.':'.($variantId ?: 'default');
    }
}
