<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\BrandFilterRequest;
use App\Models\Banner;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index(BrandFilterRequest $request): View
    {
        $locale = app()->getLocale();
        $filters = $request->filters();
        $brands = Brand::where('status', true)
            ->withCount(['products' => fn ($query) => $query->where('status', true)])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where("name->{$locale}", 'like', '%'.$search.'%'));

        match ($filters['sort'] ?? 'name') {
            'products_desc' => $brands->orderByDesc('products_count')->orderBy("name->{$locale}"),
            'latest' => $brands->latest(),
            default => $brands->orderBy("name->{$locale}"),
        };

        $brands = $brands
            ->paginate(24)
            ->withQueryString();

        return view('brands.index', [
            'brands' => $brands,
            'filters' => $filters,
            'pageBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_BRANDS_TOP)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Brand $brand): View
    {
        abort_unless($brand->status, 404);
        $brand = Cache::remember("storefront.brand.{$brand->id}.v1", now()->addMinutes(10), fn () => $brand->loadCount(['products' => fn ($query) => $query->where('status', true)]));

        return view('brands.show', [
            'brand' => $brand,
            'products' => $brand->products()->with(['images', 'brand', 'category', 'reviews'])->where('status', true)->latest()->paginate(12),
            'pageBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_BRANDS_TOP)->orderBy('sort_order')->get(),
        ]);
    }
}
