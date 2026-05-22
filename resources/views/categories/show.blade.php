@extends('layouts.app')
@section('title', $category->name)
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10"><h1 class="text-3xl font-bold">{{ $category->name }}</h1><p class="mt-2 mb-8 text-slate-600 dark:text-slate-300">{{ $category->description }}</p><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($products as $product) @include('partials.product-card',['product'=>$product]) @empty <p>{{ __('No products found.') }}</p> @endforelse</div><div class="mt-8">{{ $products->links() }}</div></section>
@endsection
