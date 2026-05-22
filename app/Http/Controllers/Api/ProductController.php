<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'brand_id', 'min_price', 'max_price', 'sort']);

        return ProductResource::collection($this->service->list($filters, $request->get('per_page', 12)));
    }

    public function show($product)
    {
        $model = $this->service->get($product);

        return new ProductResource($model);
    }
}
