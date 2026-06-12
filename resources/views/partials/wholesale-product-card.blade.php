@php
    $minimumQuantity = max(1, (int) ($product->wholesale_minimum_quantity ?: 1));
    $image = $product->images->first()?->path;
    $imageUrl = $image && file_exists(public_path('storage/'.$image))
        ? asset('storage/'.$image)
        : asset('images/storefront/product-fallback.svg');
    $rating = round((float) $product->reviews->avg('rating'), 1);
    $reviewsCount = $product->reviews->count();
    $displayMode = $displayMode ?? 'grid';
    $isList = $displayMode === 'list';
    $pricing = app(\App\Services\ProductPricingService::class)->getPriceForUser($product->loadMissing('priceTiers'), auth()->user(), $minimumQuantity);
    $price = (float) $pricing->price;
    $retailPrice = (float) $product->retail_price;
    $saving = $retailPrice > $price ? $retailPrice - $price : 0;
@endphp

<article class="premium-product-card {{ $isList ? 'is-list' : '' }}">
    <div class="premium-product-media">
        <a href="{{ route('products.show', $product->slug) }}" aria-label="{{ $product->name }}">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
        </a>

        <div class="premium-product-badges">
            <span>{{ __('Wholesale') }}</span>
        </div>
    </div>

    <div class="premium-product-body">
        <p class="premium-product-brand">{{ $product->brand?->name ?: 'ALBAIK' }}</p>
        <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <p class="premium-product-subtitle">{{ __('Minimum quantity: :quantity', ['quantity' => $minimumQuantity]) }}</p>

        <div class="premium-product-rating">
            <span aria-hidden="true">★</span>
            <strong>{{ $rating ?: '0.0' }}</strong>
            <small>({{ $reviewsCount }})</small>
        </div>

        <div class="premium-product-price">
            <strong>{{ store_money($price) }}</strong>
            @if($saving > 0)
                <del>{{ store_money($retailPrice) }}</del>
                <small>{{ __('You save :amount', ['amount' => store_money($saving)]) }}</small>
            @endif
        </div>
    </div>

    <div class="premium-product-actions">
        <form method="POST" action="{{ route('cart.add', $product) }}" data-ajax-store-action>
            @csrf
            <input type="hidden" name="quantity" value="{{ $minimumQuantity }}">
            <button class="premium-cart-button" aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}" @disabled($product->stock_quantity < $minimumQuantity)>
                {{ __('Add wholesale pack') }}
                <span aria-hidden="true">🛒</span>
            </button>
        </form>
    </div>
</article>
