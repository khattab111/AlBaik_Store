<?php

namespace App\Payments\Contracts;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Payments\PaymentResult;

interface PaymentDriver
{
    public function charge(Order $order, PaymentMethod $method): PaymentResult;
}
