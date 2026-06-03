@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', $product->seo_description ?: ($product->short_description ?: __('View product details, price, stock, variants, and related products.')))
@section('canonical', route('products.show', $product->slug))
@section('og_type', 'product')

@php
    $imageUrl = function (?string $path): string {
        return $path && file_exists(public_path('storage/'.$path))
            ? asset('storage/'.$path)
            : asset('images/storefront/product-fallback.svg');
    };

    $mainImage = $product->images->first()?->path;
    $mainImageUrl = $imageUrl($mainImage);
    $galleryImages = $product->images->isNotEmpty() ? $product->images : collect([(object) ['path' => null, 'alt_text' => $product->name]]);
    $rating = round((float) $product->reviews->avg('rating'), 1);
    $reviewsCount = $product->reviews->count();
    $stock = $product->availableStock();
    $hasDiscount = $pricing->originalPrice && $pricing->originalPrice > $pricing->price;
    $discountPercent = $hasDiscount ? round(((float) $pricing->originalPrice - (float) $pricing->price) / (float) $pricing->originalPrice * 100) : null;
    $dimensions = ($product->length || $product->width || $product->height)
        ? trim(($product->length ?: '-').' x '.($product->width ?: '-').' x '.($product->height ?: '-')).' cm'
        : __('Not specified');

    $variantAttributes = $product->variants
        ->flatMap(fn ($variant) => collect($variant->attributes ?? []))
        ->filter(fn ($value) => filled($value))
        ->unique()
        ->take(10);

    $topSpecs = collect($variantAttributes)->take(4);
    if ($topSpecs->isEmpty()) {
        $topSpecs = collect([
            __('Brand') => $product->brand?->name,
            __('Category') => $product->category?->name,
            __('SKU') => $product->sku,
            __('Weight') => $product->weight ? $product->weight.' kg' : null,
        ])->filter();
    }

    $featureBullets = collect([
        $product->brand ? __('Original :brand product', ['brand' => $product->brand->name]) : __('Original product'),
        $product->category ? __('Selected from :category category', ['category' => $product->category->name]) : null,
        $stock > 0 ? __('Available now with verified stock') : __('Currently out of stock'),
        $product->requires_shipping ? __('Shipping options are calculated at checkout') : __('No shipping is required for this product'),
        $product->free_shipping ? __('This product has free shipping') : null,
    ])->filter()->values();

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => strip_tags($product->short_description ?: $product->description ?: $product->name),
        'sku' => $product->sku,
        'brand' => ['@type' => 'Brand', 'name' => $product->brand?->name ?: 'AlBaik Store'],
        'image' => [$mainImageUrl],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => $currentCurrency?->code ?? 'USD',
            'price' => (float) $pricing->price,
            'availability' => $stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('products.show', $product->slug),
        ],
    ];
@endphp

@section('og_image', $mainImageUrl)
@section('structured_data')
    <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endsection

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-amber-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('products.index') }}" class="transition hover:text-amber-700">{{ __('Products') }}</a>
        @if($product->category)
            <span aria-hidden="true">›</span>
            <a href="{{ route('categories.show', $product->category->slug) }}" class="transition hover:text-amber-700">{{ $product->category->name }}</a>
        @endif
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $product->name }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
        <section class="store-panel p-4 sm:p-5" aria-label="{{ __('Product images') }}">
            <div class="grid gap-4 sm:grid-cols-[92px_1fr]">
                <div class="order-2 grid grid-cols-4 gap-3 sm:order-1 sm:grid-cols-1">
                    @foreach($galleryImages->take(6) as $image)
                        <button type="button" class="aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition hover:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500" data-product-gallery-thumb data-image="{{ $imageUrl($image->path ?? null) }}">
                            <img src="{{ $imageUrl($image->path ?? null) }}" loading="lazy" decoding="async" class="h-full w-full object-contain" alt="{{ $image->alt_text ?? $product->name }}">
                        </button>
                    @endforeach
                </div>
                <div class="order-1 relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-50 via-white to-slate-100 sm:order-2">
                    @if($discountPercent)
                        <span class="absolute start-5 top-5 z-10 rounded-full bg-red-600 px-4 py-2 text-sm font-black text-white">{{ __('Discount') }} {{ $discountPercent }}%</span>
                    @endif
                    <img id="product-main-image" src="{{ $mainImageUrl }}" loading="eager" fetchpriority="high" decoding="async" class="aspect-square w-full object-contain p-8 sm:p-12" alt="{{ $product->name }}">
                </div>
            </div>
        </section>

        <section class="store-panel p-6 sm:p-8" aria-labelledby="product-title">
            <div class="mb-4 flex flex-wrap gap-2 text-sm font-black">
                @if($product->brand)
                    <a href="{{ route('brands.show', $product->brand->slug) }}" class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-amber-700">{{ $product->brand->name }}</a>
                @endif
                @if($product->category)
                    <a href="{{ route('categories.show', $product->category->slug) }}" class="rounded-full bg-slate-100 px-4 py-2 text-slate-700">{{ $product->category->name }}</a>
                @endif
                <span class="rounded-full px-4 py-2 {{ $stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $stock > 0 ? __('In Stock') : __('Out of Stock') }}
                </span>
            </div>

            <h1 id="product-title" class="store-safe-text text-3xl font-black leading-tight text-slate-950 sm:text-5xl">{{ $product->name }}</h1>

            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
                <span class="text-amber-500" aria-hidden="true">★★★★★</span>
                <span class="font-black text-slate-700">{{ $rating ?: '0.0' }}</span>
                <span class="font-bold text-slate-500">({{ $reviewsCount }} {{ __('Reviews') }})</span>
                @if($product->sku)
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-600">{{ __('SKU') }}: {{ $product->sku }}</span>
                @endif
            </div>

            @if($topSpecs->isNotEmpty())
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach($topSpecs as $label => $value)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
                            <p class="store-safe-text text-sm font-black text-slate-950">{{ $value }}</p>
                            <p class="mt-1 text-xs font-bold text-slate-500">{{ is_string($label) ? __(str_replace('_', ' ', ucfirst($label))) : __('Specification') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-7 border-y border-slate-200 py-6">
                <div class="flex flex-wrap items-end gap-3">
                    @if($hasDiscount)
                        <span class="text-xl font-black text-slate-400 line-through">{{ store_money((float) $pricing->originalPrice) }}</span>
                    @endif
                    <span class="store-safe-text text-3xl font-black text-red-700 sm:text-4xl">{{ store_money((float) $pricing->price) }}</span>
                    @if($discountPercent)
                        <span class="rounded-full bg-red-50 px-3 py-1 text-sm font-black text-red-700">{{ __('Save') }} {{ $discountPercent }}%</span>
                    @endif
                </div>
                <p class="mt-2 text-sm font-bold text-slate-500">
                    {{ $pricing->flashOffer ? __('Product flash offer price') : ($pricing->priceType === 'wholesale' ? __('Wholesale price applied for this quantity.') : __('Retail price')) }}
                </p>
                @if($hasDiscount)
                    <p class="mt-1 text-sm font-black text-emerald-700">{{ __('You save :amount', ['amount' => store_money((float) $pricing->originalPrice - (float) $pricing->price)]) }}</p>
                @endif
            </div>

            <div class="mt-6">
                <p class="store-safe-text text-base leading-8 text-slate-600">{{ $product->short_description ?: __('A carefully selected product with verified store data and checkout-time shipping calculation.') }}</p>
                <p id="product-stock-help" class="mt-3 text-sm font-black {{ $stock > 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ __('Available quantity') }}: {{ $stock }}
                </p>
            </div>

            @if($isWholesaleCustomer && $wholesaleTiers->isNotEmpty())
                <div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                    <h2 class="text-lg font-black text-emerald-900">{{ __('Wholesale quantity tiers') }}</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach($wholesaleTiers as $tier)
                            <button type="button" class="rounded-2xl bg-white p-4 text-center shadow-sm transition hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                data-wholesale-tier
                                data-quantity="{{ $tier->min_quantity }}"
                                data-price="{{ number_format((float) $tier->price, 2, '.', '') }}">
                                <p class="text-sm font-black text-emerald-700">{{ $tier->min_quantity }}+ {{ __('pieces') }}</p>
                                <p class="store-safe-text mt-1 text-xl font-black text-slate-950">{{ store_money((float) $tier->price) }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-7 grid gap-3">
                <form method="POST" action="{{ route('cart.add', $product) }}" class="grid gap-3 sm:grid-cols-[140px_1fr]" data-ajax-store-action aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}">
                    @csrf
                    @if($product->variants->isNotEmpty())
                        <div class="sm:col-span-2">
                            <label for="product-variant" class="text-sm font-black">{{ __('Variant') }}</label>
                            <select id="product-variant" name="variant_id" class="store-field mt-2">
                                <option value="">{{ __('Default') }}</option>
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}">{{ $variant->sku }} - {{ collect($variant->attributes)->map(fn($v, $k) => __(str_replace('_', ' ', ucfirst($k))).': '.$v)->implode(', ') }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="product-quantity" class="sr-only">{{ __('Quantity') }}</label>
                        <input id="product-quantity" type="number" name="quantity" value="1" min="1" class="store-field" aria-describedby="product-stock-help" data-product-quantity>
                    </div>
                    <button class="store-button-primary w-full text-base" @disabled($stock <= 0)>{{ __('Add to Cart') }}</button>
                </form>

                @auth
                    <form method="POST" action="{{ route('favorites.toggle', $product) }}" data-ajax-store-action>
                        @csrf
                        <button class="store-button-secondary w-full">{{ __('Add to Wishlist') }}</button>
                    </form>
                @else
                    <a href="{{ route('customer.login') }}" class="store-button-secondary w-full">{{ __('Login for Wishlist') }}</a>
                @endauth
            </div>

            <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([__('Secure payment'), __('Verified product'), __('Checkout shipping'), __('Return policy')] as $benefit)
                    <div class="rounded-2xl bg-slate-50 p-3 text-center text-xs font-black text-slate-600">{{ $benefit }}</div>
                @endforeach
            </div>
        </section>
    </div>
</section>

<section class="store-section pt-0">
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="grid gap-6">
            <section class="store-panel p-6" aria-labelledby="description-heading">
                <h2 id="description-heading" class="text-2xl font-black">{{ __('Description and Highlights') }}</h2>
                <div class="mt-5 grid gap-6 lg:grid-cols-[1fr_320px]">
                    <div>
                        <h3 class="text-lg font-black">{{ __('Full description') }}</h3>
                        <p class="store-safe-text mt-3 whitespace-pre-line leading-8 text-slate-600">{{ $product->description ?: $product->short_description ?: __('No detailed description is available yet.') }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5">
                        <h3 class="font-black">{{ __('Why choose this product?') }}</h3>
                        <ul class="mt-4 grid gap-3 text-sm font-bold text-slate-700">
                            @foreach($featureBullets as $feature)
                                <li class="flex gap-2">
                                    <span class="mt-1 size-2 rounded-full bg-emerald-500"></span>
                                    <span class="store-safe-text">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>

            <section class="store-panel p-6" aria-labelledby="specifications-heading">
                <h2 id="specifications-heading" class="text-2xl font-black">{{ __('Technical Specifications') }}</h2>
                <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                        __('SKU') => $product->sku,
                        __('Barcode') => $product->barcode,
                        __('Brand') => $product->brand?->name,
                        __('Category') => $product->category?->name,
                        __('Supplier') => $product->supplier?->name,
                        __('Stock quantity') => $stock,
                        __('Stock status') => $stock > 0 ? __('In Stock') : __('Out of Stock'),
                        __('Weight') => $product->weight ? $product->weight.' kg' : __('Not specified'),
                        __('Dimensions') => $dimensions,
                        __('Warranty') => __('Available according to store policy'),
                    ] as $label => $value)
                        @if($value !== null && $value !== '')
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <dt class="text-xs font-black uppercase text-slate-500">{{ $label }}</dt>
                                <dd class="store-safe-text mt-1 font-black text-slate-950">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                @if($variantAttributes->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="text-lg font-black">{{ __('Product attributes') }}</h3>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($variantAttributes as $attribute => $value)
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <dt class="text-xs font-black uppercase text-slate-500">{{ __(str_replace('_', ' ', ucfirst($attribute))) }}</dt>
                                    <dd class="store-safe-text mt-1 font-black text-slate-950">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                @if($product->variants->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="text-lg font-black">{{ __('Variants') }}</h3>
                        <div class="mt-4 grid gap-3">
                            @foreach($product->variants as $variant)
                                <article class="rounded-2xl border border-slate-200 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <p class="store-safe-text font-black">{{ $variant->sku }}</p>
                                        <p class="text-sm font-bold text-slate-500">{{ __('Available') }}: {{ $variant->available_stock }}</p>
                                    </div>
                                    @if($variant->attributes)
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($variant->attributes as $attribute => $value)
                                                <span class="store-safe-text rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __(str_replace('_', ' ', ucfirst($attribute))) }}: {{ $value }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            @if($activeOfferDetails->isNotEmpty())
                <section class="store-panel p-6" aria-labelledby="linked-offers-heading">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="store-eyebrow">{{ __('Offers') }}</p>
                            <h2 id="linked-offers-heading" class="text-2xl font-black">{{ __('Offers linked to this product') }}</h2>
                        </div>
                        <a href="{{ route('offers.index') }}" class="text-sm font-black text-amber-700 hover:text-slate-950">{{ __('All offers') }}</a>
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach($activeOfferDetails as $offer)
                            <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase text-amber-700">{{ $offer['badge'] }}</p>
                                        <h3 class="store-safe-text mt-1 text-xl font-black text-slate-950">{{ $offer['title'] }}</h3>
                                        <p class="store-safe-text mt-2 text-sm font-bold text-slate-700">{{ $offer['summary'] }}</p>
                                    </div>
                                    @if($offer['discount_percentage'] > 0)
                                        <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-black text-white">-{{ $offer['discount_percentage'] }}%</span>
                                    @endif
                                </div>
                                <dl class="mt-4 grid gap-2 text-sm font-bold text-slate-700">
                                    <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-3"><dt>{{ __('Original') }}</dt><dd class="whitespace-nowrap">{{ store_money((float) $offer['original_price']) }}</dd></div>
                                    <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-3"><dt>{{ __('Offer price') }}</dt><dd class="whitespace-nowrap">{{ store_money((float) $offer['offer_price']) }}</dd></div>
                                    @if($offer['ends_at'])
                                        <div class="flex justify-between gap-3"><dt>{{ __('Ends at') }}</dt><dd>{{ $offer['ends_at']->format('Y-m-d H:i') }}</dd></div>
                                    @endif
                                </dl>
                                <a href="{{ route('offers.show', ['flashOffer' => $offer['slug']]) }}" class="store-button-secondary mt-5 w-full">{{ __('View offer details') }}</a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="grid gap-6">
            <section class="store-panel p-6" aria-labelledby="shipping-heading">
                <h2 id="shipping-heading" class="text-xl font-black">{{ __('Shipping Information') }}</h2>
                <dl class="mt-5 grid gap-4 text-sm font-bold">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">{{ __('Requires shipping') }}</dt>
                        <dd class="{{ $product->requires_shipping ? 'text-emerald-700' : 'text-slate-700' }}">{{ $product->requires_shipping ? __('Yes') : __('No') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">{{ __('Shipping weight') }}</dt>
                        <dd>{{ $product->weight ? $product->weight.' kg' : __('Default store weight') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">{{ __('Free shipping') }}</dt>
                        <dd class="{{ $product->free_shipping ? 'text-emerald-700' : 'text-slate-700' }}">{{ $product->free_shipping ? __('Yes') : __('No') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Checkout calculation') }}</dt>
                        <dd class="mt-2 leading-7 text-slate-700">{{ __('Available cities and carriers appear during checkout according to the selected address.') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="store-panel p-6" aria-labelledby="confidence-heading">
                <h2 id="confidence-heading" class="text-xl font-black">{{ __('Purchase Confidence') }}</h2>
                <div class="mt-5 grid gap-3 text-sm font-bold text-slate-600">
                    <p>{{ __('Secure manual and cash payment options.') }}</p>
                    <p>{{ __('Order snapshot keeps the purchased price and shipping details unchanged.') }}</p>
                    <p>{{ __('Admin reviews payments and shipment status from Filament.') }}</p>
                </div>
            </section>
        </aside>
    </div>
</section>

<section class="store-section pt-0">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <p class="store-eyebrow">{{ __('You may also like') }}</p>
            <h2 class="store-section-title">{{ __('Similar Products') }}</h2>
        </div>
        @if($product->category)
            <a href="{{ route('categories.show', $product->category->slug) }}" class="text-sm font-black text-amber-700">{{ __('View category') }}</a>
        @endif
    </div>
    <div class="store-product-grid">
        @forelse($similarProducts as $relatedProduct)
            @include('partials.product-card', ['product' => $relatedProduct])
        @empty
            <p class="rounded-3xl bg-white p-6 font-bold text-slate-500">{{ __('No products found.') }}</p>
        @endforelse
    </div>
</section>

@if($brandProducts->isNotEmpty())
    <section class="store-section pt-0">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="store-eyebrow">{{ $product->brand?->name }}</p>
                <h2 class="store-section-title">{{ __('More from this brand') }}</h2>
            </div>
        </div>
        <div class="store-product-grid">
            @foreach($brandProducts as $relatedProduct)
                @include('partials.product-card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </section>
@endif

@if($recommendedProducts->isNotEmpty())
    <section class="store-section pt-0">
        <div class="mb-6">
            <p class="store-eyebrow">{{ __('Recommended') }}</p>
            <h2 class="store-section-title">{{ __('Products you may need with this item') }}</h2>
        </div>
        <div class="store-product-grid">
            @foreach($recommendedProducts as $relatedProduct)
                @include('partials.product-card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </section>
@endif

@endsection
