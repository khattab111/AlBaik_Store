@extends('layouts.app')

@section('title', __('Brands'))

@section('content')
@php
    $sortOptions = [
        'name' => __('Alphabetical'),
        'products_desc' => __('Most products'),
        'latest' => __('Newest'),
    ];
    $activeSort = $filters['sort'] ?? 'name';
@endphp

<section class="store-section">
    <nav class="store-breadcrumb justify-end" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-amber-600">{{ __('Home') }}</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-950">{{ __('Brands') }}</span>
    </nav>

    <header class="brand-directory-hero">
        <div class="brand-directory-icon" aria-hidden="true">
            <span>◇</span>
        </div>
        <p class="store-eyebrow">{{ __('Trusted brands') }}</p>
        <h1>{{ __('Brands') }}</h1>
        <p>{{ __('Browse trusted global brands for electronics, phones, and accessories.') }}</p>
    </header>

    @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])

    <div data-ajax-filter-target="brands-directory">
    <form method="GET" class="brand-directory-toolbar" aria-label="{{ __('Filter brands') }}" data-ajax-filter data-ajax-target="[data-ajax-filter-target='brands-directory']">
        <div class="brand-count-pill">
            {{ trans_choice(':count brand|:count brands', $brands->total(), ['count' => $brands->total()]) }}
        </div>

        <div class="brand-sort-tabs" role="group" aria-label="{{ __('Sort brands') }}">
            @foreach ($sortOptions as $value => $label)
                <a href="{{ route('brands.index', array_filter(['search' => $filters['search'] ?? null, 'sort' => $value])) }}" class="{{ $activeSort === $value ? 'is-active' : '' }}" data-filter-link data-ajax-target="[data-ajax-filter-target='brands-directory']">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="brand-search-box">
            <label for="brand-search" class="sr-only">{{ __('Search brands') }}</label>
            <input id="brand-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search brands') }}">
            <input type="hidden" name="sort" value="{{ $activeSort }}">
            <button aria-label="{{ __('Search brands') }}">
                <span aria-hidden="true">⌕</span>
            </button>
        </div>
    </form>

    <div class="brand-directory-grid">
        @forelse($brands as $brand)
            @php
                $logoUrl = $brand->logo && file_exists(public_path('storage/'.$brand->logo)) ? asset('storage/'.$brand->logo) : null;
                $tone = ['amber', 'sky', 'emerald', 'violet', 'rose', 'slate'][$loop->index % 6];
            @endphp
            <article class="brand-directory-card">
                <a href="{{ route('brands.show', $brand->slug) }}" class="brand-logo-frame" aria-label="{{ __('View brand') }} {{ $brand->name }}">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brand->name }}">
                    @else
                        <span>{{ mb_substr($brand->name, 0, 1) }}</span>
                    @endif
                </a>

                <div class="brand-card-body">
                    <h2>
                        <a href="{{ route('brands.show', $brand->slug) }}">{{ $brand->name }}</a>
                        <span aria-hidden="true">◆</span>
                    </h2>
                    <p>{{ $brand->description ?: __('Original products from a trusted brand.') }}</p>
                    <span class="brand-product-pill is-{{ $tone }}">{{ $brand->products_count }} {{ __('Products') }}</span>
                </div>

                <a href="{{ route('brands.show', $brand->slug) }}" class="brand-card-action">
                    {{ __('View products') }}
                    <span aria-hidden="true">‹</span>
                </a>
            </article>
        @empty
            <div class="store-panel col-span-full p-10 text-center">
                <h2 class="text-xl font-black">{{ __('No brands found.') }}</h2>
                <p class="mt-2 text-slate-500">{{ __('Try changing filters or search terms.') }}</p>
            </div>
        @endforelse
    </div>

    <section class="brand-trust-strip" aria-label="{{ __('Purchase Confidence') }}">
        <div>
            <span aria-hidden="true">▣</span>
            <strong>{{ __('Shop with confidence') }}</strong>
            <p>{{ __('Warranty and easy returns') }}</p>
        </div>
        <div>
            <span aria-hidden="true">◇</span>
            <strong>{{ __('100% original products') }}</strong>
            <p>{{ __('Original and guaranteed catalog') }}</p>
        </div>
        <div>
            <span aria-hidden="true">◌</span>
            <strong>{{ __('Trusted and known') }}</strong>
            <p>{{ __('We work with global brands') }}</p>
        </div>
        <div>
            <span aria-hidden="true">↻</span>
            <strong>{{ __('Daily updates') }}</strong>
            <p>{{ __('We add new brands and products') }}</p>
        </div>
    </section>

    <div class="mt-8">{{ $brands->links() }}</div>
    </div>
</section>
@endsection
