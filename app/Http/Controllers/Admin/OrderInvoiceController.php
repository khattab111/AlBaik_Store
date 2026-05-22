<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;

class OrderInvoiceController extends Controller
{
    public function __invoke(Order $order, InvoiceService $invoices)
    {
        $this->authorize('view', $order);

        return $invoices->stream($order);
    }
}
