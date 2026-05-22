@extends('layouts.app')
@section('title', __('Products'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="mb-6 text-3xl font-bold">{{ __('Products') }}</h1>
    <form method="GET" class="mb-8 grid gap-3 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900 md:grid-cols-6">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search') }}" class="rounded-lg border px-3 py-2 dark:bg-slate-950">
        <select name="category" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="">{{ __('All Categories') }}</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select>
        <select name="brand" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="">{{ __('All Brands') }}</option>@foreach($brands as $brand)<option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>@endforeach</select>
        <input name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="{{ __('Min Price') }}" class="rounded-lg border px-3 py-2 dark:bg-slate-950">
        <input name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="{{ __('Max Price') }}" class="rounded-lg border px-3 py-2 dark:bg-slate-950">
        <select name="sort" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="latest">{{ __('Latest') }}</option><option value="price_desc">{{ __('Highest Price') }}</option><option value="price_asc">{{ __('Lowest Price') }}</option><option value="best_selling">{{ __('Best Selling') }}</option><option value="top_rated">{{ __('Top Rated') }}</option></select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))> {{ __('In Stock') }}</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="on_sale" value="1" @checked(request()->boolean('on_sale'))> {{ __('Offers') }}</label>
        <button class="rounded-lg bg-red-700 px-4 py-2 text-white md:col-span-4">{{ __('Filter') }}</button>
    </form>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($products as $product) @include('partials.product-card', ['product' => $product]) @empty <p>{{ __('No products found.') }}</p> @endforelse</div>
    <div class="mt-8">{{ $products->links() }}</div>
</section>
@endsection
