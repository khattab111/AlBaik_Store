@php
    $image = $product->images->first()?->path;
    $imageUrl = $image && file_exists(public_path('storage/'.$image))
        ? asset('storage/'.$image)
        : asset('images/storefront/product-fallback.svg');
    $rating = round((float) $product->reviews->avg('rating'), 1);
    $reviewsCount = $product->reviews->count();
    $displayMode = $displayMode ?? 'grid';
    $isList = $displayMode === 'list';
    $pricing = app(\App\Services\ProductPricingService::class)->getPriceForUser($product->loadMissing('priceTiers'), auth()->user(), 1);
    $price = (float) $pricing->price;
    $compareAt = $pricing->originalPrice && $pricing->originalPrice > $pricing->price ? (float) $pricing->originalPrice : null;
    $flashOffer = $pricing->flashOffer;

    $isHighlighted = $compareAt || (bool) ($product->is_featured ?? false);
@endphp

<article class="group overflow-hidden rounded-2xl border bg-white shadow-sm transition duration-300 hover:-translate-y-2 hover:border-amber-200 hover:shadow-2xl hover:shadow-slate-950/10 {{ $isHighlighted ? 'border-amber-200 shadow-slate-950/5' : 'border-slate-200' }} {{ $isList ? 'grid gap-0 sm:grid-cols-[220px_1fr]' : '' }}">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block overflow-hidden bg-gradient-to-br from-slate-50 via-white to-slate-100 {{ $isList ? 'min-h-56 sm:min-h-full' : 'aspect-[5/4]' }}">
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-5 transition duration-500 group-hover:scale-105">
        <div class="absolute start-4 top-4 flex flex-wrap gap-2">
            <span class="rounded-full border px-3 py-1 text-xs font-black shadow-sm {{ $product->stock_quantity > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                {{ $product->stock_quantity > 0 ? __('In Stock') : __('Out of Stock') }}
            </span>
            @if ($flashOffer)
                <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-black text-slate-950 shadow-sm">{{ __('Flash Offer') }}</span>
            @elseif ($compareAt)
                <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-black text-slate-950 shadow-sm">{{ __('Sale') }}</span>
            @endif
        </div>
        <span class="sr-only">
            {{ $product->stock_quantity > 0 ? __('In Stock') : __('Out of Stock') }}
        </span>
    </a>

    <div class="grid gap-4 p-5 {{ $isList ? 'sm:p-6' : '' }}">
        <div>
            <p class="text-xs font-black uppercase tracking-normal text-amber-600">{{ $product->brand?->name ?: 'ALBAIK' }}</p>
            <h3 class="mt-1 text-base font-black leading-6 text-slate-950 {{ $isList ? 'sm:text-xl' : 'min-h-12' }}">
                <a href="{{ route('products.show', $product->slug) }}" class="line-clamp-2">{{ $product->name }}</a>
            </h3>
            @if ($isList && $product->short_description)
                <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $product->short_description }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 text-sm">
            <span class="text-amber-500" aria-hidden="true">★★★★★</span>
            <span class="font-bold text-slate-600">{{ $rating ?: '0.0' }}</span>
            <span class="text-slate-400">({{ $reviewsCount }})</span>
            <span class="sr-only">{{ __('Rating: :rating out of 5 from :count reviews', ['rating' => $rating ?: '0.0', 'count' => $reviewsCount]) }}</span>
        </div>

        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="store-price">{{ store_money($price) }}</p>
                @if ($compareAt)
                    <p class="text-sm font-bold text-slate-400 line-through">{{ store_money($compareAt) }}</p>
                @endif
            </div>
            <a href="{{ route('products.show', $product->slug) }}" class="text-sm font-black text-slate-500 hover:text-amber-700" aria-label="{{ __('View details for :product', ['product' => $product->name]) }}">{{ __('Details') }}</a>
        </div>

        <div class="grid grid-cols-[1fr_auto] gap-2 {{ $isList ? 'sm:max-w-md' : '' }}">
            @auth
                <form method="POST" action="{{ route('cart.add', $product) }}" data-ajax-store-action>
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="store-button-primary w-full gap-2" aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}" @disabled($product->stock_quantity <= 0)>
                        <span aria-hidden="true">🛒</span>
                        {{ __('Add to Cart') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('favorites.toggle', $product) }}" data-ajax-store-action>
                    @csrf
                    <button class="store-button-secondary h-full px-4" aria-label="{{ __('Add :product to wishlist', ['product' => $product->name]) }}">♡</button>
                </form>
            @else
                <form method="POST" action="{{ route('cart.add', $product) }}" data-ajax-store-action>
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="store-button-primary w-full gap-2" aria-label="{{ __('Add :product to cart', ['product' => $product->name]) }}" @disabled($product->stock_quantity <= 0)>
                        <span aria-hidden="true">🛒</span>
                        {{ __('Add to Cart') }}
                    </button>
                </form>
                <a href="{{ route('customer.login') }}" class="store-button-secondary px-4" aria-label="{{ __('Login to add :product to wishlist', ['product' => $product->name]) }}">♡</a>
            @endauth
        </div>
    </div>
</article>
