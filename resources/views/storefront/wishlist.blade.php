@extends('storefront.layout')

@section('title', __('Wishlist'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Wishlist') }}</h1>
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($items as $item)
            <div>
                @include('storefront.partials.product-card', ['product' => $item->product])
                <form method="POST" action="{{ route('wishlist.destroy', $item->product) }}" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button class="rounded border bg-white px-3 py-2">{{ __('Remove') }}</button>
                </form>
            </div>
        @endforeach
    </section>
    <div class="mt-6">{{ $items->links() }}</div>
@endsection
