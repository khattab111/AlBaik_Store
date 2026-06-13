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
            <a href="{{ auth()->user()?->isWholesaleCustomer() ? route('wholesale.products.index') : route('products.index') }}" class="store-button-primary mt-6">{{ auth()->user()?->isWholesaleCustomer() ? __('Wholesale products') : __('Browse Products') }}</a>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="grid gap-4">
                @foreach($items as $item)
                    @php
                        $isOffer = ($item->item_type ?? 'product') === 'offer';
                        $firstComponent = collect($item->components_snapshot ?? [])->first();
                        $image = $isOffer ? ($firstComponent['product_image'] ?? null) : $item->product->images->first()?->path;
                        $imageUrl = $image && file_exists(public_path('storage/'.$image))
                            ? asset('storage/'.$image)
                            : asset('images/storefront/product-fallback.svg');
                        $title = $isOffer ? $item->title : $item->product->name;
                    @endphp
                    <article class="store-panel grid gap-4 p-4 sm:grid-cols-[120px_minmax(0,1fr)] xl:grid-cols-[120px_minmax(0,1fr)_auto]">
                        <img src="{{ $imageUrl }}" class="mx-auto h-28 w-28 rounded-2xl object-cover sm:mx-0" alt="{{ $title }}">
                        <div class="min-w-0">
                            <h2 class="store-safe-text text-lg font-black">{{ $title }}</h2>
                            @if($isOffer)
                                <p class="mt-1 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">{{ __('Offer') }}</p>
                                <div class="mt-3 grid gap-1 text-xs font-bold text-slate-500">
                                    @foreach(collect($item->components_snapshot ?? [])->take(5) as $component)
                                        <p class="store-safe-text">{{ $component['product_name'] ?? __('Product') }} × {{ $component['quantity'] ?? 1 }}</p>
                                    @endforeach
                                </div>
                            @else
                                <p class="store-safe-text mt-1 text-sm font-bold text-slate-500">{{ $item->product->brand?->name }}</p>
                            @endif
                            @if(!$isOffer && $item->variant)
                                <p class="mt-2 text-xs font-bold text-slate-500">{{ $item->variant->sku }}</p>
                            @endif
                            @if(!$isOffer && ($item->applied_flash_offer_id ?? null))
                                <p class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">{{ __('Flash Offer') }}</p>
                            @endif
                            <p class="mt-3 store-price">{{ store_money((float) $item->unit_price) }}</p>
                        </div>
                        <div class="grid content-between gap-3 sm:col-span-2 xl:col-span-1 xl:min-w-48">
                            @auth
                                <form method="POST" action="{{ $isOffer ? route('cart.items.update', $item) : route('cart.update', $item->product) }}" class="grid gap-2 sm:grid-cols-[110px_1fr] xl:grid-cols-[96px_auto]">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="store-field">
                                    <button class="store-button-secondary w-full">{{ __('Update') }}</button>
                                </form>
                                <form method="POST" action="{{ $isOffer ? route('cart.items.destroy', $item) : route('cart.remove', $item->product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-full rounded-2xl border border-red-200 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-50">{{ __('Delete') }}</button>
                                </form>
                            @else
                                @if(!$isOffer)
                                    <form method="POST" action="{{ route('cart.update', $item->product) }}" class="grid gap-2 sm:grid-cols-[110px_1fr] xl:grid-cols-[96px_auto]">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="store-field">
                                        <button class="store-button-secondary w-full">{{ __('Update') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.remove', $item->product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="w-full rounded-2xl border border-red-200 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-50">{{ __('Delete') }}</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="store-panel h-fit p-6 lg:sticky lg:top-44">
                <h2 class="text-2xl font-black">{{ __('Order Summary') }}</h2>
                <div class="mt-6 grid gap-4 text-sm font-bold">
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Items') }}</span><span>{{ $items->sum('quantity') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ store_money($subtotal) }}</span></div>
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
