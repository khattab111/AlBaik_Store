<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Services\DiscountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartRepository $carts) {}

    public function index(Request $request): View
    {
        $cart = $this->carts->findForUser($request->user()->id)->load(['items.product.images', 'items.variant']);

        return view('storefront.cart', [
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => $cart->items->sum(fn (CartItem $item) => $item->quantity * (float) $item->unit_price),
        ]);
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $product = Product::where('status', true)->with('variants')->findOrFail($request->integer('product_id'));
        $this->carts->addItem(
            $this->carts->findForUser($request->user()->id),
            $product,
            $request->integer('quantity'),
            $request->filled('variant_id') ? $request->integer('variant_id') : null
        );

        return redirect()->route('cart.index')->with('status', __('Product added to cart.'));
    }

    public function update(Request $request, CartItem $item, DiscountService $discounts): RedirectResponse
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $item->load(['product', 'variant']);

        $item->update([
            'quantity' => $data['quantity'],
            'unit_price' => $discounts->productPrice(
                $item->product,
                (int) $data['quantity'],
                $item->variant && (float) $item->variant->price > 0 ? (float) $item->variant->price : null,
            ),
        ]);

        return back()->with('status', __('Cart updated.'));
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);
        $item->delete();

        return back()->with('status', __('Cart item removed.'));
    }
}
