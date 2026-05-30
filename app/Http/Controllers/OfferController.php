<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\OfferFilterRequest;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\FlashOfferService;
use Illuminate\Contracts\View\View;

class OfferController extends Controller
{
    public function index(OfferFilterRequest $request, FlashOfferService $flashOffers): View
    {
        $locale = app()->getLocale();
        $filters = $request->filters();
        $products = Product::with(['brand', 'category', 'images', 'reviews', 'priceTiers'])
            ->where('status', true)
            ->whereHas('flashOfferItems.flashOffer', fn ($offer) => $offer->currentlyValid());

        $products->when($filters['category'] ?? null, fn ($builder, $categorySlug) => $builder->whereHas('category', fn ($category) => $category->where('slug', $categorySlug)));
        $products->when($filters['brand'] ?? null, fn ($builder, $brandSlug) => $builder->whereHas('brand', fn ($brand) => $brand->where('slug', $brandSlug)));

        match ($filters['sort'] ?? 'latest') {
            'price_desc' => $products->orderByDesc('retail_price'),
            'price_asc' => $products->orderBy('retail_price'),
            default => $products->latest(),
        };

        return view('offers.index', [
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => Category::where('status', true)->orderBy("name->{$locale}")->get(),
            'brands' => Brand::where('status', true)->orderBy("name->{$locale}")->get(),
            'filters' => $filters,
            'flashOffers' => $flashOffers->getActiveOffers(),
            'pageBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_OFFERS_TOP)->orderBy('sort_order')->get(),
        ]);
    }
}
