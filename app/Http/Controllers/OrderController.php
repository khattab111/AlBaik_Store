<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => $request->user()->orders()->with(['paymentMethod', 'shippingMethod'])->latest()->paginate(15),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('orders.show', [
            'order' => $order->load(['items.product', 'items.variant', 'paymentMethod', 'shippingMethod', 'payments', 'timeline']),
        ]);
    }

    public function success(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('orders.success', [
            'order' => $order->load(['paymentMethod', 'shippingMethod', 'payments']),
        ]);
    }
}
