<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function create(array $payload): Order
    {
        return Order::create($payload);
    }

    public function findForUser(int $userId, int $orderId): ?Order
    {
        return Order::where('user_id', $userId)->with(['items.product', 'paymentMethod', 'shippingMethod', 'payments', 'timeline'])->find($orderId);
    }

    public function listForUser(int $userId)
    {
        return Order::with(['items.product', 'shippingMethod', 'paymentMethod', 'payments'])->where('user_id', $userId)->latest()->paginate(15);
    }
}
