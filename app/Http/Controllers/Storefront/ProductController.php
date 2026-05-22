<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->status, 404);

        return view('storefront.product', [
            'product' => $product->load(['brand', 'supplier', 'category', 'images', 'variants', 'reviews.user']),
            'relatedProducts' => Product::with(['brand', 'images'])
                ->where('status', true)
                ->where('id', '!=', $product->id)
                ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }
}
