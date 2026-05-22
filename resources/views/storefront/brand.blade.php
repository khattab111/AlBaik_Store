@extends('storefront.layout')

@section('title', $brand->name)

@section('content')
    <h1 class="mb-2 text-2xl font-bold">{{ $brand->name }}</h1>
    <p class="mb-6 text-gray-600">{{ $brand->description }}</p>
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($products as $product)
            @include('storefront.partials.product-card', ['product' => $product])
        @endforeach
    </section>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
