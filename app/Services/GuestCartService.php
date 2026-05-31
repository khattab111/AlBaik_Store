<?php

namespace App\Services;

use App\Models\Product;
use App\Models\FlashOffer;
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
            'item_type' => 'product',
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'quantity' => max(1, $quantity),
        ];

        session()->put(self::SESSION_KEY, $items);
    }

    public function addWithPrice(
        Product $product,
        int $quantity,
        float $unitPrice,
        string $priceType,
        ?int $appliedFlashOfferId = null,
        ?int $variantId = null,
    ): void {
        $items = $this->rawItems();
        $key = $this->itemKey($product->id, $variantId);

        $items[$key] = [
            'item_type' => 'product',
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'quantity' => max(1, $quantity),
            'unit_price' => round($unitPrice, 2),
            'price_type' => $priceType,
            'applied_flash_offer_id' => $appliedFlashOfferId,
        ];

        session()->put(self::SESSION_KEY, $items);
    }

    public function addOffer(FlashOffer $offer, int $quantity): void
    {
        $item = app(OfferCartService::class)->addOfferToSessionPayload($offer, max(1, $quantity));

        $items = $this->rawItems();
        $items['offer:'.$offer->id] = $item;

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
        $products = Product::with(['brand', 'images', 'variants', 'priceTiers'])
            ->whereIn('id', $rawItems->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        return $rawItems
            ->map(function (array $item) use ($products) {
                if (($item['item_type'] ?? 'product') === 'offer') {
                    return (object) [
                        'item_type' => 'offer',
                        'product' => null,
                        'offer_id' => $item['offer_id'],
                        'title' => $item['title'],
                        'variant' => null,
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (float) $item['unit_price'],
                        'original_total_price' => (float) ($item['original_total_price'] ?? 0),
                        'savings_amount' => (float) ($item['savings_amount'] ?? 0),
                        'components_snapshot' => $item['components_snapshot'] ?? [],
                        'price_type' => 'flash_offer',
                        'applied_tier_id' => null,
                        'applied_flash_offer_id' => $item['offer_id'],
                    ];
                }

                $product = $products->get((int) $item['product_id']);

                if (! $product) {
                    return null;
                }

                $variant = $item['variant_id'] ? $product->variants->firstWhere('id', (int) $item['variant_id']) : null;
                $price = app(ProductPricingService::class)->getPriceForUser($product, null, (int) $item['quantity']);
                $unitPrice = array_key_exists('unit_price', $item) ? (float) $item['unit_price'] : $price->price;

                return (object) [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $unitPrice,
                    'price_type' => $item['price_type'] ?? $price->priceType,
                    'applied_tier_id' => $price->appliedTierId,
                    'applied_flash_offer_id' => $item['applied_flash_offer_id'] ?? $price->appliedFlashOfferId,
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
            if ($item->applied_flash_offer_id && $item->price_type === 'flash_offer') {
                if (($item->item_type ?? 'product') === 'offer') {
                    $offer = FlashOffer::find($item->offer_id);

                    if ($offer) {
                        app(OfferCartService::class)->addOfferToCart($cart, $offer, $item->quantity);
                    }

                    continue;
                }

                $cartRepository->addItemWithPrice($cart, $item->product, $item->quantity, (float) $item->unit_price, $item->price_type, $item->applied_flash_offer_id, $item->variant?->id);

                continue;
            }

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
