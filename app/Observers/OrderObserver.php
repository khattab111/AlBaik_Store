<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatusHistory;

class OrderObserver
{
    public function updating(Order $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        match ($order->status) {
            'paid' => $order->paid_at ??= now(),
            'shipped' => $order->shipped_at ??= now(),
            'delivered' => $order->delivered_at ??= now(),
            'cancelled' => $order->cancelled_at ??= now(),
            default => null,
        };
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'from_status' => $order->getOriginal('status'),
            'to_status' => $order->status,
            'note' => 'Status changed.',
        ]);
    }
}
