<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\OfferFilterRequest;
use App\Http\Requests\Storefront\ProductFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Presenters\FlashOfferPresenter;
use App\Services\FlashOfferService;
use Illuminate\Contracts\View\View;

class WholesaleController extends Controller
{
    public function products(ProductFilterRequest $request): View
    {
        $locale = app()->getLocale();
        $filters = $request->filters();
        $displayMode = $filters['view'] ?? 'grid';
        $perPage = (int) ($filters['per_page'] ?? 12);

        $query = Product::query()
            ->with(['brand', 'category', 'images', 'reviews', 'priceTiers'])
            ->where('status', true)
            ->where('is_wholesale_available', true);

        $query->when($filters['search'] ?? null, fn ($builder, $search) => $builder->where("name->{$locale}", 'like', '%'.$search.'%'));
        $query->when($filters['category'] ?? null, fn ($builder, $categorySlug) => $builder->whereHas('category', fn ($category) => $category->where('slug', $categorySlug)));
        $query->when($filters['brand'] ?? null, fn ($builder, $brandSlug) => $builder->whereHas('brand', fn ($brand) => $brand->where('slug', $brandSlug)));
        $query->when($filters['min_price'] ?? null, fn ($builder, $price) => $builder->where(function ($priceQuery) use ($price): void {
            $priceQuery->where('wholesale_price', '>=', (float) $price)
                ->orWhereHas('priceTiers', fn ($tier) => $tier->where('type', 'wholesale')->where('is_active', true)->where('price', '>=', (float) $price));
        }));
        $query->when($filters['max_price'] ?? null, fn ($builder, $price) => $builder->where(function ($priceQuery) use ($price): void {
            $priceQuery->where('wholesale_price', '<=', (float) $price)
                ->orWhereHas('priceTiers', fn ($tier) => $tier->where('type', 'wholesale')->where('is_active', true)->where('price', '<=', (float) $price));
        }));
        $query->when($request->boolean('in_stock'), fn ($builder) => $builder->where('stock_quantity', '>', 0));
        $query->when($request->boolean('on_sale'), fn ($builder) => $builder->whereHas('flashOfferItems.flashOffer', fn ($offer) => $offer->currentlyValid()->forAudience(FlashOffer::AUDIENCE_WHOLESALE)));

        match ($filters['sort'] ?? 'latest') {
            'price_desc' => $query->orderByDesc('wholesale_price')->orderByDesc('retail_price'),
            'price_asc' => $query->orderBy('wholesale_price')->orderBy('retail_price'),
            'best_selling' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            'top_rated' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            default => $query->latest(),
        };

        return view('wholesale.products', [
            'products' => $query->paginate(in_array($perPage, [12, 24, 36, 48], true) ? $perPage : 12)->withQueryString(),
            'categories' => Category::where('status', true)
                ->withCount(['products' => fn ($builder) => $builder->where('status', true)->where('is_wholesale_available', true)])
                ->orderBy("name->{$locale}")
                ->get(),
            'brands' => Brand::where('status', true)
                ->withCount(['products' => fn ($builder) => $builder->where('status', true)->where('is_wholesale_available', true)])
                ->orderBy("name->{$locale}")
                ->get(),
            'filters' => $filters,
            'displayMode' => $displayMode,
        ]);
    }

    public function offers(OfferFilterRequest $request, FlashOfferPresenter $presenter, FlashOfferService $flashOffers): View
    {
        $locale = app()->getLocale();
        $filters = $request->filters();
        $perPage = (int) ($filters['per_page'] ?? 12);
        $displayMode = $filters['view'] ?? 'grid';

        $offers = FlashOffer::query()
            ->with(['items.product.images', 'items.product.brand', 'items.product.category', 'items.product.reviews'])
            ->currentlyValid()
            ->forAudience(FlashOffer::AUDIENCE_WHOLESALE)
            ->whereHas('items.product', fn ($product) => $product->where('is_wholesale_available', true));

        $offers->when($filters['category'] ?? null, fn ($builder, $categorySlug) => $builder
            ->whereHas('items.product.category', fn ($category) => $category->where('slug', $categorySlug)));
        $offers->when($filters['brand'] ?? null, fn ($builder, $brandSlug) => $builder
            ->whereHas('items.product.brand', fn ($brand) => $brand->where('slug', $brandSlug)));
        $offers->when($filters['type'] ?? null, fn ($builder, $type) => $builder->where('type', $type));

        match ($filters['sort'] ?? 'latest') {
            'price_desc' => $offers->orderByDesc('fixed_price')->orderByDesc('discount_value'),
            'price_asc' => $offers->orderBy('fixed_price')->orderBy('discount_value'),
            'ending_soon' => $offers->orderByRaw('ends_at IS NULL')->orderBy('ends_at'),
            'highest_discount' => $offers->orderByDesc('discount_value'),
            'best_selling' => $offers->orderByDesc('sold_quantity'),
            default => $offers->latest(),
        };

        $offersPaginator = $offers->paginate($perPage)->withQueryString();
        $presentedOffers = $offersPaginator->getCollection()
            ->map(fn (FlashOffer $offer): array => array_merge($presenter->forOffer($offer), [
                'product' => $offer->items->pluck('product')->filter()->first(),
            ]))
            ->values();

        return view('wholesale.offers', [
            'offersPaginator' => $offersPaginator,
            'presentedOffers' => $presentedOffers,
            'categories' => Category::where('status', true)->orderBy("name->{$locale}")->get(),
            'brands' => Brand::where('status', true)->orderBy("name->{$locale}")->get(),
            'filters' => $filters,
            'displayMode' => $displayMode,
            'flashOffers' => $flashOffers->getActiveOffers(FlashOffer::AUDIENCE_WHOLESALE),
        ]);
    }
}
