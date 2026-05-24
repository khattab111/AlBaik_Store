<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $products = Product::with(['brand', 'category', 'images', 'reviews', 'flashSales'])
            ->where('status', true)
            ->whereHas('flashSales', fn ($sale) => $sale->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now())));

        $products->when($request->filled('category'), fn ($builder) => $builder->whereHas('category', fn ($category) => $category->where('slug', $request->input('category'))));
        $products->when($request->filled('brand'), fn ($builder) => $builder->whereHas('brand', fn ($brand) => $brand->where('slug', $request->input('brand'))));

        match ($request->input('sort', 'latest')) {
            'price_desc' => $products->orderByDesc('retail_price'),
            'price_asc' => $products->orderBy('retail_price'),
            default => $products->latest(),
        };

        return view('offers.index', [
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => Category::where('status', true)->orderBy("name->{$locale}")->get(),
            'brands' => Brand::where('status', true)->orderBy("name->{$locale}")->get(),
        ]);
    }
}
