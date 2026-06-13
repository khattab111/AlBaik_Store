<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()
            ->with(['items.product', 'user'])
            ->whereIn('status', ['delivered', 'completed'])
            ->take(8)
            ->get();

        foreach ($orders as $order) {
            foreach ($order->items->where('item_type', 'product')->whereNotNull('product_id')->take(2) as $item) {
                ProductReview::updateOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'user_id' => $order->user_id,
                    ],
                    [
                        'order_id' => $order->id,
                        'rating' => 5,
                        'title' => app()->getLocale() === 'ar' ? 'تجربة ممتازة' : 'Excellent experience',
                        'comment' => app()->getLocale() === 'ar'
                            ? 'منتج أصلي ووصل بحالة ممتازة.'
                            : 'Original product delivered in excellent condition.',
                        'status' => ProductReview::STATUS_APPROVED,
                        'approved_at' => now(),
                        'approved_by' => null,
                    ]
                );
            }
        }
    }
}
