@extends('layouts.app')

@section('title', __('Home'))

@section('content')
    @php
        $heroSlides = $banners->isNotEmpty()
            ? $banners
            : collect([(object) [
                'image' => null,
                'background_color' => null,
                'text_color' => null,
                'url' => route('products.index'),
                'secondary_url' => route('offers.index'),
                'sort_order' => 1,
                'localized' => fn (string $field, ?string $fallback = null) => $fallback,
            ]]);
    @endphp

    <section class="store-section pt-6" data-hero-slider>
        <div class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-100 shadow-sm">
            @foreach ($heroSlides as $index => $slide)
                @php
                    $heroImage = $slide->image && file_exists(public_path('storage/'.$slide->image))
                        ? asset('storage/'.$slide->image)
                        : asset('images/storefront/hero-phone.svg');
                    $title = method_exists($slide, 'localized') ? $slide->localized('title', __('Discounts up to 70% on original products')) : __('Discounts up to 70% on original products');
                    $subtitle = method_exists($slide, 'localized') ? $slide->localized('subtitle', __('Shop curated retail and wholesale products with competitive prices, reliable delivery, and secure manual payment.')) : __('Shop curated retail and wholesale products with competitive prices, reliable delivery, and secure manual payment.');
                    $eyebrow = method_exists($slide, 'localized') ? $slide->localized('eyebrow', __('Premium online store')) : __('Premium online store');
                    $primaryText = method_exists($slide, 'localized') ? $slide->localized('primary_button_text', __('Shop Now')) : __('Shop Now');
                    $secondaryText = method_exists($slide, 'localized') ? $slide->localized('secondary_button_text', __('View Offers')) : __('View Offers');
                    $featuredOffer = $flashOffers->first();
                @endphp
                <article class="{{ $index === 0 ? 'relative opacity-100' : 'absolute inset-0 opacity-0' }} isolate min-h-[520px] overflow-hidden transition-opacity duration-700" data-hero-slide aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_18%_20%,rgba(217,154,22,.14),transparent_28%),linear-gradient(135deg,#fff,#f5f7fb)]"></div>
                    <div class="mx-auto grid min-h-[520px] max-w-7xl items-center gap-8 px-5 py-10 lg:grid-cols-[0.9fr_1.1fr] lg:px-12">
                        <div class="relative z-10 max-w-3xl">
                            <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-4 py-2 text-sm font-black text-amber-700 shadow-sm">
                                <span aria-hidden="true">●</span>
                                {{ $eyebrow }}
                            </div>
                            <h1 class="mt-6 text-4xl font-black leading-tight text-slate-950 sm:text-5xl lg:text-6xl">
                                {{ $title }}
                            </h1>
                            <p class="mt-5 max-w-2xl text-base font-bold leading-8 text-slate-600 sm:text-lg">
                                {{ $subtitle }}
                            </p>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="{{ $slide->url ?: route('products.index') }}" class="store-button-primary">{{ $primaryText }} <span aria-hidden="true">←</span></a>
                                <a href="{{ $slide->secondary_url ?: route('offers.index') }}" class="store-button-secondary">{{ $secondaryText }}</a>
                            </div>
                            <div class="mt-10 grid max-w-xl grid-cols-3 gap-3 text-center">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <p class="text-2xl font-black text-amber-600">15k+</p>
                                    <p class="text-xs font-bold text-slate-500">{{ __('Customers') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <p class="text-2xl font-black text-amber-600">24h</p>
                                    <p class="text-xs font-bold text-slate-500">{{ __('Support') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <p class="text-2xl font-black text-amber-600">99%</p>
                                    <p class="text-xs font-bold text-slate-500">{{ __('Trust') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="overflow-hidden rounded-[2rem] bg-transparent p-2">
                                <img src="{{ $heroImage }}" class="aspect-[5/4] w-full rounded-[1.5rem] object-contain" alt="{{ $title }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async">
                            </div>
                            @if ($featuredOffer)
                                <a href="{{ route('offers.index') }}" class="absolute bottom-6 right-6 max-w-xs rounded-3xl border border-slate-200 bg-white p-5 text-slate-950 shadow-2xl transition hover:-translate-y-1 rtl:left-6 rtl:right-auto">
                                    <p class="text-xs font-black uppercase tracking-normal text-amber-600">{{ __('Limited deals') }}</p>
                                    <h2 class="mt-1 text-xl font-black">{{ $featuredOffer->title }}</h2>
                                    <p class="mt-2 text-sm font-bold text-slate-500">{{ __('View Offers') }}</p>
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
            @if ($heroSlides->count() > 1)
                <div class="absolute bottom-6 left-1/2 z-20 flex -translate-x-1/2 gap-2" data-hero-dots>
                    @foreach ($heroSlides as $index => $slide)
                        <button type="button" class="h-2.5 w-9 rounded-full bg-slate-300 transition data-[active=true]:bg-[var(--store-accent)]" data-hero-dot data-index="{{ $index }}" data-active="{{ $index === 0 ? 'true' : 'false' }}" aria-label="{{ __('Show slide :number', ['number' => $index + 1]) }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('partials.banner-strip', ['banners' => $homeAfterHeroBanners ?? collect()])

    <section class="store-section">
        <div class="grid gap-4 md:grid-cols-4">
            @foreach ([
                ['icon' => '🚚', 'title' => __('Fast Delivery'), 'text' => __('Flexible shipping options for every order.')],
                ['icon' => '🔒', 'title' => __('Secure Payment'), 'text' => __('Manual payments and receipts are reviewed clearly.')],
                ['icon' => '↩', 'title' => __('Easy Returns'), 'text' => __('Support paths remain visible after purchase.')],
                ['icon' => '★', 'title' => __('Original Products'), 'text' => __('Curated brands and verified catalog items.')],
            ] as $service)
                <div class="store-panel p-5 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="text-3xl">{{ $service['icon'] }}</div>
                    <h3 class="mt-3 font-black">{{ $service['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $service['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="store-section pt-4">
        <div class="mb-7 flex items-end justify-between gap-4">
            <div>
                <p class="store-eyebrow">{{ __('Shop by category') }}</p>
                <h2 class="store-section-title">{{ __('Popular Categories') }}</h2>
            </div>
            <a href="{{ route('categories.index') }}" class="store-button-secondary">{{ __('View All') }}</a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($categories as $category)
                @php
                    $categoryImage = match ($category->slug) {
                        'food', 'sandwiches', 'sauces' => asset('images/storefront/category-food.svg'),
                        'electronics', 'drinkware' => asset('images/storefront/category-electronics.svg'),
                        'bulk-supplies', 'bulk' => asset('images/storefront/category-bulk.svg'),
                        default => asset('images/storefront/category-default.svg'),
                    };
                @endphp
                <a href="{{ route('categories.show', $category->slug) }}" class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <img src="{{ $categoryImage }}" class="aspect-[7/4] w-full object-cover" alt="{{ $category->name }}">
                    <div class="p-5">
                        <h3 class="text-lg font-black">{{ $category->name }}</h3>
                        <p class="mt-1 text-sm font-bold text-slate-500">{{ $category->products_count }} {{ __('Products') }}</p>
                    </div>
                </a>
            @empty
                <p class="text-slate-500">{{ __('No categories found.') }}</p>
            @endforelse
        </div>
    </section>

    @if ($flashOffers->isNotEmpty())
        <section class="store-section">
            <div class="mb-7 flex items-end justify-between gap-4">
                <div>
                    <p class="store-eyebrow">{{ __('Limited deals') }}</p>
                    <h2 class="store-section-title">{{ __('Flash Offers') }}</h2>
                </div>
                <a href="{{ route('offers.index') }}" class="store-button-secondary">{{ __('All Offers') }}</a>
            </div>
            <div class="store-scroll-row">
                @foreach ($flashOffers as $offer)
                    @php
                        $seconds = $offer->ends_at ? max(0, now()->diffInSeconds($offer->ends_at, false)) : null;
                        $days = $seconds !== null ? floor($seconds / 86400) : 0;
                        $hours = $seconds !== null ? floor(($seconds % 86400) / 3600) : 0;
                        $minutes = $seconds !== null ? floor(($seconds % 3600) / 60) : 0;
                    @endphp
                    <article class="min-w-[320px] max-w-sm snap-start rounded-3xl bg-slate-950 p-5 text-white shadow-xl shadow-slate-950/10">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-black text-amber-300">{{ __('Flash Offer') }}</p>
                                <h3 class="mt-1 text-xl font-black">{{ $offer->title }}</h3>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-4 py-3 text-center">
                                <p class="text-lg font-black">{{ sprintf('%02d:%02d:%02d', $days * 24 + $hours, $minutes, 0) }}</p>
                                <p class="text-xs text-slate-300">{{ __('Remaining') }}</p>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-3 gap-3">
                            @foreach ($offer->items->pluck('product')->filter()->take(3) as $product)
                                @php
                                    $image = $product->images->first()?->path;
                                    $imageUrl = $image && file_exists(public_path('storage/'.$image))
                                        ? asset('storage/'.$image)
                                        : asset('images/storefront/product-fallback.svg');
                                @endphp
                                <a href="{{ route('products.show', $product->slug) }}" class="overflow-hidden rounded-2xl bg-white">
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.banner-strip', ['banners' => $homeBeforeProductsBanners ?? collect()])

    <section class="store-section">
        <div class="store-panel p-5 sm:p-7">
            <input id="tab-featured" class="peer/featured hidden" type="radio" name="home-products" checked>
            <input id="tab-latest" class="peer/latest hidden" type="radio" name="home-products">
            <input id="tab-rated" class="peer/rated hidden" type="radio" name="home-products">

            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="store-eyebrow">{{ __('Selected for you') }}</p>
                    <h2 class="store-section-title">{{ __('Best Store Picks') }}</h2>
                </div>
                <div class="store-tab-labels flex flex-wrap gap-2 text-sm font-black">
                    <label for="tab-featured" class="cursor-pointer rounded-2xl border border-slate-200 px-4 py-3 transition">{{ __('Best Selling') }}</label>
                    <label for="tab-latest" class="cursor-pointer rounded-2xl border border-slate-200 px-4 py-3 transition">{{ __('New Arrivals') }}</label>
                    <label for="tab-rated" class="cursor-pointer rounded-2xl border border-slate-200 px-4 py-3 transition">{{ __('Top Rated') }}</label>
                </div>
            </div>

            <div class="store-tabs-content">
                <div id="featured-panel" class="store-tab-panel store-product-grid">
                    @forelse ($bestSellingProducts as $product)
                        @include('partials.product-card', ['product' => $product])
                    @empty
                        @foreach ($featuredProducts as $product)
                            @include('partials.product-card', ['product' => $product])
                        @endforeach
                    @endforelse
                </div>
                <div id="latest-panel" class="store-tab-panel store-product-grid">
                    @forelse ($latestProducts as $product)
                        @include('partials.product-card', ['product' => $product])
                    @empty
                        <p class="text-slate-500">{{ __('No products found.') }}</p>
                    @endforelse
                </div>
                <div id="rated-panel" class="store-tab-panel store-product-grid">
                    @forelse ($topRatedProducts as $product)
                        @include('partials.product-card', ['product' => $product])
                    @empty
                        <p class="text-slate-500">{{ __('No products found.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="store-section">
        <div class="mb-7 flex items-end justify-between gap-4">
            <div>
                <p class="store-eyebrow">{{ __('Trusted brands') }}</p>
                <h2 class="store-section-title">{{ __('Brand Wall') }}</h2>
            </div>
            <a href="{{ route('brands.index') }}" class="store-button-secondary">{{ __('View All') }}</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($brands as $brand)
                @php
                    $logoUrl = $brand->logo && file_exists(public_path('storage/'.$brand->logo)) ? asset('storage/'.$brand->logo) : null;
                @endphp
                <a href="{{ route('brands.show', $brand->slug) }}" class="store-panel flex min-h-32 items-center justify-center p-6 text-center transition hover:-translate-y-1 hover:shadow-lg">
                    <div>
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" class="mx-auto h-16 max-w-36 object-contain" alt="{{ $brand->name }}">
                        @else
                            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-2xl font-black text-red-700">{{ mb_substr($brand->name, 0, 1) }}</span>
                        @endif
                        <h3 class="mt-3 font-black">{{ $brand->name }}</h3>
                    </div>
                </a>
            @empty
                <p class="text-slate-500">{{ __('No brands found.') }}</p>
            @endforelse
        </div>
    </section>

    <section class="store-section">
        <div class="grid gap-4 rounded-[2rem] bg-slate-950 p-6 text-white sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($storeStats as $stat)
                <div class="rounded-3xl bg-white/10 p-6 text-center">
                    <p class="text-4xl font-black text-amber-300">{{ number_format($stat['value']) }}{{ $stat['suffix'] ?? '+' }}</p>
                    <p class="mt-2 text-sm font-bold text-slate-200">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="store-section">
        <div class="overflow-hidden rounded-[2rem] bg-slate-950 bg-cover bg-center p-6 text-white shadow-2xl shadow-slate-950/10 sm:p-10" style="background-image: linear-gradient(90deg, rgba(17,17,17,.96), rgba(17,17,17,.82)), url('{{ asset('images/storefront/newsletter-pattern.svg') }}')">
            <div class="grid items-center gap-6 lg:grid-cols-[1fr_auto]">
                <div>
                    <p class="text-sm font-black text-amber-300">{{ __('Newsletter') }}</p>
                    <h2 class="mt-2 text-3xl font-black">{{ __('Subscribe to receive the latest offers') }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-200">{{ __('Get launch offers, restock alerts, and wholesale updates directly in your inbox.') }}</p>
                </div>
                <form method="POST" action="{{ route('newsletter.store') }}" class="flex w-full max-w-xl flex-col gap-3 sm:flex-row">
                    @csrf
                    <input type="email" name="email" class="min-w-0 flex-1 rounded-2xl border-0 px-4 py-3 text-slate-950 outline-none" placeholder="{{ __('Email address') }}" required>
                    <button class="rounded-2xl bg-amber-400 px-6 py-3 text-sm font-black text-slate-950 transition hover:bg-amber-300">{{ __('Subscribe') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
