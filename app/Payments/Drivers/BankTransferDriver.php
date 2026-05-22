<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Payments\Contracts\PaymentDriver;
use App\Payments\PaymentResult;

class BankTransferDriver implements PaymentDriver
{
    public function charge(Order $order, PaymentMethod $method): PaymentResult
    {
        return new PaymentResult(
            successful: true,
            status: 'awaiting_transfer',
            payload: ['settings' => $method->settings ?? []],
        );
    }
}
