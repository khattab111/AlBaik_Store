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
    $perPage = (int) ($filters['per_page'] ?? 12);
    $filterTarget = "[data-ajax-filter-target='products-directory']";
    $heroProduct = $products->first();
    $heroImage = $heroProduct?->images?->first()?->path;
    $heroImageUrl = $heroImage && file_exists(public_path('storage/'.$heroImage))
        ? asset('storage/'.$heroImage)
        : asset('images/storefront/product-fallback.svg');
@endphp

<section class="premium-products-page">
    <div class="premium-products-shell">
        <nav class="store-breadcrumb premium-products-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ $pageTitle ?? __('Products') }}</span>
        </nav>

        <section class="premium-products-hero">
            <div class="premium-products-hero-content">
                <p class="premium-products-eyebrow">{{ __('Modern Electronics Store') }}</p>
                <h1>{{ __('Discover the latest original phones and electronics') }}</h1>
                <p>{{ __('Shop trusted global brands with competitive prices, real warranty, and checkout-time shipping calculation.') }}</p>
                <div class="premium-products-hero-actions">
                    <a href="#products-directory" class="premium-products-primary">{{ __('Shop now') }}</a>
                    <a href="{{ route('offers.index') }}" class="premium-products-secondary">{{ __('Explore offers') }}</a>
                </div>
                <div class="premium-products-hero-trust">
                    <span>{{ __('Fast shipping') }}</span>
                    <span>{{ __('100% original products') }}</span>
                    <span>{{ __('24/7 support') }}</span>
                </div>
            </div>
            <div class="premium-products-hero-media">
                <img src="{{ $heroImageUrl }}" alt="{{ $heroProduct?->name ?? __('Original electronics') }}" loading="eager" decoding="async">
            </div>
        </section>

        <nav class="premium-category-rail" aria-label="{{ __('Quick categories') }}">
            <a href="{{ route('products.index', collect($queryFilters)->except('category')->all()) }}" class="{{ empty($filters['category']) ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">
                <span aria-hidden="true">▦</span>
                {{ __('All') }}
            </a>
            @foreach($categories->take(12) as $category)
                @php($categoryUrl = route('products.index', array_merge(collect($queryFilters)->except('category')->all(), ['category' => $category->slug])))
                <a href="{{ $categoryUrl }}" class="{{ ($filters['category'] ?? '') === $category->slug ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">
                    <span aria-hidden="true">{{ ['📱','💻','🎧','⌚','🎮','🔌','📷','🖥'][$loop->index % 8] }}</span>
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <div class="premium-products-layout">
            <aside class="premium-filter-column">
                <form id="product-filter-form" method="GET" class="premium-filter-panel" aria-label="{{ __('Filter products') }}" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
                    <input type="hidden" name="view" value="{{ $displayMode }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @if (($filters['sort'] ?? null) && ($filters['sort'] ?? 'latest') !== 'latest')
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                    @endif

                    <div class="premium-filter-heading">
                        <div>
                            <p>{{ __('Filter results') }}</p>
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
                            @foreach($categories->take(8) as $category)
                                <label><input type="radio" name="category" value="{{ $category->slug }}" @checked(($filters['category'] ?? '') === $category->slug)><span>{{ $category->name }}</span><small>{{ $category->products_count }}</small></label>
                            @endforeach
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Brands') }}</summary>
                        <div class="premium-brand-search">
                            <input type="search" placeholder="{{ __('Search brands') }}" data-brand-filter-search>
                        </div>
                        <div class="premium-filter-options" data-brand-filter-list>
                            <label><input type="radio" name="brand" value="" @checked(empty($filters['brand']))><span>{{ __('All') }}</span><small>{{ $brands->sum('products_count') }}</small></label>
                            @foreach($brands->take(10) as $brand)
                                <label data-brand-filter-item><input type="radio" name="brand" value="{{ $brand->slug }}" @checked(($filters['brand'] ?? '') === $brand->slug)><span>{{ $brand->name }}</span><small>{{ $brand->products_count }}</small></label>
                            @endforeach
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Price range') }}</summary>
                        <div class="premium-price-range">
                            <div><span>{{ __('From price') }}</span><strong>{{ store_money((float) ($filters['min_price'] ?? 0)) }}</strong></div>
                            <div><span>{{ __('To price') }}</span><strong>{{ filled($filters['max_price'] ?? null) ? store_money((float) $filters['max_price']) : '10000+' }}</strong></div>
                            <div class="premium-range-track"><span></span></div>
                            <div class="premium-price-inputs">
                                <input name="min_price" inputmode="decimal" value="{{ $filters['min_price'] ?? '' }}" placeholder="0">
                                <input name="max_price" inputmode="decimal" value="{{ $filters['max_price'] ?? '' }}" placeholder="10000+">
                            </div>
                        </div>
                    </details>

                    <details class="premium-filter-card">
                        <summary>{{ __('Rating') }}</summary>
                        <div class="premium-filter-options">
                            <label><input type="checkbox" disabled><span>★★★★★</span></label>
                            <label><input type="checkbox" disabled><span>★★★★+</span></label>
                            <label><input type="checkbox" disabled><span>★★★+</span></label>
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Availability') }}</summary>
                        <div class="premium-filter-options">
                            <label><input type="checkbox" name="in_stock" value="1" @checked((bool) ($filters['in_stock'] ?? false))><span>{{ __('In Stock only') }}</span></label>
                            <label><input type="checkbox" disabled><span>{{ __('Include out of stock') }}</span></label>
                        </div>
                    </details>

                    <details class="premium-filter-card" open>
                        <summary>{{ __('Offers') }}</summary>
                        <div class="premium-filter-options">
                            <label><input type="checkbox" name="on_sale" value="1" @checked((bool) ($filters['on_sale'] ?? false))><span>{{ __('On sale') }}</span></label>
                            <label><input type="checkbox" disabled><span>{{ __('New products') }}</span></label>
                            <label><input type="checkbox" disabled><span>{{ __('Best Selling') }}</span></label>
                        </div>
                    </details>

                    <button class="premium-filter-apply">{{ __('Apply Filters') }}</button>
                    <a href="{{ route('products.index') }}" class="premium-filter-reset" data-filter-link data-ajax-target="{{ $filterTarget }}">{{ __('Reset Filters') }}</a>
                </form>
            </aside>

            <div class="premium-products-main" id="products-directory" data-ajax-filter-target="products-directory">
                <div class="premium-products-toolbar">
                    <div class="premium-result-count">
                        <strong>{{ $products->total() }}</strong>
                        <span>{{ __('Products') }}</span>
                    </div>

                    <form method="GET" class="premium-toolbar-form" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
                        @foreach ($queryFilters as $key => $value)
                            @if (is_scalar($value) && $key !== 'per_page')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <span>{{ __('Show') }}</span>
                        <select name="per_page" aria-label="{{ __('Show') }}">
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
                        <select name="sort" aria-label="{{ __('Sort results') }}">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
                            <option value="best_selling" @selected(($filters['sort'] ?? '') === 'best_selling')>{{ __('Best Selling') }}</option>
                            <option value="top_rated" @selected(($filters['sort'] ?? '') === 'top_rated')>{{ __('Top Rated') }}</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Lowest Price') }}</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Highest Price') }}</option>
                        </select>
                    </form>

                    <nav class="premium-view-switch" aria-label="{{ __('Product view options') }}">
                        <a href="{{ $gridUrl }}" class="{{ $displayMode === 'grid' ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}" aria-label="{{ __('Grid view') }}">▦</a>
                        <a href="{{ $listUrl }}" class="{{ $displayMode === 'list' ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}" aria-label="{{ __('List view') }}">☰</a>
                    </nav>
                </div>

                <div class="{{ $displayMode === 'list' ? 'premium-product-list' : 'premium-product-grid' }}" data-product-list data-product-view="{{ $displayMode }}">
                    @forelse($products as $product)
                        @include('partials.product-card', ['product' => $product, 'displayMode' => $displayMode])
                    @empty
                        <div class="premium-empty-products">
                            <h2>{{ __('No products found.') }}</h2>
                            <p>{{ __('Try changing filters or search terms.') }}</p>
                        </div>
                    @endforelse
                </div>

                <div class="premium-load-more-wrap">
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="premium-load-more" data-load-more data-ajax-target="{{ $filterTarget }}">
                            {{ __('Load More') }}
                            <span aria-hidden="true">⌄</span>
                        </a>
                    @endif
                </div>

                <section class="premium-products-trust">
                    <div><span>🚚</span><strong>{{ __('Fast shipping') }}</strong><p>{{ __('Within 24 - 48 hours') }}</p></div>
                    <div><span>✓</span><strong>{{ __('100% original products') }}</strong><p>{{ __('Original and guaranteed catalog') }}</p></div>
                    <div><span>🎧</span><strong>{{ __('24/7 support') }}</strong><p>{{ __('Support paths remain visible after purchase.') }}</p></div>
                    <div><span>↻</span><strong>{{ __('Warranty and easy returns') }}</strong><p>{{ __('Available according to store policy') }}</p></div>
                </section>
            </div>
        </div>
    </div>

    <aside class="premium-floating-actions" aria-label="{{ __('Quick actions') }}">
        <a href="{{ route('favorites.index') }}" aria-label="{{ __('Wishlist') }}">♡</a>
        <button type="button" aria-label="{{ __('Compare') }}">⇄</button>
        <a href="#" aria-label="{{ __('Back to top') }}">↑</a>
    </aside>
</section>
@endsection
