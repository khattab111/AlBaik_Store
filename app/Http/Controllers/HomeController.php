<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'banners' => Banner::where('is_active', true)->where('placement', 'home')->orderBy('sort_order')->get(),
            'brands' => Brand::where('status', true)->withCount('products')->orderBy('name')->take(8)->get(),
            'categories' => Category::where('status', true)->whereNull('parent_id')->withCount('products')->orderBy('name')->take(8)->get(),
            'featuredProducts' => Product::with(['brand', 'category', 'images', 'reviews'])->where('status', true)->where('is_featured', true)->latest()->take(8)->get(),
            'flashSales' => FlashSale::with('products.images', 'products.brand')
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
