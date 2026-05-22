@extends('storefront.layout')

@section('title', __('Brands'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Brands') }}</h1>
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($brands as $brand)
            <a href="{{ route('brands.show', $brand->slug) }}" class="rounded border bg-white p-4">
                @if ($brand->logo)
                    <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" class="mb-3 h-24 object-contain">
                @endif
                <h2 class="font-semibold">{{ $brand->name }}</h2>
                <p class="text-sm text-gray-600">{{ $brand->products_count }} {{ __('Products') }}</p>
            </a>
        @endforeach
    </section>
    <div class="mt-6">{{ $brands->links() }}</div>
@endsection
