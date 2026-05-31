@extends('layouts.app')

@section('title', $details['title'])
@section('meta_description', $details['description'] ?: $details['summary'])

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('offers.index') }}" class="transition hover:text-red-700">{{ __('Offers') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $details['title'] }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <div class="store-panel overflow-hidden p-0">
            <div class="bg-slate-950 p-8 text-white">
                <p class="text-sm font-black uppercase text-amber-300">{{ $details['badge'] }}</p>
                <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ $details['title'] }}</h1>
                <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-200">{{ $details['description'] ?: $details['summary'] }}</p>
            </div>

            <div class="p-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase text-slate-500">{{ __('Original price') }}</p>
                        <p class="mt-1 text-2xl font-black text-slate-950">USD {{ number_format((float) $details['original_price'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-4">
                        <p class="text-xs font-black uppercase text-amber-700">{{ __('Offer price') }}</p>
                        <p class="mt-1 text-2xl font-black text-amber-700">USD {{ number_format((float) $details['offer_price'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-4">
                        <p class="text-xs font-black uppercase text-emerald-700">{{ __('You save') }}</p>
                        <p class="mt-1 text-2xl font-black text-emerald-700">USD {{ number_format((float) $details['saving'], 2) }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-2 text-sm font-bold text-slate-700">
                    @foreach($details['details'] as $detail)
                        <p class="rounded-2xl bg-slate-50 px-4 py-3">{{ $detail }}</p>
                    @endforeach
                </div>

                <h2 class="mt-8 text-2xl font-black">{{ __('Products included in this offer') }}</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach($details['items'] as $item)
                        @php
                            $product = $item['product'];
                            $image = $product?->images?->first()?->path;
                            $imageUrl = $image && file_exists(public_path('storage/'.$image))
                                ? asset('storage/'.$image)
                                : asset('images/storefront/product-fallback.svg');
                        @endphp
                        <article class="grid grid-cols-[96px_1fr] gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                            <img src="{{ $imageUrl }}" class="h-24 w-24 rounded-2xl object-contain" alt="{{ $item['name'] }}">
                            <div>
                                <h3 class="font-black">{{ $item['name'] }}</h3>
                                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('Quantity') }}: {{ $item['quantity'] }}</p>
                                <p class="mt-2 text-sm font-black text-slate-700">
                                    USD {{ number_format((float) ($item['offer_price'] ?? $item['original_price']), 2) }}
                                    @if($item['original_price'] && ($item['offer_price'] ?? null) && $item['offer_price'] < $item['original_price'])
                                        <span class="ms-2 text-slate-400 line-through">USD {{ number_format((float) $item['original_price'], 2) }}</span>
                                    @endif
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="store-panel h-fit p-6 lg:sticky lg:top-44">
            <p class="store-eyebrow">{{ __('Limited time offer') }}</p>
            <h2 class="mt-2 text-2xl font-black">{{ $details['summary'] }}</h2>
            @if($details['remaining_quantity'] !== null)
                <p class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-700">
                    {{ __('Remaining') }}: {{ $details['remaining_quantity'] }}
                </p>
            @endif
            @if($details['ends_at'])
                <p class="mt-3 rounded-2xl bg-amber-50 px-4 py-3 text-sm font-black text-amber-700">
                    {{ __('Ends at') }}: {{ $details['ends_at']->format('Y-m-d H:i') }}
                </p>
            @endif

            <form method="POST" action="{{ route('offers.cart.add', $offer->slug) }}" class="mt-6 grid gap-3" data-ajax-store-action>
                @csrf
                <label for="offer-quantity" class="text-sm font-black">{{ __('Offer quantity') }}</label>
                <input id="offer-quantity" name="quantity" type="number" value="1" min="1" max="{{ $details['remaining_quantity'] ?: 50 }}" class="store-field">
                <button class="store-button-primary w-full">{{ __('Add offer to cart') }}</button>
            </form>
        </aside>
    </div>
</section>
@endsection
