@extends('layouts.app')

@section('title', $brand->name)
@section('meta_description', $brand->description ?: __('Browse products from this trusted brand.'))
@section('canonical', route('brands.show', $brand->slug))

@section('content')
<section class="store-section">
    @php
        $logoUrl = $brand->logo && file_exists(public_path('storage/'.$brand->logo)) ? asset('storage/'.$brand->logo) : null;
    @endphp

    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('brands.index') }}" class="transition hover:text-red-700">{{ __('Brands') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $brand->name }}</span>
    </nav>

    <div class="grid items-center gap-8 overflow-hidden rounded-[2rem] bg-slate-950 p-8 text-white shadow-2xl shadow-slate-950/10 md:grid-cols-[auto_1fr_auto]">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="h-28 w-28 rounded-3xl bg-white object-contain p-3" alt="{{ $brand->name }}">
        @else
            <span class="flex h-28 w-28 items-center justify-center rounded-3xl bg-white text-5xl font-black text-red-700">{{ mb_substr($brand->name, 0, 1) }}</span>
        @endif
        <div>
            <p class="text-sm font-black text-amber-300">{{ __('Brand') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ $brand->name }}</h1>
            <p class="mt-4 max-w-3xl leading-8 text-slate-300">{{ $brand->description ?: __('Explore this brand collection and its active products.') }}</p>
        </div>
        <div class="rounded-3xl bg-white/10 p-5 text-center">
            <p class="text-4xl font-black text-amber-300">{{ $brand->products_count }}</p>
            <p class="mt-1 text-sm font-bold text-slate-200">{{ __('Products') }}</p>
        </div>
    </div>

    @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])

    <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="store-eyebrow">{{ __('Brand products') }}</p>
            <h2 class="store-section-title">{{ __('Products') }}</h2>
        </div>
        <a href="{{ route('brands.index') }}" class="store-button-secondary">{{ __('All Brands') }}</a>
    </div>

    <div class="mt-6 store-product-grid">
        @forelse($products as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <div class="store-panel col-span-full p-10 text-center">{{ __('No products found.') }}</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
</section>
@endsection
