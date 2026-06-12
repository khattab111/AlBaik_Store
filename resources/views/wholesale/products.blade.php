@extends('layouts.app')

@section('title', __('Wholesale products'))
@section('meta_description', __('Wholesale product catalog for approved wholesale customers.'))

@section('content')
@php
    $displayMode = $displayMode ?? 'grid';
    $queryFilters = collect($filters ?? [])->except('page')->filter(fn ($value) => filled($value))->all();
    $sortFilters = collect($queryFilters)->except('sort')->all();
    $perPage = (int) ($filters['per_page'] ?? 12);
    $filterTarget = "[data-ajax-filter-target='wholesale-products-directory']";
@endphp

<section class="premium-products-page">
    <div class="premium-products-shell">
        <nav class="store-breadcrumb premium-products-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ __('Wholesale products') }}</span>
        </nav>

        <section class="premium-products-hero">
            <div class="premium-products-hero-content">
                <p class="premium-products-eyebrow">{{ __('Wholesale Partnership') }}</p>
                <h1>{{ __('Wholesale products') }}</h1>
                <p>{{ __('Browse products prepared for approved wholesale customers with server-calculated wholesale tiers and minimum quantities.') }}</p>
                <div class="premium-products-hero-actions">
                    <a href="#wholesale-products-directory" class="premium-products-primary">{{ __('Browse wholesale products') }}</a>
                    <a href="{{ route('wholesale.offers.index') }}" class="premium-products-secondary">{{ __('Wholesale offers') }}</a>
                </div>
                <div class="premium-products-hero-trust">
                    <span>{{ __('Wholesale quantity tiers') }}</span>
                    <span>{{ __('Original products') }}</span>
                    <span>{{ __('Business support') }}</span>
                </div>
            </div>
        </section>

        <div class="premium-products-layout">
            <aside class="premium-filter-column">
                <form method="GET" class="premium-filter-panel" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
                    <input type="hidden" name="view" value="{{ $displayMode }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                    <div class="premium-filter-heading">
                        <div>
                            <p>{{ __('Wholesale') }}</p>
                            <h2>{{ __('Products') }}</h2>
                        </div>
                        <span aria-hidden="true">☰</span>
                    </div>

                    <div class="premium-filter-search">
                        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search products') }}">
                    </div>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Categories') }}</summary>
                        <div class="premium-filter-options">
                            <label><input type="radio" name="category" value="" @checked(empty($filters['category']))><span>{{ __('All') }}</span><small>{{ $categories->sum('products_count') }}</small></label>
                            @foreach($categories->take(10) as $category)
                                <label><input type="radio" name="category" value="{{ $category->slug }}" @checked(($filters['category'] ?? '') === $category->slug)><span>{{ $category->name }}</span><small>{{ $category->products_count }}</small></label>
                            @endforeach
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Brands') }}</summary>
                        <div class="premium-filter-options">
                            <label><input type="radio" name="brand" value="" @checked(empty($filters['brand']))><span>{{ __('All') }}</span><small>{{ $brands->sum('products_count') }}</small></label>
                            @foreach($brands->take(10) as $brand)
                                <label><input type="radio" name="brand" value="{{ $brand->slug }}" @checked(($filters['brand'] ?? '') === $brand->slug)><span>{{ $brand->name }}</span><small>{{ $brand->products_count }}</small></label>
                            @endforeach
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Price range') }}</summary>
                        <div class="premium-price-inputs">
                            <input name="min_price" inputmode="decimal" value="{{ $filters['min_price'] ?? '' }}" placeholder="0">
                            <input name="max_price" inputmode="decimal" value="{{ $filters['max_price'] ?? '' }}" placeholder="10000+">
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Availability') }}</summary>
                        <div class="premium-filter-options">
                            <label><input type="checkbox" name="in_stock" value="1" @checked((bool) ($filters['in_stock'] ?? false))><span>{{ __('In Stock only') }}</span></label>
                            <label><input type="checkbox" name="on_sale" value="1" @checked((bool) ($filters['on_sale'] ?? false))><span>{{ __('Wholesale offers') }}</span></label>
                        </div>
                    </details>

                    <button class="premium-filter-apply">{{ __('Apply Filters') }}</button>
                    <a href="{{ route('wholesale.products.index') }}" class="premium-filter-reset" data-filter-link data-ajax-target="{{ $filterTarget }}">{{ __('Reset Filters') }}</a>
                </form>
            </aside>

            <div class="premium-products-main" id="wholesale-products-directory" data-ajax-filter-target="wholesale-products-directory">
                <div class="premium-products-toolbar">
                    <div class="premium-result-count"><strong>{{ $products->total() }}</strong><span>{{ __('Products') }}</span></div>

                    <form method="GET" class="premium-toolbar-form" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
                        @foreach ($queryFilters as $key => $value)
                            @if (is_scalar($value) && $key !== 'per_page')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <span>{{ __('Show') }}</span>
                        <select name="per_page">
                            @foreach([12, 24, 48] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </form>

                    <form method="GET" class="premium-toolbar-form" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
                        @foreach ($sortFilters as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <span>{{ __('Sort') }}</span>
                        <select name="sort">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
                            <option value="best_selling" @selected(($filters['sort'] ?? '') === 'best_selling')>{{ __('Best Selling') }}</option>
                            <option value="top_rated" @selected(($filters['sort'] ?? '') === 'top_rated')>{{ __('Top Rated') }}</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Lowest Price') }}</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Highest Price') }}</option>
                        </select>
                    </form>
                </div>

                <div class="{{ $displayMode === 'list' ? 'premium-product-list' : 'premium-product-grid' }}" data-product-list data-product-view="{{ $displayMode }}">
                    @forelse($products as $product)
                        @include('partials.wholesale-product-card', ['product' => $product, 'displayMode' => $displayMode])
                    @empty
                        <div class="premium-empty-products">
                            <h2>{{ __('No products found.') }}</h2>
                            <p>{{ __('Try changing filters or search terms.') }}</p>
                        </div>
                    @endforelse
                </div>

                <div class="premium-load-more-wrap">
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="premium-load-more" data-load-more data-ajax-target="{{ $filterTarget }}">{{ __('Load More') }} <span aria-hidden="true">⌄</span></a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
