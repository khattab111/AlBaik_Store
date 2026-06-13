<?php

namespace App\Services\Providers\Gateways;

use App\Models\ElectronicServiceOrder;
use App\Models\ElectronicServiceProvider;
use App\Services\Providers\Contracts\ProviderGatewayInterface;
use App\Services\Providers\DTOs\ProviderResponse;

class ManualGateway implements ProviderGatewayInterface
{
    public function __construct(protected ElectronicServiceProvider $provider)
    {
    }

    public function testConnection(): ProviderResponse
    {
        return ProviderResponse::success(__('Manual provider does not require API connection.'));
    }

    public function getBalance(): ProviderResponse
    {
        return ProviderResponse::success(__('Manual provider balance is managed outside the system.'));
    }

    public function syncServices(): array
    {
        return [];
    }

    public function createOrder(ElectronicServiceOrder $order): ProviderResponse
    {
        return ProviderResponse::success(__('Manual order is waiting for admin processing.'), [
            'order_id' => $order->order_number,
        ], ElectronicServiceOrder::STATUS_PENDING);
    }

    public function checkOrderStatus(ElectronicServiceOrder $order): ProviderResponse
    {
        return ProviderResponse::success(__('Manual order status is controlled by admin.'), [
            'status' => $order->status,
        ], $order->status);
    }
}
