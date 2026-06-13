<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use RespondsToMobile;

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('status', true)
            ->whereHas('wishlist', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['brand', 'category', 'images', 'priceTiers'])
            ->latest('id')
            ->paginate((int) $request->input('per_page', 12));

        return $this->success($this->paginated($products, ProductResource::class));
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->status, 404);

        Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return $this->success(null, __('Added to favorites.'));
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return $this->success(null, __('Removed from favorites.'));
    }
}
