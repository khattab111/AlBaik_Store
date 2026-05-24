<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $brands = Brand::where('status', true)
            ->withCount(['products' => fn ($query) => $query->where('status', true)])
            ->when($request->filled('search'), fn ($query) => $query->where("name->{$locale}", 'like', '%'.$request->string('search').'%'))
            ->orderBy("name->{$locale}")
            ->paginate(24)
            ->withQueryString();

        return view('brands.index', [
            'brands' => $brands,
            'filters' => $request->query(),
        ]);
    }

    public function show(Brand $brand): View
    {
        abort_unless($brand->status, 404);
        $brand = Cache::remember("storefront.brand.{$brand->id}.v1", now()->addMinutes(10), fn () => $brand->loadCount(['products' => fn ($query) => $query->where('status', true)]));

        return view('brands.show', [
            'brand' => $brand,
            'products' => $brand->products()->with(['images', 'brand', 'category', 'reviews'])->where('status', true)->latest()->paginate(12),
        ]);
    }
}
