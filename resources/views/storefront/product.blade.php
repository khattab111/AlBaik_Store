@extends('storefront.layout')

@section('title', $product->name)

@section('content')
    <article class="grid gap-8 md:grid-cols-2">
        <div>
            @foreach ($product->images as $image)
                <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text ?: $product->name }}" class="mb-3 rounded border bg-white">
            @endforeach
        </div>
        <div>
            <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
            <p class="mt-2 text-gray-600">{{ $product->brand?->name }} / {{ $product->category?->name }}</p>
            <p class="mt-4 text-2xl font-bold">{{ number_format((float) $product->retail_price, 2) }} USD</p>
            <p class="mt-4">{{ $product->short_description }}</p>
            <div class="prose mt-4">{!! $product->description !!}</div>

            @auth
                <form method="POST" action="{{ route('cart.items.store') }}" class="mt-6 grid gap-3 rounded border bg-white p-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($product->variants->isNotEmpty())
                        <select name="variant_id" class="rounded border px-3 py-2">
                            <option value="">{{ __('Default') }}</option>
                            @foreach ($product->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->sku }} - {{ collect($variant->attributes)->map(fn ($value, $key) => $key.': '.$value)->implode(', ') }}</option>
                            @endforeach
                        </select>
                    @endif
                    <input type="number" name="quantity" value="1" min="1" class="rounded border px-3 py-2">
                    <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Add to Cart') }}</button>
                </form>
                <form method="POST" action="{{ route('wishlist.store', $product) }}" class="mt-3">
                    @csrf
                    <button class="rounded border bg-white px-4 py-2">{{ __('Add to Wishlist') }}</button>
                </form>
            @endauth
        </div>
    </article>
@endsection
