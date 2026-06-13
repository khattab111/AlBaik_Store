<?php

namespace App\Services\Providers\Contracts;

use App\Models\ElectronicServiceOrder;
use App\Services\Providers\DTOs\ProviderResponse;

interface ProviderGatewayInterface
{
    public function testConnection(): ProviderResponse;

    public function getBalance(): ProviderResponse;

    public function syncServices(): array;

    public function createOrder(ElectronicServiceOrder $order): ProviderResponse;

    public function checkOrderStatus(ElectronicServiceOrder $order): ProviderResponse;
}
