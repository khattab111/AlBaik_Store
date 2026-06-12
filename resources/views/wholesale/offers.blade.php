@extends('layouts.app')

@section('title', __('Wholesale offers'))
@section('meta_description', __('Wholesale flash offers for approved wholesale customers.'))

@section('content')
@php
    $displayMode = $displayMode ?? 'grid';
    $queryFilters = collect($filters ?? [])->except('page')->filter(fn ($value) => filled($value))->all();
    $perPage = (int) ($filters['per_page'] ?? 12);
    $filterTarget = "[data-ajax-filter-target='wholesale-offers-directory']";
    $dealCards = collect($presentedOffers ?? []);
@endphp

<section class="deals-page">
    <div class="deals-shell">
        <nav class="store-breadcrumb deals-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ __('Wholesale offers') }}</span>
        </nav>

        <section class="deals-hero">
            <div class="deals-hero-content">
                <p class="deals-eyebrow">{{ __('Wholesale Partnership') }}</p>
                <h1>{{ __('Wholesale offers') }}</h1>
                <p>{{ __('Exclusive wholesale deals for approved business customers, calculated and validated from the backend.') }}</p>
                <div class="deals-hero-actions">
                    <a href="#wholesale-offers-directory" class="deals-primary">{{ __('Shop offers now') }}</a>
                    <a href="{{ route('wholesale.products.index') }}" class="deals-secondary">{{ __('Wholesale products') }}</a>
                </div>
            </div>
        </section>

        <form method="GET" class="deals-smart-filter" data-ajax-filter data-ajax-target="{{ $filterTarget }}">
            <label>
                <span>{{ __('Offer type') }}</span>
                <select name="type">
                    <option value="">{{ __('All') }}</option>
                    @foreach(\App\Models\FlashOffer::typeOptions() as $type => $label)
                        <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>{{ __('Category') }}</span>
                <select name="category">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>{{ __('Brand') }}</span>
                <select name="brand">
                    <option value="">{{ __('All brands') }}</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>{{ __('Sort') }}</span>
                <select name="sort">
                    <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
                    <option value="highest_discount" @selected(($filters['sort'] ?? '') === 'highest_discount')>{{ __('Highest discount') }}</option>
                    <option value="ending_soon" @selected(($filters['sort'] ?? '') === 'ending_soon')>{{ __('Ending soon') }}</option>
                    <option value="best_selling" @selected(($filters['sort'] ?? '') === 'best_selling')>{{ __('Best Selling') }}</option>
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
            <a href="{{ route('wholesale.offers.index') }}" data-filter-link data-ajax-target="{{ $filterTarget }}">{{ __('Reset Filters') }}</a>
        </form>

        <div id="wholesale-offers-directory" data-ajax-filter-target="wholesale-offers-directory">
            <div class="deals-toolbar">
                <div class="deals-results"><strong>{{ $offersPaginator->total() }}</strong><span>{{ __('Deals') }}</span></div>
                <nav class="deals-view-switch" aria-label="{{ __('Offer view options') }}">
                    <a href="{{ route('wholesale.offers.index', array_merge($queryFilters, ['view' => 'grid'])) }}" class="{{ $displayMode === 'grid' ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">▦</a>
                    <a href="{{ route('wholesale.offers.index', array_merge($queryFilters, ['view' => 'list'])) }}" class="{{ $displayMode === 'list' ? 'is-active' : '' }}" data-filter-link data-ajax-target="{{ $filterTarget }}">☰</a>
                </nav>
            </div>

            <div class="{{ $displayMode === 'list' ? 'deals-list' : 'deals-grid' }}" data-product-list data-product-view="{{ $displayMode }}">
                @forelse($dealCards as $deal)
                    <article class="deal-card {{ $displayMode === 'list' ? 'is-list' : '' }}">
                        <div class="deal-card-media">
                            <a href="{{ route('offers.show', $deal['slug']) }}" aria-label="{{ $deal['title'] }}">
                                <img src="{{ $deal['image_url'] }}" alt="{{ $deal['product_name'] }}" loading="lazy" decoding="async">
                            </a>
                            <span class="deal-discount">{{ $deal['discount_percentage'] > 0 ? '-' . number_format($deal['discount_percentage'], 0) . '%' : __('Wholesale') }}</span>
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
                @if($offersPaginator->hasMorePages())
                    <a href="{{ $offersPaginator->nextPageUrl() }}" class="premium-load-more" data-load-more data-ajax-target="{{ $filterTarget }}">{{ __('Load More') }} <span aria-hidden="true">⌄</span></a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
