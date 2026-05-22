@extends('storefront.layout')

@section('title', __('Shop'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Shop') }}</h1>
    <form method="GET" class="mb-6 grid gap-3 rounded border bg-white p-4 md:grid-cols-5">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search') }}" class="rounded border px-3 py-2">
        <select name="category_id" class="rounded border px-3 py-2">
            <option value="">{{ __('All Categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="brand_id" class="rounded border px-3 py-2">
            <option value="">{{ __('All Brands') }}</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected(($filters['brand_id'] ?? null) == $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <select name="sort" class="rounded border px-3 py-2">
            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Latest') }}</option>
            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Price Low to High') }}</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Price High to Low') }}</option>
            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>{{ __('Name') }}</option>
        </select>
        <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Filter') }}</button>
    </form>
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($products as $product)
            @include('storefront.partials.product-card', ['product' => $product])
        @endforeach
    </section>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
