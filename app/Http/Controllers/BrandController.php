<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Contracts\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('brands.index', [
            'brands' => Brand::where('status', true)->withCount('products')->orderBy('name')->paginate(24),
        ]);
    }

    public function show(Brand $brand): View
    {
        abort_unless($brand->status, 404);

        return view('brands.show', [
            'brand' => $brand,
            'products' => $brand->products()->with(['images', 'brand', 'category'])->where('status', true)->latest()->paginate(12),
        ]);
    }
}
