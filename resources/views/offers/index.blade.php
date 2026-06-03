@extends('layouts.app')

@section('title', __('Offers'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Offers') }}</span>
    </nav>

    <div class="mb-8 overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10 sm:p-8">
        <p class="text-sm font-black text-amber-300">{{ __('Limited time') }}</p>
        <h1 class="store-safe-text mt-2 text-4xl font-black">{{ __('Offers') }}</h1>
        <p class="store-safe-text mt-3 max-w-2xl text-slate-300">{{ __('Browse active discounts, wholesale picks, and launch deals from the storefront catalog.') }}</p>
    </div>

    @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])

    <div data-ajax-filter-target="offers-directory">
    @if(($presentedFlashOffers ?? collect())->isNotEmpty())
        <div class="flash-offer-grid mb-8">
            @foreach($presentedFlashOffers as $offer)
                @php
                    $mainProduct = $offer['items']->pluck('product')->filter()->first();
                    $image = $mainProduct?->images?->first()?->path;
                    $imageUrl = $image && file_exists(public_path('storage/'.$image))
                        ? asset('storage/'.$image)
                        : asset('images/storefront/product-fallback.svg');
                @endphp
                <article class="flash-offer-card">
                    <a href="{{ route('offers.show', $offer['slug']) }}" class="flash-offer-image">
                        <img src="{{ $imageUrl }}" alt="{{ $offer['title'] }}" loading="lazy" decoding="async">
                    </a>
                    <div class="flash-offer-body">
                        <div>
                            <p class="flash-offer-badge">{{ $offer['badge'] }}</p>
                            <h2>{{ $offer['title'] }}</h2>
                            <p class="flash-offer-summary">{{ $offer['summary'] }}</p>
                        </div>
                        @if($offer['description'])
                            <p class="flash-offer-description">{{ $offer['description'] }}</p>
                        @endif
                        <div class="flash-offer-details">
                            @foreach(array_slice($offer['details'], 0, 4) as $detail)
                                <p>{{ $detail }}</p>
                            @endforeach
                        </div>
                        @if($offer['items']->count() > 1)
                            <div class="flash-offer-items">
                                @foreach($offer['items']->take(3) as $item)
                                    <p>{{ $item['name'] }} × {{ $item['quantity'] }} @if($item['is_free_item']) - {{ __('Free') }} @endif</p>
                                @endforeach
                            </div>
                        @endif
                        <div class="flash-offer-tags">
                            @if($offer['ends_at'])
                                <span class="is-amber">{{ __('Ends at') }} {{ $offer['ends_at']->format('Y-m-d') }}</span>
                            @endif
                            @if($offer['remaining_quantity'] !== null)
                                <span class="is-emerald">{{ __('Remaining') }} {{ $offer['remaining_quantity'] }}</span>
                            @endif
                            @if($offer['free_shipping_scope'])
                                <span class="is-sky">{{ __('Free shipping') }}: {{ $offer['free_shipping_scope'] }}</span>
                            @endif
                        </div>
                        <div class="flash-offer-actions">
                            <a href="{{ route('offers.show', $offer['slug']) }}" class="store-button-secondary w-full">{{ __('Details') }}</a>
                            <form method="POST" action="{{ route('offers.cart.add', $offer['slug']) }}" data-ajax-store-action>
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button class="store-button-primary w-full">{{ __('Add to Cart') }}</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <form method="GET" class="store-panel mb-8 grid gap-3 p-5 md:grid-cols-4" data-ajax-filter data-ajax-target="[data-ajax-filter-target='offers-directory']">
        <select name="category" class="store-field">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="brand" class="store-field">
            <option value="">{{ __('All Brands') }}</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <select name="sort" class="store-field">
            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Lowest Price') }}</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Highest Price') }}</option>
        </select>
        <button class="store-button-primary gap-2"><span aria-hidden="true">≡</span>{{ __('Filter') }}</button>
    </form>

    <div class="store-product-grid">
        @forelse($products as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <div class="store-panel col-span-full p-10 text-center">{{ __('No active offers.') }}</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
    </div>

    <div class="store-trust-strip">
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🚚</span><div><p class="font-black">{{ __('Fast shipping') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Fast delivery for all orders.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🔒</span><div><p class="font-black">{{ __('Secure checkout') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Safe and flexible payment methods.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">✓</span><div><p class="font-black">{{ __('Original guarantee') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Verified catalog products.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">☎</span><div><p class="font-black">{{ __('Customer support') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Support paths remain visible after purchase.') }}</p></div></div>
    </div>
</section>
@endsection
