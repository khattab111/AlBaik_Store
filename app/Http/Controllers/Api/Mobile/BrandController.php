<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\BrandResource;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    use RespondsToMobile;

    public function index(): JsonResponse
    {
        $brands = Brand::query()
            ->where('status', true)
            ->withCount(['products' => fn ($query) => $query->where('status', true)])
            ->orderBy('id')
            ->paginate((int) request('per_page', 24));

        return $this->success($this->paginated($brands, BrandResource::class));
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->withCount(['products' => fn ($query) => $query->where('status', true)])
            ->firstOrFail();

        $products = $brand->products()
            ->where('status', true)
            ->with(['brand', 'category', 'images', 'priceTiers'])
            ->latest('id')
            ->paginate((int) request('per_page', 12));

        return $this->success([
            'brand' => new BrandResource($brand),
            'products' => $this->paginated($products, ProductResource::class),
        ]);
    }
}
