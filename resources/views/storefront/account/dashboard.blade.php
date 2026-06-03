@extends('layouts.app')

@section('title', __('Account'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Account') }}</span>
    </nav>

    <div class="store-page-hero mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="store-eyebrow">{{ __('Customer center') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Account') }}</h1>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Manage orders, addresses, saved products, and account details from one place.') }}</p>
        </div>
        <nav class="flex flex-wrap gap-2">
            <a href="{{ route('account.profile.edit') }}" class="store-button-secondary">{{ __('Profile') }}</a>
            <a href="{{ route('account.addresses.index') }}" class="store-button-secondary">{{ __('Addresses') }}</a>
            <a href="{{ route('orders.index') }}" class="store-button-secondary">{{ __('Orders') }}</a>
        </nav>
    </div>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Orders') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $ordersCount }}</p></div>
        <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Addresses') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $addressesCount }}</p></div>
        <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Wishlist') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $wishlistCount }}</p></div>
    </section>

    <section class="store-panel mt-8 p-6">
        <h2 class="text-2xl font-black">{{ __('Latest Orders') }}</h2>
        <div class="mt-5 grid gap-3">
            @forelse($latestOrders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-red-200">
                    <span class="font-black">{{ $order->order_number }}</span>
                    <span class="text-sm font-bold text-slate-500">{{ __($order->status) }}</span>
                    <span class="font-black text-red-700">{{ store_money((float) $order->total) }}</span>
                </a>
            @empty
                <p class="text-slate-500">{{ __('No orders found.') }}</p>
            @endforelse
        </div>
    </section>
</section>
@endsection
