@extends('storefront.layout')

@section('title', __('Home'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Home') }}</h1>

    @if ($banners->isNotEmpty())
        <section class="mb-8 grid gap-4 md:grid-cols-2">
            @foreach ($banners as $banner)
                <a href="{{ $banner->url ?: route('shop.index') }}" class="rounded border bg-white p-4">
                    <h2 class="text-xl font-semibold">{{ $banner->title }}</h2>
                    <p class="text-gray-600">{{ $banner->subtitle }}</p>
                </a>
            @endforeach
        </section>
    @endif

    <h2 class="mb-4 text-xl font-semibold">{{ __('Featured Products') }}</h2>
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($featuredProducts as $product)
            @include('storefront.partials.product-card', ['product' => $product])
        @endforeach
    </section>
@endsection
