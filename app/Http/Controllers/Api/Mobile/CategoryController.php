<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\CategoryResource;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use RespondsToMobile;

    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->where('status', true)->orderBy('id')])
            ->withCount(['products' => fn ($query) => $query->where('status', true)])
            ->orderBy('id')
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->with(['children' => fn ($query) => $query->where('status', true)->orderBy('id')])
            ->withCount(['products' => fn ($query) => $query->where('status', true)])
            ->firstOrFail();

        $products = $category->products()
            ->where('status', true)
            ->with(['brand', 'category', 'images', 'priceTiers'])
            ->latest('id')
            ->paginate((int) request('per_page', 12));

        return $this->success([
            'category' => new CategoryResource($category),
            'products' => $this->paginated($products, ProductResource::class),
        ]);
    }
}
