@extends('layouts.app')

@section('title', ($isWholesaleAccount ?? false) ? __('Wholesale account') : __('Account'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ ($isWholesaleAccount ?? false) ? __('Wholesale account') : __('Account') }}</span>
    </nav>

    <div class="store-page-hero mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="store-eyebrow">{{ ($isWholesaleAccount ?? false) ? __('Wholesale center') : __('Customer center') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ ($isWholesaleAccount ?? false) ? __('Wholesale account') : __('Account') }}</h1>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">
                {{ ($isWholesaleAccount ?? false)
                    ? __('Manage wholesale orders, quantity deals, business addresses, and account details from one place.')
                    : __('Manage orders, addresses, saved products, and account details from one place.') }}
            </p>
        </div>
        <nav class="flex flex-wrap gap-2">
            @if($isWholesaleAccount ?? false)
                <a href="{{ route('wholesale.products.index') }}" class="store-button-primary">{{ __('Wholesale products') }}</a>
                <a href="{{ route('wholesale.offers.index') }}" class="store-button-secondary">{{ __('Wholesale offers') }}</a>
            @endif
            <a href="{{ route('account.profile.edit') }}" class="store-button-secondary">{{ __('Profile') }}</a>
            <a href="{{ route('account.wallet.index') }}" class="store-button-secondary">{{ __('My Wallet') }}</a>
            <a href="{{ route('account.addresses.index') }}" class="store-button-secondary">{{ __('Addresses') }}</a>
            <a href="{{ ($isWholesaleAccount ?? false) ? route('wholesale.orders.index') : route('orders.index') }}" class="store-button-secondary">{{ __('Orders') }}</a>
        </nav>
    </div>

    <section class="grid gap-4 md:grid-cols-2 {{ ($isWholesaleAccount ?? false) ? 'xl:grid-cols-5' : 'xl:grid-cols-4' }}">
        <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Orders') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $ordersCount }}</p></div>
        <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Addresses') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $addressesCount }}</p></div>
        <a href="{{ route('account.wallet.index') }}" class="store-panel p-6 transition hover:-translate-y-1 hover:shadow-lg">
            <p class="text-sm font-bold text-slate-500">{{ __('Wallet') }}</p>
            <p class="store-safe-text mt-2 text-3xl font-black text-red-700">{{ number_format((float) $wallet->balance, 2) }}</p>
            <p class="mt-1 text-xs font-black text-slate-500">{{ $wallet->currency_code ?: __('Currency') }}</p>
        </a>
        @if($isWholesaleAccount ?? false)
            <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Wholesale products') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $wholesaleProductsCount }}</p></div>
            <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Wholesale offers') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $wholesaleOffersCount }}</p></div>
        @else
            <div class="store-panel p-6"><p class="text-sm font-bold text-slate-500">{{ __('Wishlist') }}</p><p class="mt-2 text-4xl font-black text-red-700">{{ $wishlistCount }}</p></div>
        @endif
    </section>

    <section class="store-panel mt-8 p-6">
        <h2 class="text-2xl font-black">{{ __('Latest Orders') }}</h2>
        <div class="mt-5 grid gap-3">
            @forelse($latestOrders as $order)
                <a href="{{ ($isWholesaleAccount ?? false) ? route('wholesale.orders.show', $order) : route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-red-200">
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
