@extends('layouts.app')

@section('title', __('Cart'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Cart') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Checkout path') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Cart') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Review quantities, product availability, and checkout totals before placing your order.') }}</p>
    </div>

    @if($items->isEmpty())
        <div class="store-panel p-12 text-center">
            <h2 class="text-2xl font-black">{{ __('Cart is empty.') }}</h2>
            <p class="mt-3 text-slate-500">{{ __('Start with products or active offers.') }}</p>
            <a href="{{ route('products.index') }}" class="store-button-primary mt-6">{{ __('Browse Products') }}</a>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="grid gap-4">
                @foreach($items as $item)
                    @php
                        $image = $item->product->images->first()?->path;
                        $imageUrl = $image && file_exists(public_path('storage/'.$image))
                            ? asset('storage/'.$image)
                            : asset('images/storefront/product-fallback.svg');
                    @endphp
                    <article class="store-panel grid gap-4 p-4 sm:grid-cols-[120px_1fr_auto]">
                        <img src="{{ $imageUrl }}" class="h-28 w-28 rounded-2xl object-cover" alt="{{ $item->product->name }}">
                        <div>
                            <h2 class="text-lg font-black">{{ $item->product->name }}</h2>
                            <p class="mt-1 text-sm font-bold text-slate-500">{{ $item->product->brand?->name }}</p>
                            @if($item->variant)
                                <p class="mt-2 text-xs font-bold text-slate-500">{{ $item->variant->sku }}</p>
                            @endif
                            <p class="mt-3 store-price">USD {{ number_format((float)$item->unit_price, 2) }}</p>
                        </div>
                        <div class="grid content-between gap-3 sm:min-w-48">
                            <form method="POST" action="{{ route('cart.update', $item->product) }}" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="store-field w-24">
                                <button class="store-button-secondary">{{ __('Update') }}</button>
                            </form>
                            <form method="POST" action="{{ route('cart.remove', $item->product) }}">
                                @csrf
                                @method('DELETE')
                                <button class="w-full rounded-2xl border border-red-200 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-50">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="store-panel h-fit p-6 lg:sticky lg:top-44">
                <h2 class="text-2xl font-black">{{ __('Order Summary') }}</h2>
                <div class="mt-6 grid gap-4 text-sm font-bold">
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Items') }}</span><span>{{ $items->sum('quantity') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>USD {{ number_format($subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Shipping') }}</span><span>{{ __('Calculated at checkout') }}</span></div>
                </div>
                <a href="{{ route('checkout.index') }}" class="store-button-primary mt-6 w-full">{{ __('Checkout') }}</a>
                <form method="POST" action="{{ route('cart.clear') }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button class="store-button-secondary w-full">{{ __('Clear Cart') }}</button>
                </form>
            </aside>
        </div>
    @endif
</section>
@endsection
