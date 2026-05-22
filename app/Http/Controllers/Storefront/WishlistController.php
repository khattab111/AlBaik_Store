<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('storefront.wishlist', [
            'items' => $request->user()->wishlist()->with('product.images')->latest()->paginate(24),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return back()->with('status', __('Product added to wishlist.'));
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();

        return back()->with('status', __('Product removed from wishlist.'));
    }
}
