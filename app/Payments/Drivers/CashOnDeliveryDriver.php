<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Payments\Contracts\PaymentDriver;
use App\Payments\PaymentResult;

class CashOnDeliveryDriver implements PaymentDriver
{
    public function charge(Order $order, PaymentMethod $method): PaymentResult
    {
        return new PaymentResult(
            successful: true,
            status: 'pending',
            payload: ['message' => 'Cash will be collected on delivery.'],
        );
    }
}
