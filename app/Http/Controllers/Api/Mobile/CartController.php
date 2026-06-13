<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\StoreCartItemRequest;
use App\Http\Requests\Api\Mobile\UpdateCartItemRequest;
use App\Http\Resources\Api\Mobile\CartResource;
use App\Models\CartItem;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
use App\Services\OfferCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use RespondsToMobile;

    public function index(Request $request, CartRepository $carts): JsonResponse
    {
        $cart = $carts->findForUser($request->user()->id)->load(['items.product.images', 'items.variant', 'items.offer']);

        return $this->success(new CartResource($cart));
    }

    public function store(StoreCartItemRequest $request, CartRepository $carts, OfferCartService $offerCart, InventoryService $inventory): JsonResponse
    {
        $data = $request->validated();
        $cart = $carts->findForUser($request->user()->id)->load('user');

        if (! empty($data['offer_id'])) {
            $offer = FlashOffer::query()->with('items.product.images')->findOrFail($data['offer_id']);
            $offerCart->addOfferToCart($cart, $offer, (int) $data['quantity']);
        } else {
            $product = Product::query()->with(['priceTiers', 'variants'])->where('status', true)->findOrFail($data['product_id']);
            $inventory->assertAvailableForUpdate($product, (int) $data['quantity'], $data['variant_id'] ?? null);
            $carts->addItem($cart, $product, (int) $data['quantity'], $data['variant_id'] ?? null);
        }

        return $this->success(new CartResource($cart->fresh()->load(['items.product.images', 'items.variant', 'items.offer'])), __('Cart updated successfully.'), 201);
    }

    public function update(UpdateCartItemRequest $request, int $item, CartRepository $carts, OfferCartService $offerCart, InventoryService $inventory): JsonResponse
    {
        $cart = $carts->findForUser($request->user()->id)->load('user');
        $cartItem = $cart->items()->with(['product.priceTiers', 'offer.items.product.images'])->whereKey($item)->firstOrFail();
        $quantity = (int) $request->validated('quantity');

        if ($cartItem->item_type === 'offer') {
            $offerCart->addOfferToCart($cart, $cartItem->offer, $quantity);
        } else {
            $inventory->assertAvailableForUpdate($cartItem->product, $quantity, $cartItem->variant_id);
            $carts->addItem($cart, $cartItem->product, $quantity, $cartItem->variant_id);
        }

        return $this->success(new CartResource($cart->fresh()->load(['items.product.images', 'items.variant', 'items.offer'])), __('Cart updated successfully.'));
    }

    public function destroy(Request $request, int $item, CartRepository $carts): JsonResponse
    {
        $cart = $carts->findForUser($request->user()->id);
        $cart->items()->whereKey($item)->delete();

        return $this->success(new CartResource($cart->fresh()->load(['items.product.images', 'items.variant', 'items.offer'])), __('Cart item removed.'));
    }

    public function clear(Request $request, CartRepository $carts): JsonResponse
    {
        $cart = $carts->findForUser($request->user()->id);
        $cart->items()->delete();

        return $this->success(new CartResource($cart->fresh()->load(['items.product.images', 'items.variant', 'items.offer'])), __('Cart cleared.'));
    }
}
