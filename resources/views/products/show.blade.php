@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', $product->seo_description ?: ($product->short_description ?: __('View product details, price, stock, variants, and related products.')))
@section('canonical', route('products.show', $product->slug))
@section('og_type', 'product')

@section('content')
@php
    $mainImage = $product->images->first()?->path;
    $mainImageUrl = $mainImage && file_exists(public_path('storage/'.$mainImage))
        ? asset('storage/'.$mainImage)
        : asset('images/storefront/product-fallback.svg');
    $rating = round((float) $product->reviews->avg('rating'), 1);
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
            'priceCurrency' => 'USD',
            'price' => (float) $product->retail_price,
            'availability' => $product->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('products.show', $product->slug),
        ],
    ];
@endphp
@section('og_image', $mainImageUrl)
@section('structured_data')
    <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endsection

<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('products.index') }}" class="transition hover:text-red-700">{{ __('Products') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $product->name }}</span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-[1fr_0.9fr]">
        <div class="grid gap-4">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <img src="{{ $mainImageUrl }}" loading="eager" fetchpriority="high" decoding="async" class="aspect-square w-full object-cover" alt="{{ $product->name }}">
            </div>
            @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-3">
                    @foreach($product->images as $image)
                        @php
                            $thumbUrl = file_exists(public_path('storage/'.$image->path))
                                ? asset('storage/'.$image->path)
                                : asset('images/storefront/product-fallback.svg');
                        @endphp
                        <img src="{{ $thumbUrl }}" loading="lazy" decoding="async" class="aspect-square rounded-2xl border border-slate-200 bg-white object-cover" alt="{{ $image->alt_text ?: $product->name }}">
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <div class="mb-4 flex flex-wrap gap-2 text-sm font-black">
                @if($product->brand)
                    <a href="{{ route('brands.show', $product->brand->slug) }}" class="rounded-full bg-red-50 px-4 py-2 text-red-700">{{ $product->brand->name }}</a>
                @endif
                @if($product->category)
                    <a href="{{ route('categories.show', $product->category->slug) }}" class="rounded-full bg-slate-100 px-4 py-2 text-slate-700">{{ $product->category->name }}</a>
                @endif
            </div>
            <h1 class="text-4xl font-black leading-tight">{{ $product->name }}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="text-amber-500" aria-hidden="true">★★★★★</span>
                <span class="font-black text-slate-700">{{ $rating ?: '0.0' }}</span>
                <span class="text-sm font-bold text-slate-500">({{ $product->reviews->count() }} {{ __('Reviews') }})</span>
                <span class="sr-only">{{ __('Rating: :rating out of 5 from :count reviews', ['rating' => $rating ?: '0.0', 'count' => $product->reviews->count()]) }}</span>
            </div>
            <div class="mt-6 grid gap-3">
                @if($pricing->originalPrice && $pricing->originalPrice > $pricing->price)
                    <p class="text-base font-bold text-slate-400 line-through">USD {{ number_format((float) $pricing->originalPrice, 2) }}</p>
                @endif
                <p class="text-4xl font-black text-red-700">USD {{ number_format((float) $pricing->price, 2) }}</p>
                <p class="text-sm font-bold text-slate-500">
                    @if($pricing->flashOffer)
                        {{ __('Flash offer price') }}
                    @else
                        {{ $pricing->priceType === 'wholesale' ? __('Wholesale price applied for this quantity.') : __('Retail price') }}
                    @endif
                </p>
                @if($pricing->flashOffer)
                    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-900">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span>{{ __('Flash Offer') }}: {{ $pricing->flashOffer->title }}</span>
                            @if($pricing->flashOffer->ends_at)
                                <span>{{ __('Ends at') }} {{ $pricing->flashOffer->ends_at->format('Y-m-d H:i') }}</span>
                            @endif
                        </div>
                        @if($pricing->flashOffer->max_quantity)
                            <p class="mt-2">{{ __('Remaining quantity') }}: {{ $pricing->flashOffer->remainingQuantity() }}</p>
                        @endif
                    </div>
                @endif
            </div>
            <p class="mt-5 text-lg leading-8 text-slate-600">{{ $product->short_description }}</p>

            @if($isWholesaleCustomer)
                <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black">{{ __('Wholesale Pricing') }}</h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('Choose a quantity tier, then add it to cart. The final price is recalculated on the server.') }}</p>
                        </div>
                    </div>

                    @if($wholesaleTiers->isNotEmpty())
                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach($wholesaleTiers as $tier)
                            <button type="button" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center transition hover:-translate-y-1 hover:border-emerald-400 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                data-wholesale-tier
                                data-quantity="{{ $tier->min_quantity }}"
                                data-price="{{ number_format((float) $tier->price, 2, '.', '') }}"
                                aria-label="{{ __('Choose wholesale tier :quantity pieces', ['quantity' => $tier->min_quantity]) }}">
                                <p class="text-sm font-black text-emerald-700">{{ $tier->min_quantity }}+ {{ __('pieces') }}</p>
                                <p class="mt-1 text-xl font-black text-slate-950">USD {{ number_format((float) $tier->price, 2) }}</p>
                                <p class="mt-2 text-xs font-bold text-emerald-700">{{ __('Select this tier') }}</p>
                            </button>
                        @endforeach
                    </div>
                    @else
                        <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-600">{{ __('No active wholesale tiers are available for this product yet.') }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-4 text-center shadow-sm"><p class="font-black">{{ __('Original') }}</p><p class="text-xs text-slate-500">{{ __('Verified catalog') }}</p></div>
                <div class="rounded-2xl bg-white p-4 text-center shadow-sm"><p class="font-black">{{ __('Stock') }}</p><p id="product-stock-help" class="text-xs text-slate-500">{{ $product->stock_quantity }} {{ __('Available') }}</p></div>
                <div class="rounded-2xl bg-white p-4 text-center shadow-sm">
                    <p class="font-black">{{ $isWholesaleCustomer ? __('Wholesale') : __('Availability') }}</p>
                    <p class="text-xs text-slate-500">{{ $isWholesaleCustomer ? __('Tier pricing enabled') : __('Ready to order') }}</p>
                </div>
            </div>

            <div class="mt-7 store-panel p-5">
                @auth
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="grid gap-4" data-ajax-store-action aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}">
                        @csrf
                        @if($product->variants->isNotEmpty())
                            <label for="product-variant" class="text-sm font-black">{{ __('Variant') }}</label>
                            <select id="product-variant" name="variant_id" class="store-field">
                                <option value="">{{ __('Default') }}</option>
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}">{{ $variant->sku }} - {{ collect($variant->attributes)->map(fn($v, $k) => $k.': '.$v)->implode(', ') }}</option>
                                @endforeach
                            </select>
                        @endif
                        <div class="grid gap-3 sm:grid-cols-[120px_1fr]">
                            <div>
                                <label for="product-quantity" class="sr-only">{{ __('Quantity') }}</label>
                                <input id="product-quantity" type="number" name="quantity" value="1" min="1" class="store-field" aria-describedby="product-stock-help" data-product-quantity>
                            </div>
                            <button class="store-button-primary" aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}" @disabled($product->stock_quantity <= 0)>{{ __('Add to Cart') }}</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('favorites.toggle', $product) }}" class="mt-3" data-ajax-store-action>
                        @csrf
                        <button class="store-button-secondary w-full" aria-label="{{ __('Add :product to wishlist', ['product' => $product->name]) }}">{{ __('Add to Wishlist') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="grid gap-4" data-ajax-store-action aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}">
                        @csrf
                        @if($product->variants->isNotEmpty())
                            <label for="product-variant-guest" class="text-sm font-black">{{ __('Variant') }}</label>
                            <select id="product-variant-guest" name="variant_id" class="store-field">
                                <option value="">{{ __('Default') }}</option>
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}">{{ $variant->sku }} - {{ collect($variant->attributes)->map(fn($v, $k) => $k.': '.$v)->implode(', ') }}</option>
                                @endforeach
                            </select>
                        @endif
                        <div class="grid gap-3 sm:grid-cols-[120px_1fr]">
                            <input type="number" name="quantity" value="1" min="1" class="store-field" aria-describedby="product-stock-help">
                            <button class="store-button-primary" @disabled($product->stock_quantity <= 0)>{{ __('Add to Cart') }}</button>
                        </div>
                    </form>
                    <a href="{{ route('customer.login') }}" class="store-button-secondary mt-3 w-full">{{ __('Login for Wishlist') }}</a>
                @endauth
            </div>
        </div>
    </div>
</section>

<section class="store-section pt-0">
    <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
        <div class="store-panel p-6">
            <h2 class="text-2xl font-black">{{ __('Product Details') }}</h2>
            <div class="prose mt-5 max-w-none whitespace-pre-line text-slate-700">{{ $product->description }}</div>
        </div>
        <aside class="store-panel p-6" aria-labelledby="purchase-confidence-heading">
            <h2 id="purchase-confidence-heading" class="text-xl font-black">{{ __('Purchase Confidence') }}</h2>
            <div class="mt-5 grid gap-4 text-sm font-bold text-slate-600">
                <p>🚚 {{ __('Fast Delivery') }}</p>
                <p>🔒 {{ __('Secure Payment') }}</p>
                <p>↩ {{ __('Easy Returns') }}</p>
                <p>★ {{ __('Original Products') }}</p>
            </div>
        </aside>
    </div>
</section>

<section class="store-section pt-0">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <p class="store-eyebrow">{{ __('You may also like') }}</p>
            <h2 class="store-section-title">{{ __('Similar Products') }}</h2>
        </div>
    </div>
    <div class="store-product-grid">
        @forelse($similarProducts as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <p>{{ __('No products found.') }}</p>
        @endforelse
    </div>
</section>
@endsection
