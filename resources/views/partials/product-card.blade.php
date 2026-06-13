@php
    $image = $product->images->first()?->path;
    $imageUrl = $image && file_exists(public_path('storage/'.$image))
        ? asset('storage/'.$image)
        : asset('images/storefront/product-fallback.svg');
    $rating = round((float) ($product->average_rating ?? 0), 1);
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $displayMode = $displayMode ?? 'grid';
    $isList = $displayMode === 'list';
    $pricing = app(\App\Services\ProductPricingService::class)->getPriceForUser($product->loadMissing('priceTiers'), auth()->user(), 1);
    $price = (float) $pricing->price;
    $compareAt = $pricing->originalPrice && $pricing->originalPrice > $pricing->price ? (float) $pricing->originalPrice : null;
    $flashOffer = $pricing->flashOffer;
    $discountPercent = $compareAt ? max(1, round((($compareAt - $price) / max($compareAt, 1)) * 100)) : null;
@endphp

<article class="premium-product-card {{ $isList ? 'is-list' : '' }}">
    <div class="premium-product-media">
        <a href="{{ route('products.show', $product->slug) }}" aria-label="{{ $product->name }}">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
        </a>

        <div class="premium-product-badges">
            @if($discountPercent)
                <span class="is-sale">{{ __('Discount :percent%', ['percent' => $discountPercent]) }}</span>
            @elseif($flashOffer)
                <span class="is-sale">{{ __('Flash Offer') }}</span>
            @elseif($product->is_featured)
                <span>{{ __('New') }}</span>
            @endif
        </div>

        <div class="premium-product-quick">
            @auth
                <form method="POST" action="{{ route('favorites.toggle', $product) }}" data-ajax-store-action>
                    @csrf
                    <button aria-label="{{ __('Add :product to wishlist', ['product' => $product->name]) }}">♡</button>
                </form>
            @else
                <a href="{{ route('customer.login') }}" aria-label="{{ __('Login to add :product to wishlist', ['product' => $product->name]) }}">♡</a>
            @endauth
            <button type="button" aria-label="{{ __('Compare') }}">⇄</button>
            <a href="{{ route('products.show', $product->slug) }}" aria-label="{{ __('Quick View') }}">⌕</a>
        </div>
    </div>

    <div class="premium-product-body">
        <p class="premium-product-brand">{{ $product->brand?->name ?: 'ALBAIK' }}</p>
        <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <p class="premium-product-subtitle">{{ $product->short_description ?: $product->category?->name }}</p>

        <div class="premium-product-rating">
            <span aria-hidden="true">★</span>
            <strong>{{ $rating ?: '0.0' }}</strong>
            <small>({{ $reviewsCount }})</small>
        </div>

        <div class="premium-product-price">
            <strong>{{ store_money($price) }}</strong>
            @if($compareAt)
                <del>{{ store_money($compareAt) }}</del>
            @endif
        </div>
    </div>

    <div class="premium-product-actions">
        <form method="POST" action="{{ route('cart.add', $product) }}" data-ajax-store-action>
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button class="premium-cart-button" aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}" @disabled($product->stock_quantity <= 0)>
                {{ __('Add to Cart') }}
                <span aria-hidden="true">🛒</span>
            </button>
        </form>
    </div>
</article>
