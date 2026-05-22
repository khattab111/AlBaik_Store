<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Repositories\CartRepository;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $service,
        protected CartRepository $cartRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        return OrderResource::collection($this->service->listForUser($request->user()->id));
    }

    public function show(Request $request, $order): JsonResponse
    {
        $model = $this->service->getForUser($request->user()->id, $order);

        return new OrderResource($model);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = app(CreateOrderFromCart::class)->handle(CheckoutData::fromRequest($request));

        return (new OrderResource($order))->additional(['message' => 'Order created'])->response()->setStatusCode(201);
    }
}
