@extends('layouts.app')
@section('title', __('Offers'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="mb-6 text-3xl font-bold">{{ __('Offers') }}</h1>
    <form method="GET" class="mb-8 grid gap-3 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900 md:grid-cols-4">
        <select name="category" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="">{{ __('All Categories') }}</option>@foreach($categories as $category)<option value="{{ $category->slug }}">{{ $category->name }}</option>@endforeach</select>
        <select name="brand" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="">{{ __('All Brands') }}</option>@foreach($brands as $brand)<option value="{{ $brand->slug }}">{{ $brand->name }}</option>@endforeach</select>
        <select name="sort" class="rounded-lg border px-3 py-2 dark:bg-slate-950"><option value="latest">{{ __('Latest') }}</option><option value="price_asc">{{ __('Lowest Price') }}</option><option value="price_desc">{{ __('Highest Price') }}</option></select>
        <button class="rounded-lg bg-red-700 px-4 py-2 text-white">{{ __('Filter') }}</button>
    </form>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($products as $product) @include('partials.product-card', ['product'=>$product]) @empty <p>{{ __('No active offers.') }}</p> @endforelse</div>
    <div class="mt-8">{{ $products->links() }}</div>
</section>
@endsection
