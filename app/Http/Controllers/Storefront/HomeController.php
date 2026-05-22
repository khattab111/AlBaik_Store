<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('storefront.home', [
            'banners' => Banner::where('is_active', true)->where('placement', 'home')->orderBy('sort_order')->get(),
            'featuredProducts' => Product::with(['brand', 'images'])->where('status', true)->where('is_featured', true)->latest()->take(8)->get(),
            'categories' => Category::where('status', true)->whereNull('parent_id')->orderBy('name')->take(10)->get(),
            'brands' => Brand::where('status', true)->orderBy('name')->take(10)->get(),
        ]);
    }
}
