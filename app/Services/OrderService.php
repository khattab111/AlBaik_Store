<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Repositories\OrderRepository;

class OrderService
{
    public function __construct(protected OrderRepository $repository) {}

    public function createOrder(array $payload, array $items)
    {
        $order = $this->repository->create($payload);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return $order;
    }

    public function listForUser(int $userId)
    {
        return $this->repository->listForUser($userId);
    }

    public function getForUser(int $userId, int $orderId)
    {
        return $this->repository->findForUser($userId, $orderId);
    }
}
