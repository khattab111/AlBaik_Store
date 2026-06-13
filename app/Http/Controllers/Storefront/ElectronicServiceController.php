<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ElectronicService;
use App\Models\ElectronicServiceCategory;
use App\Models\ElectronicServiceOrder;
use App\Services\ElectronicServices\ElectronicServiceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElectronicServiceController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();

        $categories = ElectronicServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $services = ElectronicService::query()
            ->with(['category', 'provider'])
            ->where('is_active', true)
            ->where('is_visible', true)
            ->where('is_available', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($categorySlug, fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $categorySlug)))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('services.index', compact('categories', 'services', 'categorySlug'));
    }

    public function show(ElectronicService $service): View
    {
        abort_unless($service->is_active && $service->is_visible && $service->is_available && $service->category?->is_active, 404);

        $service->load(['category', 'provider']);

        return view('services.show', compact('service'));
    }

    public function orders(Request $request): View
    {
        $orders = ElectronicServiceOrder::query()
            ->with(['service', 'provider'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('services.orders', compact('orders'));
    }

    public function store(Request $request, ElectronicService $service, ElectronicServiceOrderService $orders): RedirectResponse
    {
        abort_unless($service->is_active && $service->is_visible && $service->is_available && $service->category?->is_active, 404);

        $order = $orders->create($request->user(), $service->load(['category', 'provider']), $request->input('fields', []));

        return redirect()
            ->route('services.orders.index')
            ->with('status', __('Your service order #:number has been created and is waiting for processing.', ['number' => $order->order_number]));
    }
}
