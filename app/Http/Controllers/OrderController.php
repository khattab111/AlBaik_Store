<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isWholesaleCustomer() && ! $request->routeIs('wholesale.orders.index')) {
            return redirect()->route('wholesale.orders.index');
        }

        return view('orders.index', [
            'isWholesaleAccount' => $request->user()->isWholesaleCustomer(),
            'orders' => $request->user()->orders()->with(['paymentMethod', 'shippingCarrier'])->latest()->paginate(15),
        ]);
    }

    public function show(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($request->user()->isWholesaleCustomer() && ! $request->routeIs('wholesale.orders.show')) {
            return redirect()->route('wholesale.orders.show', $order);
        }

        return view('orders.show', [
            'isWholesaleAccount' => $request->user()->isWholesaleCustomer(),
            'order' => $order->load(['items.product', 'items.variant', 'paymentMethod', 'shippingCarrier', 'payments', 'timeline']),
        ]);
    }

    public function success(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('orders.success', [
            'order' => $order->load(['paymentMethod', 'shippingCarrier', 'payments']),
        ]);
    }
}
