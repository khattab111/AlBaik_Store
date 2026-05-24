<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Services\GuestCartService;
use App\Services\ProductPricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartRepository $carts,
        private readonly GuestCartService $guestCart,
    ) {}

    public function index(Request $request): View
    {
        if (! $request->user()) {
            $items = $this->guestCart->items();

            return view('cart.index', [
                'cart' => null,
                'items' => $items,
                'subtotal' => $items->sum(fn ($item): float => $item->quantity * (float) $item->unit_price),
            ]);
        }

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

        $variantId = $request->filled('variant_id') ? $request->integer('variant_id') : null;

        if (! $request->user()) {
            $this->guestCart->add($product->load('variants'), $request->integer('quantity', 1), $variantId);

            return back()->with('status', __('Product added to cart.'));
        }

        $this->carts->addItem($this->carts->findForUser($request->user()->id), $product->load('variants'), $request->integer('quantity', 1), $variantId);

        return back()->with('status', __('Product added to cart.'));
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $product = Product::where('status', true)->findOrFail($request->integer('product_id'));

        return $this->add($request, $product);
    }

    public function update(Request $request, Product $product, ProductPricingService $pricing): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        if (! $request->user()) {
            $this->guestCart->update($product, (int) $data['quantity']);

            return back()->with('status', __('Cart updated.'));
        }

        $cart = $this->carts->findForUser($request->user()->id);
        $item = $cart->items()->where('product_id', $product->id)->with(['product.priceTiers', 'variant'])->firstOrFail();
        $price = $pricing->getPriceForUser($item->product, $request->user(), (int) $data['quantity']);

        $item->update([
            'quantity' => $data['quantity'],
            'unit_price' => $price->price,
            'price_type' => $price->priceType,
            'applied_tier_id' => $price->appliedTierId,
        ]);

        return back()->with('status', __('Cart updated.'));
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        if (! $request->user()) {
            $this->guestCart->remove($product);

            return back()->with('status', __('Cart item removed.'));
        }

        $this->carts->findForUser($request->user()->id)->items()->where('product_id', $product->id)->delete();

        return back()->with('status', __('Cart item removed.'));
    }

    public function clear(Request $request): RedirectResponse
    {
        if (! $request->user()) {
            $this->guestCart->clear();

            return back()->with('status', __('Cart cleared.'));
        }

        $this->carts->findForUser($request->user()->id)->items()->delete();

        return back()->with('status', __('Cart cleared.'));
    }
}
