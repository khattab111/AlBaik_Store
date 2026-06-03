<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\ProductFilterRequest;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Presenters\FlashOfferPresenter;
use App\Services\FlashOfferService;
use App\Services\ProductPricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(ProductFilterRequest $request): View
    {
        return $this->productIndexView($request, $request->filters());
    }

    public function latest(ProductFilterRequest $request): View
    {
        return $this->productIndexView($request, [
            ...$request->filters(),
            'sort' => 'latest',
        ])->with('pageTitle', __('New Arrivals'));
    }

    private function productIndexView(Request $request, array $filters): View
    {
        $locale = app()->getLocale();
        $displayMode = $filters['view'] ?? 'grid';
        $perPage = (int) ($filters['per_page'] ?? 12);

        $query = Product::with(['brand', 'category', 'images', 'reviews', 'priceTiers'])
            ->where('status', true);

        $query->when($filters['search'] ?? null, fn ($builder, $search) => $builder->where("name->{$locale}", 'like', '%'.$search.'%'));
        $query->when($filters['category'] ?? null, fn ($builder, $categorySlug) => $builder->whereHas('category', fn ($category) => $category->where('slug', $categorySlug)));
        $query->when($filters['brand'] ?? null, fn ($builder, $brandSlug) => $builder->whereHas('brand', fn ($brand) => $brand->where('slug', $brandSlug)));
        $query->when($filters['min_price'] ?? null, fn ($builder, $price) => $builder->where('retail_price', '>=', (float) $price));
        $query->when($filters['max_price'] ?? null, fn ($builder, $price) => $builder->where('retail_price', '<=', (float) $price));
        $query->when($request->boolean('in_stock'), fn ($builder) => $builder->where('stock_quantity', '>', 0));
        $query->when($request->boolean('on_sale'), fn ($builder) => $builder->whereHas('flashOfferItems.flashOffer', fn ($offer) => $offer->currentlyValid()));

        match ($filters['sort'] ?? 'latest') {
            'price_desc' => $query->orderByDesc('retail_price'),
            'price_asc' => $query->orderBy('retail_price'),
            'best_selling' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            'top_rated' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            default => $query->latest(),
        };

        return view('products.index', [
            'products' => $query->paginate(in_array($perPage, [12, 24, 36, 48], true) ? $perPage : 12)->withQueryString(),
            'categories' => Category::where('status', true)
                ->withCount(['products' => fn ($builder) => $builder->where('status', true)])
                ->orderBy("name->{$locale}")
                ->get(),
            'brands' => Brand::where('status', true)
                ->withCount(['products' => fn ($builder) => $builder->where('status', true)])
                ->orderBy("name->{$locale}")
                ->get(),
            'filters' => $filters,
            'displayMode' => $displayMode,
            'pageBanners' => Banner::activeNow()->forPlacement(['shop', Banner::PLACEMENT_PRODUCTS_TOP])->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Product $product, Request $request, ProductPricingService $pricing, FlashOfferPresenter $offers, FlashOfferService $flashOffers): View
    {
        abort_unless($product->status, 404);

        $product->load([
            'brand',
            'category',
            'supplier',
            'tags',
            'images',
            'variants',
            'reviews.user',
            'priceTiers',
            'flashOfferItems.flashOffer.items.product.images',
        ]);
        $isWholesaleCustomer = (bool) $request->user()?->isWholesaleCustomer();
        $priceData = $pricing->getPriceForUser($product, $request->user(), 1);
        $activeOffers = $product->flashOfferItems
            ->pluck('flashOffer')
            ->filter(fn ($offer) => $offer && $flashOffers->isOfferValid($offer))
            ->unique('id')
            ->values();

        return view('products.show', [
            'product' => $product,
            'pricing' => $priceData,
            'activeOfferDetails' => $activeOffers->map(fn ($offer) => $offers->forProduct($offer, $product))->values(),
            'primaryOfferDetails' => $priceData->flashOffer ? $offers->forProduct($priceData->flashOffer, $product) : null,
            'isWholesaleCustomer' => $isWholesaleCustomer,
            'wholesaleTiers' => $isWholesaleCustomer
                ? $product->priceTiers->where('is_active', true)->where('type', 'wholesale')->sortBy('min_quantity')->values()
                : collect(),
            'similarProducts' => Product::with(['images', 'brand', 'reviews'])->where('status', true)->whereKeyNot($product->id)->where('category_id', $product->category_id)->latest()->take(4)->get(),
            'brandProducts' => Product::with(['images', 'brand', 'reviews'])->where('status', true)->whereKeyNot($product->id)->where('brand_id', $product->brand_id)->latest()->take(4)->get(),
            'recommendedProducts' => Product::with(['images', 'brand', 'reviews'])
                ->where('status', true)
                ->whereKeyNot($product->id)
                ->where(function ($query) use ($product): void {
                    $query->where('is_featured', true)
                        ->orWhere('category_id', '!=', $product->category_id);
                })
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }
}
