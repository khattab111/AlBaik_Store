<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Payments\Contracts\PaymentDriver;
use App\Payments\PaymentResult;

class WalletPaymentDriver implements PaymentDriver
{
    public function charge(Order $order, PaymentMethod $method): PaymentResult
    {
        return new PaymentResult(
            successful: true,
            status: 'paid',
            payload: ['message' => 'Paid from customer wallet.'],
        );
    }
}
