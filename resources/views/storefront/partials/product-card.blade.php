<article class="rounded border bg-white p-4">
    @if ($product->images->first())
        <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}" class="mb-3 h-40 w-full object-cover">
    @endif
    <h3 class="font-semibold">
        <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
    </h3>
    <p class="text-sm text-gray-600">{{ $product->brand?->name }}</p>
    <p class="mt-2 font-bold">{{ number_format((float) $product->retail_price, 2) }} USD</p>
    @auth
        <form method="POST" action="{{ route('cart.items.store') }}" class="mt-3 flex gap-2">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="number" name="quantity" value="1" min="1" class="w-20 rounded border px-2 py-1">
            <button class="rounded bg-gray-950 px-3 py-1 text-white">{{ __('Add') }}</button>
        </form>
    @endauth
</article>
