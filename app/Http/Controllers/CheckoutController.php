<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Repositories\CartRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request, CartRepository $carts): View
    {
        $cart = $carts->findForUser($request->user()->id)->load(['items.product.images', 'items.variant']);

        return view('checkout.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'addresses' => $request->user()->addresses()->latest()->get(),
            'shippingMethods' => ShippingMethod::where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'subtotal' => $cart->items->sum(fn ($item) => $item->quantity * (float) $item->unit_price),
        ]);
    }

    public function store(CheckoutRequest $request, CreateOrderFromCart $checkout): RedirectResponse
    {
        $receiptPath = $request->hasFile('payment_receipt')
            ? $request->file('payment_receipt')->store('payment-receipts', 'public')
            : null;

        $order = $checkout->handle(new CheckoutData(
            userId: $request->user()->id,
            shippingAddressId: (int) $request->input('shipping_address_id'),
            paymentMethodId: (int) $request->input('payment_method_id'),
            shippingMethodId: (int) $request->input('shipping_method_id'),
            billingAddressId: $request->filled('billing_address_id') ? (int) $request->input('billing_address_id') : null,
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
            paymentReceiptPath: $receiptPath,
        ));

        return redirect()->route('orders.show', $order)->with('status', __('Order created successfully.'));
    }
}
