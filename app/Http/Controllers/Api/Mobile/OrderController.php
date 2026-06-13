<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use RespondsToMobile;

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'paymentMethod', 'shippingCarrier'])
            ->latest('id')
            ->paginate((int) $request->input('per_page', 12));

        return $this->success($this->paginated($orders, OrderResource::class));
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->with(['items.product', 'items.offer', 'paymentMethod', 'shippingCarrier', 'payments', 'timeline'])
            ->whereKey($order)
            ->firstOrFail();

        return $this->success(new OrderResource($order));
    }
}
