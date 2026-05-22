<?php

namespace App\Http\Controllers;

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

        return view('cart.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => $cart->items->sum(fn (CartItem $item) => $item->quantity * (float) $item->unit_price),
        ]);
    }

    public function add(AddCartItemRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        $this->carts->addItem(
            $this->carts->findForUser($request->user()->id),
            $product->load('variants'),
            $request->integer('quantity', 1),
            $request->filled('variant_id') ? $request->integer('variant_id') : null
        );

        return back()->with('status', __('Product added to cart.'));
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $product = Product::where('status', true)->findOrFail($request->integer('product_id'));

        return $this->add($request, $product);
    }

    public function update(Request $request, Product $product, DiscountService $discounts): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $cart = $this->carts->findForUser($request->user()->id);
        $item = $cart->items()->where('product_id', $product->id)->with(['product', 'variant'])->firstOrFail();

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

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $this->carts->findForUser($request->user()->id)->items()->where('product_id', $product->id)->delete();

        return back()->with('status', __('Cart item removed.'));
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->carts->findForUser($request->user()->id)->items()->delete();

        return back()->with('status', __('Cart cleared.'));
    }
}
