<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\FlashOfferService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(FlashOfferService $flashOffers): View
    {
        $locale = app()->getLocale();
        $productRelations = ['brand', 'category', 'images', 'reviews'];

        $data = Cache::remember('storefront.home.'.app()->getLocale().'.v2', now()->addMinutes(10), fn (): array => [
            'banners' => Banner::where('is_active', true)
                ->forPlacement(Banner::PLACEMENT_HOME_HERO)
                ->activeNow()
                ->orderBy('sort_order')
                ->get(),
            'homeAfterHeroBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_HOME_AFTER_HERO)->orderBy('sort_order')->get(),
            'homeBeforeProductsBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_HOME_BEFORE_PRODUCTS)->orderBy('sort_order')->get(),
            'brands' => Brand::where('status', true)->withCount('products')->orderBy("name->{$locale}")->take(8)->get(),
            'categories' => Category::where('status', true)->whereNull('parent_id')->withCount('products')->orderBy("name->{$locale}")->take(8)->get(),
            'featuredProducts' => Product::with($productRelations)->where('status', true)->where('is_featured', true)->latest()->take(8)->get(),
            'latestProducts' => Product::with($productRelations)->where('status', true)->latest()->take(8)->get(),
            'bestSellingProducts' => Product::with($productRelations)->where('status', true)->withCount('orderItems')->orderByDesc('order_items_count')->latest()->take(8)->get(),
            'topRatedProducts' => Product::with($productRelations)->where('status', true)->orderByDesc('average_rating')->orderByDesc('reviews_count')->latest()->take(8)->get(),
            'flashOffers' => $flashOffers->getActiveOffers()->take(3),
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
