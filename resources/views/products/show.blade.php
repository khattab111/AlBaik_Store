@extends('layouts.app')
@section('title', $product->name)
@section('content')
<section class="mx-auto grid max-w-7xl gap-10 px-4 py-10 md:grid-cols-2">
    <div class="grid gap-4">@forelse($product->images as $image)<img src="{{ asset('storage/'.$image->path) }}" class="rounded-xl border bg-white" alt="{{ $image->alt_text ?: $product->name }}">@empty<div class="flex aspect-square items-center justify-center rounded-xl bg-slate-100 text-slate-500">{{ __('No Image') }}</div>@endforelse</div>
    <div>
        <p class="text-sm text-slate-500">{{ $product->brand?->name }} / {{ $product->category?->name }}</p>
        <h1 class="mt-2 text-3xl font-black">{{ $product->name }}</h1>
        <p class="mt-4 text-2xl font-bold text-red-700">{{ number_format((float)$product->retail_price,2) }} USD</p>
        <p class="mt-4 text-slate-600 dark:text-slate-300">{{ $product->short_description }}</p>
        <div class="prose mt-5 dark:prose-invert">{!! $product->description !!}</div>
        <p class="mt-4 text-sm">{{ __('Available Quantity') }}: {{ $product->stock_quantity }}</p>
        @auth
        <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6 grid gap-3 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">@csrf
            @if($product->variants->isNotEmpty())<select name="variant_id" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="">{{ __('Default') }}</option>@foreach($product->variants as $variant)<option value="{{ $variant->id }}">{{ $variant->sku }} - {{ collect($variant->attributes)->map(fn($v,$k)=>$k.': '.$v)->implode(', ') }}</option>@endforeach</select>@endif
            <input type="number" name="quantity" value="1" min="1" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><button class="rounded-lg bg-red-700 px-5 py-3 text-white">{{ __('Add to Cart') }}</button>
        </form>
        <form method="POST" action="{{ route('favorites.toggle', $product) }}" class="mt-3">@csrf<button class="rounded-lg border bg-white px-5 py-3 dark:border-slate-800 dark:bg-slate-900">{{ __('Add to Wishlist') }}</button></form>
        @endauth
    </div>
</section>
<section class="mx-auto max-w-7xl px-4 py-8"><h2 class="mb-5 text-2xl font-bold">{{ __('Similar Products') }}</h2><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($similarProducts as $product) @include('partials.product-card', ['product'=>$product]) @empty <p>{{ __('No products found.') }}</p> @endforelse</div></section>
@endsection
