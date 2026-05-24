<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
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

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        $favorite = Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('status', __('Product removed from wishlist.'));
        }

        Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $product->id]);

        return back()->with('status', __('Product added to wishlist.'));
    }
}
