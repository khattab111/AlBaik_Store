@extends('layouts.app')

@section('title', __('Offers'))
@section('meta_description', __('Exclusive limited-time deals on phones, electronics, accessories, bundles, and free shipping offers.'))
@section('canonical', route('offers.index'))

@section('content')
@php
    $filterTarget = "[data-ajax-filter-target='offers-directory']";
    $queryFilters = collect($filters ?? [])->except('page')->filter(fn ($value) => filled($value))->all();
    $perPage = (int) ($filters['per_page'] ?? 12);
    $displayMode = $filters['view'] ?? 'grid';
    $typeFilters = [
        '' => __('All'),
        \App\Models\FlashOffer::TYPE_PERCENTAGE_DISCOUNT => __('Percentage discounts'),
        \App\Models\FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT => __('Fixed discounts'),
        \App\Models\FlashOffer::TYPE_BUY_X_GET_Y => __('Buy and get'),
        \App\Models\FlashOffer::TYPE_FIXED_PRICE_QUANTITY => __('First quantity price'),
        \App\Models\FlashOffer::TYPE_BUNDLE_FIXED_PRICE => __('Bundle deals'),
        \App\Models\FlashOffer::TYPE_FREE_SHIPPING_PRODUCT => __('Free shipping'),
        \App\Models\FlashOffer::TYPE_CART_FREE_SHIPPING => __('Wholesale offers'),
    ];
    $allOffers = $allPresentedFlashOffers ?? collect();
    $flashOfferStrip = $presentedFlashOffers ?? collect();
    $flashOfferCards = $flashOfferStrip->take(8)->map(function (array $flashOffer): array {
        $mainProduct = collect($flashOffer['items'] ?? [])->pluck('product')->filter()->first();
        $image = $mainProduct?->images?->first()?->path;

        return [
            'slug' => $flashOffer['slug'] ?? '',
            'title' => $flashOffer['title'] ?? __('Flash Offer'),
            'summary' => $flashOffer['summary'] ?? __('Limited time offer'),
            'price' => (float) ($flashOffer['offer_price'] ?? 0),
            'image_url' => $image && file_exists(public_path('storage/'.$image))
                ? asset('storage/'.$image)
                : asset('images/storefront/product-fallback.svg'),
        ];
    });
    $heroDeadline = $allOffers->pluck('ends_at')->filter()->sort()->first();
    $heroDeadlineIso = $heroDeadline?->toIso8601String();
    $endingTodayCount = $allOffers->filter(fn ($offer) => $offer['ends_at'] && $offer['ends_at']->isToday())->count();
    $maxDiscount = (float) $allOffers->max('discount_percentage');
    $includedProducts = $allOffers->sum(fn ($offer) => $offer['items']->count());
    $topDeals = $allOffers->sortByDesc('discount_percentage')->take(10);
    $endingSoonDeals = $allOffers->filter(fn ($offer) => $offer['ends_at'] && $offer['ends_at']->between(now(), now()->copy()->addDay()))->take(8);
    $dealCards = ($presentedProductOffers ?? collect())->map(function (array $deal): array {
        $product = $deal['product'] ?? null;
        $firstOfferItem = $deal['items']->first();
        $image = $product?->images?->first()?->path ?: ($firstOfferItem['image'] ?? null);
        $sold = max(0, (int) (($deal['offer']->sold_quantity ?? null) ?: 0));
        $remaining = $deal['remaining_quantity'] ?? null;
        $maxQuantity = $remaining !== null ? max($sold + $remaining, 1) : null;
        $progress = $maxQuantity ? min(100, round(($sold / $maxQuantity) * 100)) : null;

        return [
            'slug' => $deal['slug'] ?? '',
            'title' => $deal['title'] ?? __('Flash Offer'),
            'product_name' => $product?->name ?: ($deal['title'] ?? __('Flash Offer')),
            'brand' => $product?->brand?->name ?: __('Exclusive offer'),
            'summary' => $deal['summary'] ?? __('Limited time offer'),
            'badge' => $deal['badge'] ?? __('Flash Offer'),
            'discount_percentage' => (float) ($deal['discount_percentage'] ?? 0),
            'offer_price' => (float) ($deal['offer_price'] ?? 0),
            'original_price' => (float) ($deal['original_price'] ?? 0),
            'saving' => (float) ($deal['saving'] ?? 0),
            'remaining_quantity' => $remaining,
            'sold' => $sold,
            'max_quantity' => $maxQuantity,
            'progress' => $progress,
            'ends_at' => $deal['ends_at'] ?? null,
            'image_url' => $image && file_exists(public_path('storage/'.$image))
                ? asset('storage/'.$image)
                : asset('images/storefront/product-fallback.svg'),
        ];
    });
@endphp

<section class="deals-page">
    <div class="deals-shell">
        <nav class="store-breadcrumb deals-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ __('Offers') }}</span>
        </nav>

        <section class="deals-hero">
            <div class="deals-hero-content">
                <p class="deals-eyebrow">{{ __('Limited time deals') }}</p>
                <h1>{{ __('Best exclusive offers') }}</h1>
                <p>{{ __('Save more on the latest original phones, electronics, accessories, bundles, and shipping deals.') }}</p>
                <div class="deals-countdown" data-countdown="{{ $heroDeadlineIso }}">
                    <p>{{ __('Deals end in') }}</p>
                    <div>
                        <span><strong data-countdown-days>00</strong><small>{{ __('Days') }}</small></span>
                        <span><strong data-countdown-hours>00</strong><small>{{ __('Hours') }}</small></span>
                        <span><strong data-countdown-minutes>00</strong><small>{{ __('Minutes') }}</small></span>
                        <span><strong data-countdown-seconds>00</strong><small>{{ __('Seconds') }}</small></span>
                    </div>
                </div>
                <div class="deals-hero-actions">
                    <a href="#offers-directory" class="deals-primary">{{ __('Shop offers now') }}</a>
                    <a href="{{ route('offers.index', array_merge($queryFilters, ['sort' => 'best_selling'])) }}" class="deals-secondary" data-filter-link data-ajax-target="{{ $filterTarget }}">{{ __('Best selling deals') }}</a>
                </div>
            </div>
            <div class="deals-hero-visual" aria-hidden="true">
                <div>
                    <span>{{ __('Up to') }}</span>
                    <strong>{{ number_format($maxDiscount ?: 50, 0) }}%</strong>
                    <small>{{ __('on selected products') }}</small>
                </div>
            </div>
        </section>

        <nav class="deals-type-rail" aria-label="{{ __('Offer types') }}">
            @foreach($typeFilters as $type => $label)
                @php($typeUrl = route('offers.index', array_merge(collect($queryFilters)->except('type')->all(), filled($type) ? ['type' => $type] : [])))
                <a href="{{ $typeUrl }}" class="{{ ($filters['type'] ?? '') === $type ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <section class="deals-stats">
            <div><span>{{ __('Current offers') }}</span><strong>{{ $allOffers->count() }}</strong></div>
            <div><span>{{ __('Highest saving') }}</span><strong>{{ number_format($maxDiscount, 0) }}%</strong></div>
            <div><span>{{ __('Ending today') }}</span><strong>{{ $endingTodayCount }}</strong></div>
            <div><span>{{ __('Included products') }}</span><strong>{{ $includedProducts }}</strong></div>
        </section>

        @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])

        <section class="deals-flash-section">
            <div class="deals-section-heading">
                <div>
                    <p>{{ __('Flash Deals') }}</p>
                    <h2>{{ __('Most requested right now') }}</h2>
                </div>
                <a href="{{ route('offers.index') }}">{{ __('All offers') }}</a>
            </div>
            <div class="deals-horizontal">
                @foreach($flashOfferCards as $flashOfferCard)
                    <a href="{{ route('offers.show', $flashOfferCard['slug']) }}" class="deals-mini-card">
                        <img src="{{ $flashOfferCard['image_url'] }}" alt="{{ $flashOfferCard['title'] }}" loading="lazy" decoding="async">
                        <span>{{ $flashOfferCard['summary'] }}</span>
                        <strong>{{ $flashOfferCard['title'] }}</strong>
                        <small>{{ store_money($flashOfferCard['price']) }}</small>
                    </a>
                @endforeach
            </div>
        </section>

        <div data-ajax-filter-target="offers-directory" id="offers-directory">
            <form method="GET" class="deals-smart-filter" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
                <input type="hidden" name="view" value="{{ $displayMode }}">
                <label>
                    <span>{{ __('Offer type') }}</span>
                    <select name="type">
                        @foreach($typeFilters as $type => $label)
                            <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>{{ __('Category') }}</span>
                    <select name="category">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>{{ __('Brand') }}</span>
                    <select name="brand">
                        <option value="">{{ __('All Brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>{{ __('Sort') }}</span>
                    <select name="sort">
                        <option value="highest_discount" @selected(($filters['sort'] ?? '') === 'highest_discount')>{{ __('Highest discount') }}</option>
                        <option value="ending_soon" @selected(($filters['sort'] ?? '') === 'ending_soon')>{{ __('Ending soon') }}</option>
                        <option value="best_selling" @selected(($filters['sort'] ?? '') === 'best_selling')>{{ __('Best Selling') }}</option>
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Lowest Price') }}</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Highest Price') }}</option>
                    </select>
                </label>
                <label>
                    <span>{{ __('Show') }}</span>
                    <select name="per_page">
                        @foreach([12, 24, 48] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <button>{{ __('Apply Filters') }}</button>
                <a href="{{ route('offers.index') }}" data-filter-link data-ajax-target="{{ $filterTarget }}">{{ __('Reset Filters') }}</a>
            </form>

            <div class="deals-toolbar">
                <div class="deals-results"><strong>{{ $products->total() }}</strong><span>{{ __('Deals') }}</span></div>
                <nav class="deals-view-switch" aria-label="{{ __('Offer view options') }}">
                    <a href="{{ route('offers.index', array_merge($queryFilters, ['view' => 'grid'])) }}" class="{{ $displayMode === 'grid' ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">▦</a>
                    <a href="{{ route('offers.index', array_merge($queryFilters, ['view' => 'list'])) }}" class="{{ $displayMode === 'list' ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">☰</a>
                </nav>
            </div>

            <div class="{{ $displayMode === 'list' ? 'deals-list' : 'deals-grid' }}" data-product-list data-product-view="{{ $displayMode }}">
                @forelse($dealCards as $deal)
                    <article class="deal-card {{ $displayMode === 'list' ? 'is-list' : '' }}">
                        <div class="deal-card-media">
                            <a href="{{ route('offers.show', $deal['slug']) }}" aria-label="{{ $deal['title'] }}">
                                <img src="{{ $deal['image_url'] }}" alt="{{ $deal['product_name'] }}" loading="lazy" decoding="async">
                            </a>
                            <span class="deal-discount">{{ $deal['discount_percentage'] > 0 ? '-' . number_format($deal['discount_percentage'], 0) . '%' : $deal['badge'] }}</span>
                            @if($deal['remaining_quantity'] !== null)
                                <span class="deal-quantity">{{ __('Only :count left', ['count' => $deal['remaining_quantity']]) }}</span>
                            @endif
                        </div>
                        <div class="deal-card-body">
                            <p class="deal-badge">{{ $deal['summary'] }}</p>
                            <h2><a href="{{ route('offers.show', $deal['slug']) }}">{{ $deal['product_name'] }}</a></h2>
                            <p class="deal-brand">{{ $deal['brand'] }}</p>
                            <div class="deal-prices">
                                <strong>{{ store_money($deal['offer_price']) }}</strong>
                                @if($deal['original_price'] > $deal['offer_price'])
                                    <del>{{ store_money($deal['original_price']) }}</del>
                                @endif
                                @if($deal['saving'] > 0)
                                    <span>{{ __('You save :amount', ['amount' => store_money($deal['saving'])]) }}</span>
                                @endif
                            </div>
                            @if($deal['progress'] !== null)
                                <div class="deal-progress">
                                    <p>{{ __('Sold :sold of :total', ['sold' => $deal['sold'], 'total' => $deal['max_quantity']]) }}</p>
                                    <span><i style="width: {{ $deal['progress'] }}%"></i></span>
                                </div>
                            @endif
                            @if($deal['ends_at'])
                                <div class="deal-card-countdown" data-countdown="{{ $deal['ends_at']->toIso8601String() }}">
                                    <span>{{ __('Ends in') }}</span>
                                    <strong><b data-countdown-total-hours>00</b>:<b data-countdown-minutes>00</b>:<b data-countdown-seconds>00</b></strong>
                                </div>
                            @endif
                        </div>
                        <div class="deal-card-actions">
                            <form method="POST" action="{{ route('offers.cart.add', $deal['slug']) }}" data-ajax-store-action>
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button>{{ __('Claim deal') }}</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="deals-empty">
                        <h2>{{ __('No active offers.') }}</h2>
                        <p>{{ __('Try changing filters or check back soon for new deals.') }}</p>
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
        </div>

        @if($endingSoonDeals->isNotEmpty())
            <section class="deals-urgent-section">
                <div class="deals-section-heading">
                    <div>
                        <p>{{ __('Ending soon') }}</p>
                        <h2>{{ __('Do not miss this chance') }}</h2>
                    </div>
                </div>
                <div class="deals-horizontal">
                    @foreach($endingSoonDeals as $offer)
                        <a href="{{ route('offers.show', $offer['slug']) }}" class="deals-strip-card">
                            <strong>{{ $offer['title'] }}</strong>
                            <span>{{ $offer['summary'] }}</span>
                            <small>{{ $offer['ends_at']?->diffForHumans() }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($topDeals->isNotEmpty())
            <section class="deals-top-section">
                <div class="deals-section-heading">
                    <div>
                        <p>{{ __('Top Deals') }}</p>
                        <h2>{{ __('Best savings today') }}</h2>
                    </div>
                </div>
                <div class="deals-top-list">
                    @foreach($topDeals as $offer)
                        <a href="{{ route('offers.show', $offer['slug']) }}">
                            <span>{{ $loop->iteration }}</span>
                            <strong>{{ $offer['title'] }}</strong>
                            <small>{{ number_format((float) $offer['discount_percentage'], 0) }}%</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="deals-trust">
            <div><span>🚚</span><strong>{{ __('Fast shipping') }}</strong><p>{{ __('Within 24 - 48 hours') }}</p></div>
            <div><span>✓</span><strong>{{ __('100% original products') }}</strong><p>{{ __('Original and guaranteed catalog') }}</p></div>
            <div><span>🎧</span><strong>{{ __('24/7 support') }}</strong><p>{{ __('Support paths remain visible after purchase.') }}</p></div>
            <div><span>↻</span><strong>{{ __('Warranty and easy returns') }}</strong><p>{{ __('Available according to store policy') }}</p></div>
        </section>
    </div>
</section>
@endsection
