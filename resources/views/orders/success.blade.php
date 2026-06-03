@extends('layouts.app')

@section('title', __('Order Success'))
@section('meta_description', __('Your order has been created successfully. Track payment review, processing, and shipping status.'))

@section('content')
<section class="store-section">
    <div class="store-panel mx-auto max-w-3xl p-10 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-4xl text-emerald-700" aria-hidden="true">✓</div>
        <h1 class="mt-6 text-4xl font-black">{{ __('Order created successfully.') }}</h1>
        <p class="mt-3 text-slate-600">{{ __('We received your order and will review payment and shipping details shortly.') }}</p>
        <div class="mt-8 grid gap-3 rounded-3xl bg-slate-50 p-5 text-start text-sm font-bold">
            <div class="flex justify-between gap-4"><span class="text-slate-500">{{ __('Order number') }}</span><span>{{ $order->order_number }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-500">{{ __('Status') }}</span><span>{{ __($order->status) }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-500">{{ __('Total') }}</span><span>{{ store_money((float) $order->total) }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-500">{{ __('Payment method') }}</span><span>{{ $order->paymentMethod?->name }}</span></div>
        </div>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('orders.show', $order) }}" class="store-button-primary">{{ __('View Order') }}</a>
            <a href="{{ route('products.index') }}" class="store-button-secondary">{{ __('Continue Shopping') }}</a>
        </div>
    </div>
</section>
@endsection
