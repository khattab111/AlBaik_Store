<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('storefront.account.orders', [
            'orders' => $request->user()->orders()->with(['paymentMethod', 'shippingCarrier'])->latest()->paginate(15),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('storefront.account.order', [
            'order' => $order->load(['items.product', 'items.variant', 'paymentMethod', 'shippingCarrier', 'payments', 'timeline']),
        ]);
    }
}
