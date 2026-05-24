<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $locale = app()->getLocale();
        $productRelations = ['brand', 'category', 'images', 'reviews'];

        $data = Cache::remember('storefront.home.'.app()->getLocale().'.v1', now()->addMinutes(10), fn (): array => [
            'banners' => Banner::where('is_active', true)
                ->where('placement', 'home')
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('sort_order')
                ->get(),
            'brands' => Brand::where('status', true)->withCount('products')->orderBy("name->{$locale}")->take(8)->get(),
            'categories' => Category::where('status', true)->whereNull('parent_id')->withCount('products')->orderBy("name->{$locale}")->take(8)->get(),
            'featuredProducts' => Product::with($productRelations)->where('status', true)->where('is_featured', true)->latest()->take(8)->get(),
            'latestProducts' => Product::with($productRelations)->where('status', true)->latest()->take(8)->get(),
            'bestSellingProducts' => Product::with($productRelations)->where('status', true)->withCount('orderItems')->orderByDesc('order_items_count')->latest()->take(8)->get(),
            'topRatedProducts' => Product::with($productRelations)->where('status', true)->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating')->latest()->take(8)->get(),
            'flashSales' => FlashSale::with(['products.images', 'products.brand', 'products.reviews'])
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->latest()
                ->take(3)
                ->get(),
            'storeStats' => [
                ['value' => Product::where('status', true)->count(), 'label' => __('Products')],
                ['value' => Brand::where('status', true)->count(), 'label' => __('Brands')],
                ['value' => Order::count(), 'label' => __('Orders')],
                ['value' => 99, 'suffix' => '%', 'label' => __('Customer Satisfaction')],
            ],
        ]);

        return view('home', $data);
    }
}
