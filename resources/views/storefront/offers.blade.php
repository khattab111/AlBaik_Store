@extends('storefront.layout')

@section('title', __('Offers'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Offers') }}</h1>
    <section class="mb-8 grid gap-4">
        <h2 class="text-xl font-semibold">{{ __('Flash Offers') }}</h2>
        @forelse ($flashOffers as $offer)
            <div class="rounded border bg-white p-4">
                <h3 class="font-semibold">{{ $offer->title }}</h3>
                <p class="text-sm text-gray-600">{{ $offer->starts_at }} - {{ $offer->ends_at }}</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($offer->items->pluck('product')->filter() as $product)
                        @include('storefront.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        @empty
            <p>{{ __('No active flash offers.') }}</p>
        @endforelse
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        @foreach ($coupons as $coupon)
            <div class="rounded border bg-white p-4">
                <h3 class="font-semibold">{{ $coupon->code }}</h3>
                <p>{{ $coupon->type }}: {{ $coupon->value }}</p>
            </div>
        @endforeach
    </section>
@endsection
