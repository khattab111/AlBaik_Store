<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCartItemRequest;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected InventoryService $inventory,
    ) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartRepository->findForUser(auth()->id());

        return response()->json(['items' => $this->cartRepository->items($cart)]);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $cart = $this->cartRepository->findForUser(auth()->id());
        $product = Product::findOrFail($request->product_id);
        $this->inventory->assertAvailable($product, $request->quantity, $request->variant_id);

        $item = $this->cartRepository->addItem($cart, $product, $request->quantity, $request->variant_id);

        return response()->json(['item' => $item], 201);
    }

    public function destroy($item): JsonResponse
    {
        $cartItem = auth()->user()->cart?->items()->findOrFail($item);
        $cartItem->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
