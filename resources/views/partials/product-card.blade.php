@php
    $image = $product->images->first()?->path;
    $rating = round((float) $product->reviews->avg('rating'), 1);
@endphp
<article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <a href="{{ route('products.show', $product->slug) }}" class="block aspect-[4/3] bg-slate-100">
        @if ($image)
            <img src="{{ asset('storage/'.$image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center text-slate-400">{{ __('No Image') }}</div>
        @endif
    </a>
    <div class="grid gap-3 p-4">
        <div>
            <p class="text-xs text-slate-500">{{ $product->brand?->name }}</p>
            <h3 class="line-clamp-2 font-semibold"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        </div>
        <div class="flex items-center justify-between">
            <span class="font-bold text-red-700">{{ number_format((float) $product->retail_price, 2) }} USD</span>
            <span class="text-xs text-amber-600">★ {{ $rating ?: '-' }}</span>
        </div>
        <p class="text-xs {{ $product->stock_quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $product->stock_quantity > 0 ? __('In Stock') : __('Out of Stock') }}</p>
        <div class="flex gap-2">
            <a href="{{ route('products.show', $product->slug) }}" class="flex-1 rounded-lg border px-3 py-2 text-center text-sm">{{ __('Details') }}</a>
            @auth
                <form method="POST" action="{{ route('cart.add', $product) }}">@csrf<input type="hidden" name="quantity" value="1"><button class="rounded-lg bg-slate-950 px-3 py-2 text-sm text-white dark:bg-white dark:text-slate-950">{{ __('Add') }}</button></form>
                <form method="POST" action="{{ route('favorites.toggle', $product) }}">@csrf<button class="rounded-lg border px-3 py-2 text-sm">♡</button></form>
            @endauth
        </div>
    </div>
</article>
