<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __invoke(Request $request, ProductService $products): View
    {
        $filters = $request->only(['search', 'category_id', 'brand_id', 'min_price', 'max_price', 'sort']);

        return view('storefront.shop', [
            'products' => $products->list($filters, (int) $request->integer('per_page', 12))->withQueryString(),
            'filters' => $filters,
            'categories' => Category::where('status', true)->orderBy('name')->get(),
            'brands' => Brand::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
