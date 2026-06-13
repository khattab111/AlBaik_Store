<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\StoreElectronicServiceOrderRequest;
use App\Http\Resources\Api\Mobile\ElectronicServiceCategoryResource;
use App\Http\Resources\Api\Mobile\ElectronicServiceDetailResource;
use App\Http\Resources\Api\Mobile\ElectronicServiceOrderResource;
use App\Http\Resources\Api\Mobile\ElectronicServiceResource;
use App\Models\ElectronicService;
use App\Models\ElectronicServiceCategory;
use App\Models\ElectronicServiceOrder;
use App\Services\ElectronicServices\ElectronicServiceOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElectronicServiceController extends Controller
{
    use RespondsToMobile;

    public function categories(): JsonResponse
    {
        $categories = ElectronicServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services' => fn ($query) => $query->where('is_active', true)->where('is_visible', true)->where('is_available', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->success(ElectronicServiceCategoryResource::collection($categories));
    }

    public function index(Request $request): JsonResponse
    {
        $services = ElectronicService::query()
            ->with('category')
            ->where('is_active', true)
            ->where('is_visible', true)
            ->where('is_available', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $request->input('category'))->orWhereKey($request->input('category'))))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(fn ($builder) => $builder
                    ->where('name->ar', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%"));
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 12));

        return $this->success($this->paginated($services, ElectronicServiceResource::class));
    }

    public function show(string $slug): JsonResponse
    {
        $service = ElectronicService::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('is_visible', true)
            ->where('is_available', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();

        return $this->success(new ElectronicServiceDetailResource($service));
    }

    public function storeOrder(StoreElectronicServiceOrderRequest $request, string $slug, ElectronicServiceOrderService $orders): JsonResponse
    {
        $service = ElectronicService::query()
            ->with(['category', 'provider'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('is_visible', true)
            ->where('is_available', true)
            ->firstOrFail();

        $order = $orders->create($request->user(), $service, $request->validated('fields'));

        return $this->success(new ElectronicServiceOrderResource($order->load('service')), __('Service order created successfully.'), 201);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = ElectronicServiceOrder::query()
            ->with('service')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate((int) $request->input('per_page', 12));

        return $this->success($this->paginated($orders, ElectronicServiceOrderResource::class));
    }

    public function order(Request $request, int $order): JsonResponse
    {
        $order = ElectronicServiceOrder::query()
            ->with('service')
            ->where('user_id', $request->user()->id)
            ->whereKey($order)
            ->firstOrFail();

        return $this->success(new ElectronicServiceOrderResource($order));
    }
}
