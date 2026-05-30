@extends('layouts.app')

@section('title', __('Brands'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Brands') }}</span>
    </nav>

    <div class="store-page-hero grid items-end gap-6 lg:grid-cols-[1fr_auto]">
        <div>
            <p class="store-eyebrow">{{ __('Trusted partners') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Brands') }}</h1>
            <p class="mt-3 max-w-2xl text-slate-600">{{ __('A cleaner logo wall for browsing brands and supplier collections.') }}</p>
        </div>
        <form method="GET" class="flex w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-1">
            <label for="brand-search" class="sr-only">{{ __('Search brands') }}</label>
            <input id="brand-search" name="search" value="{{ $filters['search'] ?? '' }}" class="min-w-0 flex-1 bg-transparent px-4 py-3 text-sm outline-none" placeholder="{{ __('Search brands') }}">
            <button class="rounded-xl bg-red-700 px-5 py-3 text-sm font-black text-white">{{ __('Search') }}</button>
        </form>
    </div>

    @include('partials.banner-strip', ['banners' => $pageBanners ?? collect()])

    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($brands as $brand)
            @php
                $logoUrl = $brand->logo && file_exists(public_path('storage/'.$brand->logo)) ? asset('storage/'.$brand->logo) : null;
            @endphp
            <a href="{{ route('brands.show', $brand->slug) }}" class="group store-panel flex min-h-56 items-center justify-center overflow-hidden p-6 text-center transition hover:-translate-y-2 hover:shadow-xl">
                <div class="w-full">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="mx-auto h-20 max-w-40 object-contain" alt="{{ $brand->name }}">
                    @else
                        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-red-50 text-3xl font-black text-red-700 transition group-hover:bg-red-700 group-hover:text-white">{{ mb_substr($brand->name, 0, 1) }}</span>
                    @endif
                    <h2 class="mt-5 text-lg font-black">{{ $brand->name }}</h2>
                    <p class="mt-1 text-sm font-bold text-slate-500">{{ $brand->products_count }} {{ __('Products') }}</p>
                    <span class="mt-5 inline-flex rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 transition group-hover:bg-red-50 group-hover:text-red-700">{{ __('View brand') }}</span>
                </div>
            </a>
        @empty
            <div class="store-panel col-span-full p-10 text-center">
                <h2 class="text-xl font-black">{{ __('No brands found.') }}</h2>
                <p class="mt-2 text-slate-500">{{ __('Try changing filters or search terms.') }}</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $brands->links() }}</div>
</section>
@endsection
