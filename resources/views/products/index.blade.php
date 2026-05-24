@extends('layouts.app')

@section('title', $pageTitle ?? __('Products'))
@section('meta_description', __('Browse original products with filters by category, brand, price, stock, offers, and rating.'))
@section('canonical', route('products.index'))

@section('content')
@php
    $displayMode = $displayMode ?? 'grid';
    $queryFilters = collect($filters ?? [])->except('page')->filter(fn ($value) => filled($value))->all();
    $sortFilters = collect($queryFilters)->except('sort')->all();
    $gridUrl = route('products.index', array_merge($queryFilters, ['view' => 'grid']));
    $listUrl = route('products.index', array_merge($queryFilters, ['view' => 'list']));
@endphp

<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $pageTitle ?? __('Products') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <div class="grid items-center gap-6 lg:grid-cols-[1fr_auto]">
            <div>
                <p class="store-eyebrow">{{ __('Catalog') }}</p>
                <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ $pageTitle ?? __('Products') }}</h1>
                <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Discover original products from trusted brands with competitive prices.') }}</p>
            </div>
            <a href="{{ route('offers.index') }}" class="store-button-primary">
                <span aria-hidden="true">🔥</span>
                {{ __('View Offers') }}
            </a>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-[340px_minmax(0,1fr)]">
        <aside>
            <form method="GET" class="store-filter-panel" aria-label="{{ __('Filter products') }}">
                <input type="hidden" name="view" value="{{ $displayMode }}">
                @if (($filters['sort'] ?? null) && ($filters['sort'] ?? 'latest') !== 'latest')
                    <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                @endif

                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-black">{{ __('Refine Results') }}</h2>
                    <span class="store-icon-pill" aria-hidden="true">≡</span>
                </div>

                <div>
                    <label for="product-filter-search" class="sr-only">{{ __('Search products') }}</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 ltr:left-4 rtl:right-4" aria-hidden="true">⌕</span>
                        <input id="product-filter-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search products') }}" class="store-field ltr:pl-10 rtl:pr-10">
                    </div>
                </div>

                <div>
                    <label for="product-filter-category" class="sr-only">{{ __('All Categories') }}</label>
                    <select id="product-filter-category" name="category" class="store-field">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="product-filter-brand" class="sr-only">{{ __('All Brands') }}</label>
                    <select id="product-filter-brand" name="brand" class="store-field">
                        <option value="">{{ __('All Brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="product-filter-min-price" class="sr-only">{{ __('From price') }}</label>
                        <input id="product-filter-min-price" name="min_price" inputmode="decimal" value="{{ $filters['min_price'] ?? '' }}" placeholder="{{ __('From price') }}" class="store-field">
                    </div>
                    <div>
                        <label for="product-filter-max-price" class="sr-only">{{ __('To price') }}</label>
                        <input id="product-filter-max-price" name="max_price" inputmode="decimal" value="{{ $filters['max_price'] ?? '' }}" placeholder="{{ __('To price') }}" class="store-field">
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold">
                    <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))>
                    {{ __('In Stock') }}
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold">
                    <input type="checkbox" name="on_sale" value="1" @checked(request()->boolean('on_sale'))>
                    {{ __('Offers') }}
                </label>

                <button class="store-button-primary w-full">
                    <span aria-hidden="true">≡</span>
                    {{ __('Apply Filters') }}
                </button>
                <a href="{{ route('products.index') }}" class="store-button-secondary w-full">
                    <span aria-hidden="true">↻</span>
                    {{ __('Reset') }}
                </a>
            </form>
        </aside>

        <div>
            <div class="store-product-toolbar">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-sm font-black text-slate-700">{{ $products->total() }} {{ __('Products') }}</p>
                    <span class="hidden h-5 w-px bg-slate-200 sm:block" aria-hidden="true"></span>
                    <p class="text-sm font-bold text-slate-500">{{ __('Showing curated store results') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" class="flex items-center gap-2" aria-label="{{ __('Sort results') }}">
                        @foreach ($sortFilters as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="product-sort" class="sr-only">{{ __('Sort results') }}</label>
                        <select id="product-sort" name="sort" class="store-field min-w-36 py-2">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Highest Price') }}</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Lowest Price') }}</option>
                            <option value="best_selling" @selected(($filters['sort'] ?? '') === 'best_selling')>{{ __('Best Selling') }}</option>
                            <option value="top_rated" @selected(($filters['sort'] ?? '') === 'top_rated')>{{ __('Top Rated') }}</option>
                        </select>
                        <button class="store-button-secondary px-4 py-2">{{ __('Sort') }}</button>
                    </form>

                    <nav class="flex rounded-2xl border border-slate-200 bg-slate-50 p-1" aria-label="{{ __('Product view options') }}">
                        <a href="{{ $gridUrl }}" class="rounded-xl px-3 py-2 text-sm font-black transition {{ $displayMode === 'grid' ? 'bg-red-700 text-white shadow-sm' : 'text-slate-600 hover:text-red-700' }}" aria-label="{{ __('Grid view') }}" @if($displayMode === 'grid') aria-current="page" @endif>▦</a>
                        <a href="{{ $listUrl }}" class="rounded-xl px-3 py-2 text-sm font-black transition {{ $displayMode === 'list' ? 'bg-red-700 text-white shadow-sm' : 'text-slate-600 hover:text-red-700' }}" aria-label="{{ __('List view') }}" @if($displayMode === 'list') aria-current="page" @endif>☰</a>
                    </nav>
                </div>
            </div>

            <div data-product-view="{{ $displayMode }}" class="{{ $displayMode === 'list' ? 'grid gap-4' : 'store-product-grid' }}">
                @forelse($products as $product)
                    @include('partials.product-card', ['product' => $product, 'displayMode' => $displayMode])
                @empty
                    <div class="store-panel col-span-full p-10 text-center">
                        <h2 class="text-xl font-black">{{ __('No products found.') }}</h2>
                        <p class="mt-2 text-slate-500">{{ __('Try changing filters or search terms.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $products->links() }}</div>

            <div class="store-trust-strip">
                <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🚚</span><div><p class="font-black">{{ __('Fast shipping') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Fast delivery for all orders.') }}</p></div></div>
                <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🔒</span><div><p class="font-black">{{ __('Secure checkout') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Safe and flexible payment methods.') }}</p></div></div>
                <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">✓</span><div><p class="font-black">{{ __('Original guarantee') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Verified catalog products.') }}</p></div></div>
                <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">☎</span><div><p class="font-black">{{ __('Customer support') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Support paths remain visible after purchase.') }}</p></div></div>
            </div>
        </div>
    </div>
</section>
@endsection
