@extends('layouts.app')

@section('title', __('Orders'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('account.dashboard') }}" class="transition hover:text-red-700">{{ __('Account') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Orders') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Account') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Orders') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Track orders, payment status, and delivery details from your account.') }}</p>
    </div>

    <div class="grid gap-4">
        @forelse($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="store-panel grid gap-4 p-5 transition hover:-translate-y-1 hover:shadow-lg md:grid-cols-4">
                <strong class="store-safe-text text-lg">{{ $order->order_number }}</strong>
                <span class="font-bold text-slate-600">{{ __($order->status) }}</span>
                <span class="font-black text-red-700">{{ store_money((float) $order->total) }}</span>
                <span class="text-sm font-bold text-slate-500">{{ $order->created_at->format('Y-m-d') }}</span>
            </a>
        @empty
            <div class="store-panel p-12 text-center">{{ __('No orders found.') }}</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $orders->links() }}</div>
</section>
@endsection
