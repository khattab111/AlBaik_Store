@extends('layouts.app')

@section('title', __('Offers'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Offers') }}</span>
    </nav>

    <div class="mb-8 overflow-hidden rounded-[2rem] bg-slate-950 p-8 text-white shadow-xl shadow-slate-950/10">
        <p class="text-sm font-black text-amber-300">{{ __('Limited time') }}</p>
        <h1 class="mt-2 text-4xl font-black">{{ __('Offers') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-300">{{ __('Browse active discounts, wholesale picks, and launch deals from the storefront catalog.') }}</p>
    </div>

    @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])

    @if(($presentedFlashOffers ?? collect())->isNotEmpty())
        <div class="mb-8 grid gap-4 lg:grid-cols-3">
            @foreach($presentedFlashOffers as $offer)
                @php
                    $mainProduct = $offer['items']->pluck('product')->filter()->first();
                    $image = $mainProduct?->images?->first()?->path;
                    $imageUrl = $image && file_exists(public_path('storage/'.$image))
                        ? asset('storage/'.$image)
                        : asset('images/storefront/product-fallback.svg');
                @endphp
                <article class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="grid gap-0 sm:grid-cols-[150px_1fr]">
                        <a href="{{ route('offers.show', $offer['slug']) }}" class="bg-slate-50">
                            <img src="{{ $imageUrl }}" alt="{{ $offer['title'] }}" class="h-full min-h-40 w-full object-contain p-5" loading="lazy" decoding="async">
                        </a>
                        <div class="p-5">
                            <p class="text-xs font-black uppercase text-amber-600">{{ $offer['badge'] }}</p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">{{ $offer['title'] }}</h2>
                            <p class="mt-2 text-sm font-black leading-6 text-amber-700">{{ $offer['summary'] }}</p>
                            @if($offer['description'])
                                <p class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">{{ $offer['description'] }}</p>
                            @endif
                            <div class="mt-3 grid gap-1 text-xs font-bold text-slate-600">
                                @foreach(array_slice($offer['details'], 0, 4) as $detail)
                                    <p>{{ $detail }}</p>
                                @endforeach
                            </div>
                            @if($offer['items']->count() > 1)
                                <div class="mt-3 grid gap-1 rounded-2xl bg-slate-50 p-3 text-xs font-bold text-slate-600">
                                    @foreach($offer['items']->take(3) as $item)
                                        <p>{{ $item['name'] }} × {{ $item['quantity'] }} @if($item['is_free_item']) - {{ __('Free') }} @endif</p>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-2 text-xs font-black">
                                @if($offer['ends_at'])
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ __('Ends at') }} {{ $offer['ends_at']->format('Y-m-d') }}</span>
                                @endif
                                @if($offer['remaining_quantity'] !== null)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">{{ __('Remaining') }} {{ $offer['remaining_quantity'] }}</span>
                                @endif
                                @if($offer['free_shipping_scope'])
                                    <span class="rounded-full bg-sky-100 px-3 py-1 text-sky-800">{{ __('Free shipping') }}: {{ $offer['free_shipping_scope'] }}</span>
                                @endif
                            </div>
                            <div class="mt-5 grid gap-2 sm:grid-cols-2">
                                <a href="{{ route('offers.show', $offer['slug']) }}" class="store-button-secondary w-full">{{ __('Details') }}</a>
                                <form method="POST" action="{{ route('offers.cart.add', $offer['slug']) }}" data-ajax-store-action>
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="store-button-primary w-full">{{ __('Add to Cart') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <form method="GET" class="store-panel mb-8 grid gap-3 p-5 md:grid-cols-4">
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

    <div class="store-trust-strip">
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🚚</span><div><p class="font-black">{{ __('Fast shipping') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Fast delivery for all orders.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🔒</span><div><p class="font-black">{{ __('Secure checkout') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Safe and flexible payment methods.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">✓</span><div><p class="font-black">{{ __('Original guarantee') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Verified catalog products.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">☎</span><div><p class="font-black">{{ __('Customer support') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Support paths remain visible after purchase.') }}</p></div></div>
    </div>
</section>
@endsection
