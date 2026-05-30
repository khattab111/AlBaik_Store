<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\ToggleFavoriteRequest;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        return view('favorites.index', [
            'items' => $request->user()->wishlist()->with('product.images', 'product.brand', 'product.reviews')->latest()->paginate(24),
        ]);
    }

    public function toggle(ToggleFavoriteRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($product->status, 404);

        $favorite = Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->first();

        if ($favorite) {
            $favorite->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Product removed from wishlist.'),
                    'action' => 'removed',
                    'wishlist_count' => $request->user()->wishlist()->count(),
                    'cart_count' => (int) ($request->user()->cart?->items()->sum('quantity') ?? 0),
                ]);
            }

            return back()->with('status', __('Product removed from wishlist.'));
        }

        Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $product->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Product added to wishlist.'),
                'action' => 'added',
                'wishlist_count' => $request->user()->wishlist()->count(),
                'cart_count' => (int) ($request->user()->cart?->items()->sum('quantity') ?? 0),
            ]);
        }

        return back()->with('status', __('Product added to wishlist.'));
    }
}
