<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Payments\Contracts\PaymentDriver;
use App\Payments\Drivers\BankTransferDriver;
use App\Payments\Drivers\CashOnDeliveryDriver;
use App\Payments\Drivers\ManualPaymentDriver;
use App\Payments\Drivers\WalletPaymentDriver;
use InvalidArgumentException;

class PaymentService
{
    public function createPayment(Order $order, PaymentMethod $method): Payment
    {
        $driver = $this->driver($method->type);
        $result = $driver->charge($order, $method);

        return Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'driver' => $method->type,
            'status' => $result->status,
            'amount' => $order->total,
            'transaction_reference' => $result->reference,
            'payload' => $result->payload,
        ]);
    }

    private function driver(string $type): PaymentDriver
    {
        return match ($type) {
            'cod' => new CashOnDeliveryDriver(),
            'bank_transfer' => new BankTransferDriver(),
            'manual' => new ManualPaymentDriver(),
            'wallet' => new WalletPaymentDriver(),
            default => throw new InvalidArgumentException("Unsupported payment driver [{$type}]."),
        };
    }
}
