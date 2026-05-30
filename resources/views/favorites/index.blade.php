@extends('layouts.app')

@section('title', __('Wishlist'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Wishlist') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Saved products') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Wishlist') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Keep your preferred products close and move them to cart when you are ready.') }}</p>
    </div>
    <div class="store-product-grid">
        @forelse($items as $item)
            <div class="grid gap-3" data-favorite-item>
                @include('partials.product-card', ['product' => $item->product])
                <form method="POST" action="{{ route('favorites.toggle', $item->product) }}" data-ajax-store-action>
                    @csrf
                    <button class="store-button-secondary w-full">{{ __('Remove') }}</button>
                </form>
            </div>
        @empty
            <div class="store-panel col-span-full p-12 text-center">
                <h2 class="text-2xl font-black">{{ __('No favorite products.') }}</h2>
                <a href="{{ route('products.index') }}" class="store-button-primary mt-6">{{ __('Browse Products') }}</a>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $items->links() }}</div>
</section>
@endsection
