<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Validation\ValidationException;

class OrderWorkflowService
{
    private const TRANSITIONS = [
        'pending' => ['paid', 'processing', 'cancelled'],
        'paid' => ['processing', 'refunded'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function transition(Order $order, string $status, ?User $actor = null, ?string $note = null): Order
    {
        $from = $order->status;

        if ($from === $status) {
            return $order;
        }

        if (! in_array($status, self::TRANSITIONS[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot move order from {$from} to {$status}.",
            ]);
        }

        $order->forceFill(['status' => $status])->save();

        $order->user?->notify(new OrderStatusChangedNotification($order));

        return $order->refresh();
    }
}
