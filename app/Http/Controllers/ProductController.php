<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['brand', 'category', 'images', 'reviews', 'flashSales'])
            ->where('status', true);

        $query->when($request->filled('search'), fn ($builder) => $builder->where('name', 'like', '%'.$request->string('search').'%'));
        $query->when($request->filled('category'), fn ($builder) => $builder->whereHas('category', fn ($category) => $category->where('slug', $request->input('category'))));
        $query->when($request->filled('brand'), fn ($builder) => $builder->whereHas('brand', fn ($brand) => $brand->where('slug', $request->input('brand'))));
        $query->when($request->filled('min_price'), fn ($builder) => $builder->where('retail_price', '>=', (float) $request->input('min_price')));
        $query->when($request->filled('max_price'), fn ($builder) => $builder->where('retail_price', '<=', (float) $request->input('max_price')));
        $query->when($request->boolean('in_stock'), fn ($builder) => $builder->where('stock_quantity', '>', 0));
        $query->when($request->boolean('on_sale'), fn ($builder) => $builder->whereHas('flashSales', fn ($sale) => $sale->where('is_active', true)));

        match ($request->input('sort', 'latest')) {
            'price_desc' => $query->orderByDesc('retail_price'),
            'price_asc' => $query->orderBy('retail_price'),
            'best_selling' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            'top_rated' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            default => $query->latest(),
        };

        return view('products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::where('status', true)->orderBy('name')->get(),
            'brands' => Brand::where('status', true)->orderBy('name')->get(),
            'filters' => $request->query(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status, 404);

        $product->load(['brand', 'category', 'images', 'variants', 'reviews.user', 'flashSales']);

        return view('products.show', [
            'product' => $product,
            'similarProducts' => Product::with(['images', 'brand'])->where('status', true)->whereKeyNot($product->id)->where('category_id', $product->category_id)->latest()->take(4)->get(),
            'brandProducts' => Product::with(['images', 'brand'])->where('status', true)->whereKeyNot($product->id)->where('brand_id', $product->brand_id)->latest()->take(4)->get(),
        ]);
    }
}
