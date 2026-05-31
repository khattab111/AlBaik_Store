<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function assertAvailable(Product $product, int $quantity, ?int $variantId = null): void
    {
        if ($variantId) {
            $variant = ProductVariant::where('product_id', $product->id)->findOrFail($variantId);

            if ($variant->available_stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock for {$product->name}. Available: {$variant->available_stock}.",
                ]);
            }

            return;
        }

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}.",
            ]);
        }
    }

    public function assertAvailableForUpdate(Product $product, int $quantity, ?int $variantId = null): void
    {
        if ($variantId) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->whereKey($variantId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($variant->available_stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock for {$product->name}. Available: {$variant->available_stock}.",
                ]);
            }

            return;
        }

        $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

        if ($lockedProduct->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock for {$lockedProduct->name}. Available: {$lockedProduct->stock_quantity}.",
            ]);
        }
    }

    public function reserve(Product $product, int $quantity, ?int $variantId = null): void
    {
        $this->assertAvailable($product, $quantity, $variantId);

        if ($variantId) {
            ProductVariant::whereKey($variantId)->increment('reserved_stock', $quantity);
        }
    }

    public function deductForOrder(Order $order): void
    {
        $warehouse = Warehouse::where('is_active', true)->first();

        foreach ($order->items()->with(['product', 'variant'])->get() as $item) {
            if ($item->item_type === 'offer') {
                continue;
            }

            if ($item->variant) {
                $item->variant->decrement('stock', $item->quantity);
                $item->variant->decrement('reserved_stock', min($item->quantity, $item->variant->reserved_stock));

                if ($warehouse) {
                    InventoryMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'product_variant_id' => $item->variant->id,
                        'type' => 'sale',
                        'quantity' => -1 * $item->quantity,
                        'source_type' => Order::class,
                        'source_id' => $order->id,
                        'metadata' => ['order_number' => $order->order_number],
                    ]);
                }

                continue;
            }

            $item->product->decrement('stock_quantity', $item->quantity);
        }
    }
}
