@extends('layouts.app')

@section('title', __('Offers'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Offers') }}</span>
    </nav>

    <div class="mb-8 overflow-hidden rounded-[2rem] bg-slate-950 p-8 text-white shadow-xl shadow-slate-950/10">
        <p class="text-sm font-black text-amber-300">{{ __('Limited time') }}</p>
        <h1 class="mt-2 text-4xl font-black">{{ __('Offers') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-300">{{ __('Browse active discounts, wholesale picks, and launch deals from the storefront catalog.') }}</p>
    </div>

    <form method="GET" class="store-panel mb-8 grid gap-3 p-5 md:grid-cols-4">
        <select name="category" class="store-field">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="brand" class="store-field">
            <option value="">{{ __('All Brands') }}</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <select name="sort" class="store-field">
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Latest') }}</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Lowest Price') }}</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Highest Price') }}</option>
        </select>
        <button class="store-button-primary gap-2"><span aria-hidden="true">≡</span>{{ __('Filter') }}</button>
    </form>

    <div class="store-product-grid">
        @forelse($products as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <div class="store-panel col-span-full p-10 text-center">{{ __('No active offers.') }}</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>

    <div class="store-trust-strip">
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🚚</span><div><p class="font-black">{{ __('Fast shipping') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Fast delivery for all orders.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">🔒</span><div><p class="font-black">{{ __('Secure checkout') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Safe and flexible payment methods.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">✓</span><div><p class="font-black">{{ __('Original guarantee') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Verified catalog products.') }}</p></div></div>
        <div class="flex items-center gap-3"><span class="text-2xl text-red-700" aria-hidden="true">☎</span><div><p class="font-black">{{ __('Customer support') }}</p><p class="text-xs font-bold text-slate-500">{{ __('Support paths remain visible after purchase.') }}</p></div></div>
    </div>
</section>
@endsection
