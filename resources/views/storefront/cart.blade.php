@extends('storefront.layout')

@section('title', __('Cart'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Cart') }}</h1>
    <section class="grid gap-4">
        @forelse ($items as $item)
            @php($isOffer = ($item->item_type ?? 'product') === 'offer')
            <div class="flex items-center justify-between rounded border bg-white p-4">
                <div>
                    <h2 class="font-semibold">{{ $isOffer ? $item->title : $item->product->name }}</h2>
                    @if($isOffer)
                        <div class="mt-2 text-xs text-gray-600">
                            @foreach(collect($item->components_snapshot ?? [])->take(4) as $component)
                                <p>{{ $component['product_name'] ?? __('Product') }} x {{ $component['quantity'] ?? 1 }}</p>
                            @endforeach
                        </div>
                    @endif
                    <p class="text-sm text-gray-600">{{ number_format((float) $item->unit_price, 2) }} USD</p>
                </div>
                <form method="POST" action="{{ route('cart.items.update', $item) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="w-24 rounded border px-3 py-2">
                    <button class="rounded border px-3 py-2">{{ __('Update') }}</button>
                </form>
                <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                    @csrf
                    @method('DELETE')
                    <button class="rounded border px-3 py-2">{{ __('Delete') }}</button>
                </form>
            </div>
        @empty
            <p>{{ __('Cart is empty.') }}</p>
        @endforelse
    </section>
    <div class="mt-6 rounded border bg-white p-4">
        <p class="font-bold">{{ __('Subtotal') }}: {{ number_format($subtotal, 2) }} USD</p>
        <a href="{{ route('checkout.index') }}" class="mt-3 inline-block rounded bg-gray-950 px-4 py-2 text-white">{{ __('Checkout') }}</a>
    </div>
@endsection
