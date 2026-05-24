@extends('layouts.app')

@section('title', __('Categories'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Categories') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Shop by department') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Categories') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Move quickly between product families with visual category cards.') }}</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($categories as $category)
            @php
                $categoryImage = match ($category->slug) {
                    'food', 'sandwiches', 'sauces' => asset('images/storefront/category-food.svg'),
                    'electronics', 'drinkware' => asset('images/storefront/category-electronics.svg'),
                    'bulk-supplies', 'bulk' => asset('images/storefront/category-bulk.svg'),
                    default => asset('images/storefront/category-default.svg'),
                };
            @endphp
            <a href="{{ route('categories.show', $category->slug) }}" class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-2 hover:shadow-xl">
                <img src="{{ $categoryImage }}" class="aspect-[7/4] w-full object-cover" alt="{{ $category->name }}">
                <div class="p-5">
                    <h2 class="text-lg font-black">{{ $category->name }}</h2>
                    <p class="mt-2 text-sm font-bold text-slate-500">{{ $category->products_count }} {{ __('Products') }}</p>
                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $category->description }}</p>
                </div>
            </a>
        @empty
            <div class="store-panel p-8 text-center">{{ __('No categories found.') }}</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $categories->links() }}</div>
</section>
@endsection
