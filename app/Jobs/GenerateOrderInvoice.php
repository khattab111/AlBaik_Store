<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateOrderInvoice implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::with(['items.product', 'user'])->find($this->orderId);

        if (! $order) {
            return;
        }

        Log::info('Invoice generation queued.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }
}
