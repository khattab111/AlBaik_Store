<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Contracts\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('storefront.brands', [
            'brands' => Brand::where('status', true)->withCount('products')->orderBy('name')->paginate(24),
        ]);
    }

    public function show(Brand $brand): View
    {
        abort_unless($brand->status, 404);

        return view('storefront.brand', [
            'brand' => $brand,
            'products' => $brand->products()->with(['images', 'category'])->where('status', true)->latest()->paginate(12),
        ]);
    }
}
