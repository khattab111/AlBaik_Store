@extends('layouts.app')

@section('title', $category->name)
@section('meta_description', $category->description ?: __('Browse products in this category.'))
@section('canonical', route('categories.show', $category->slug))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('categories.index') }}" class="transition hover:text-red-700">{{ __('Categories') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $category->name }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Category') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ $category->name }}</h1>
        <p class="mt-3 max-w-3xl leading-7 text-slate-600">{{ $category->description }}</p>
    </div>
    @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])
    <div class="store-product-grid">
        @forelse($products as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <div class="store-panel col-span-full p-10 text-center">{{ __('No products found.') }}</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
</section>
@endsection
