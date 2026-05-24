<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductPricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $displayMode = in_array($request->query('view'), ['grid', 'list'], true)
            ? $request->query('view')
            : 'grid';

        $query = Product::with(['brand', 'category', 'images', 'reviews', 'flashSales'])
            ->where('status', true);

        $query->when($request->filled('search'), fn ($builder) => $builder->where("name->{$locale}", 'like', '%'.$request->string('search').'%'));
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
            'categories' => Category::where('status', true)->orderBy("name->{$locale}")->get(),
            'brands' => Brand::where('status', true)->orderBy("name->{$locale}")->get(),
            'filters' => $request->query(),
            'displayMode' => $displayMode,
        ]);
    }

    public function latest(Request $request): View
    {
        $request->merge(['sort' => 'latest']);

        return $this->index($request)->with('pageTitle', __('New Arrivals'));
    }

    public function show(Product $product, Request $request, ProductPricingService $pricing): View
    {
        abort_unless($product->status, 404);

        $product->load(['brand', 'category', 'images', 'variants', 'reviews.user', 'flashSales', 'priceTiers']);
        $isWholesaleCustomer = (bool) $request->user()?->isWholesaleCustomer();

        return view('products.show', [
            'product' => $product,
            'pricing' => $pricing->getPriceForUser($product, $request->user(), 1),
            'isWholesaleCustomer' => $isWholesaleCustomer,
            'wholesaleTiers' => $isWholesaleCustomer
                ? $product->priceTiers->where('is_active', true)->where('type', 'wholesale')->sortBy('min_quantity')->values()
                : collect(),
            'similarProducts' => Product::with(['images', 'brand', 'reviews'])->where('status', true)->whereKeyNot($product->id)->where('category_id', $product->category_id)->latest()->take(4)->get(),
            'brandProducts' => Product::with(['images', 'brand', 'reviews'])->where('status', true)->whereKeyNot($product->id)->where('brand_id', $product->brand_id)->latest()->take(4)->get(),
        ]);
    }
}
