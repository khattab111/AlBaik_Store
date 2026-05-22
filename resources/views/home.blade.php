@extends('layouts.app')

@section('title', __('Home'))

@section('content')
    <section class="bg-gradient-to-b from-red-50 to-white dark:from-slate-900 dark:to-slate-950">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 md:grid-cols-2">
            <div>
                <h1 class="text-4xl font-black leading-tight md:text-5xl">متجر البيك للمنتجات الأصلية والعروض اليومية</h1>
                <p class="mt-5 text-lg text-slate-600 dark:text-slate-300">تسوق منتجات التجزئة والجملة بسهولة، مع شحن مرن ودفع يدوي آمن ومراجعة سريعة للطلبات.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="rounded-full bg-red-700 px-6 py-3 font-semibold text-white">{{ __('Browse Products') }}</a>
                    <a href="{{ route('offers.index') }}" class="rounded-full border border-slate-300 px-6 py-3 font-semibold">{{ __('View Offers') }}</a>
                </div>
            </div>
            <div class="rounded-2xl border bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($banners->first()?->image)
                    <img src="{{ asset('storage/'.$banners->first()->image) }}" class="aspect-video w-full rounded-xl object-cover" alt="Hero">
                @else
                    <div class="flex aspect-video items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800">AlBaik Store</div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="grid gap-6 md:grid-cols-4">
            @foreach ([__('Original Products'), __('Offers & Discounts'), __('Fast Delivery'), __('Technical Support')] as $service)
                <div class="rounded-xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h3 class="font-bold">{{ $service }}</h3><p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Reliable shopping experience for retail and wholesale customers.') }}</p></div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between"><h2 class="text-2xl font-bold">{{ __('Brands') }}</h2><a href="{{ route('brands.index') }}" class="text-sm text-red-700">{{ __('View All') }}</a></div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($brands as $brand)
                <a href="{{ route('brands.show', $brand->slug) }}" class="rounded-xl border bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mx-auto mb-3 flex h-20 items-center justify-center">@if($brand->logo)<img src="{{ asset('storage/'.$brand->logo) }}" class="max-h-20" alt="{{ $brand->name }}">@else <span class="text-3xl font-black text-red-700">{{ mb_substr($brand->name,0,1) }}</span>@endif</div>
                    <h3 class="font-semibold">{{ $brand->name }}</h3><p class="text-sm text-slate-500">{{ $brand->products_count }} {{ __('Products') }}</p>
                </a>
            @empty
                <p class="text-slate-500">{{ __('No brands found.') }}</p>
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between"><h2 class="text-2xl font-bold">{{ __('Featured Products') }}</h2><a href="{{ route('products.index') }}" class="text-sm text-red-700">{{ __('View All') }}</a></div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($featuredProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p class="text-slate-500">{{ __('No products found.') }}</p>
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between"><h2 class="text-2xl font-bold">{{ __('Categories') }}</h2><a href="{{ route('categories.index') }}" class="text-sm text-red-700">{{ __('View All') }}</a></div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="rounded-xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h3 class="font-bold">{{ $category->name }}</h3><p class="mt-2 text-sm text-slate-500">{{ $category->products_count }} {{ __('Products') }}</p></a>
            @empty
                <p class="text-slate-500">{{ __('No categories found.') }}</p>
            @endforelse
        </div>
    </section>
@endsection
