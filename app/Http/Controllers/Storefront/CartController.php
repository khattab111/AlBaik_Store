<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Services\ProductPricingService;
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

    public function update(UpdateCartItemRequest $request, CartItem $item, ProductPricingService $pricing): RedirectResponse
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);

        $data = $request->validated();
        $item->load(['product.priceTiers', 'variant']);
        $price = $pricing->getPriceForUser($item->product, $request->user(), (int) $data['quantity']);

        $item->update([
            'quantity' => $data['quantity'],
            'unit_price' => $price->price,
            'price_type' => $price->priceType,
            'applied_tier_id' => $price->appliedTierId,
            'applied_flash_offer_id' => $price->appliedFlashOfferId,
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
