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
        $receiptPath = $request->hasFile('payment_receipt')
            ? $request->file('payment_receipt')->store('payment-receipts', 'public')
            : null;

        $order = app(CreateOrderFromCart::class)->handle(new CheckoutData(
            userId: $request->user()->id,
            paymentMethodId: (int) $request->input('payment_method_id'),
            addressMode: $request->input('address_mode', 'saved'),
            shippingCarrierId: $request->filled('shipping_carrier_id') ? (int) $request->input('shipping_carrier_id') : null,
            userAddressId: $request->filled('user_address_id') ? (int) $request->input('user_address_id') : null,
            shippingCityId: $request->filled('city_id') ? (int) $request->input('city_id') : null,
            temporaryAddress: $request->input('address'),
            saveAddress: $request->boolean('save_address'),
            addressLabel: $request->input('address_label'),
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
            paymentReceiptPath: $receiptPath,
        ));

        return (new OrderResource($order))->additional(['message' => 'Order created'])->response()->setStatusCode(201);
    }
}
