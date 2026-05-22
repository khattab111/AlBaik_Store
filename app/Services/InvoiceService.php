<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    public function stream(Order $order)
    {
        $order->load(['user', 'items.product', 'items.variant', 'paymentMethod', 'shippingMethod']);

        return Pdf::loadView('pdf.invoice', ['order' => $order])
            ->setPaper('a4')
            ->stream("invoice-{$order->order_number}.pdf");
    }
}
