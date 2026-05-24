@extends('layouts.app')

@section('title', __('Checkout'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('cart.index') }}" class="transition hover:text-red-700">{{ __('Cart') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Checkout') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Secure order') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Checkout') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Choose delivery, payment, and review your order summary in one place.') }}</p>
    </div>

    @if($items->isEmpty())
        <div class="store-panel p-12 text-center">{{ __('Cart is empty.') }}</div>
    @elseif($addresses->isEmpty())
        <div class="store-panel p-12 text-center">
            <h2 class="text-2xl font-black">{{ __('Please add an address before checkout.') }}</h2>
            <a href="{{ route('account.addresses.index') }}" class="store-button-primary mt-6">{{ __('Addresses') }}</a>
        </div>
    @else
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="grid gap-8 lg:grid-cols-[1fr_360px]">
            @csrf
            <div class="grid gap-6">
                <section class="store-panel p-6">
                    <h2 class="text-2xl font-black">{{ __('Shipping Address') }}</h2>
                    <div class="mt-5 grid gap-3">
                        @foreach($addresses as $address)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" class="sr-only" @checked($loop->first)>
                                <span class="block font-black">{{ $address->label ?: __('Address') }}</span>
                                <span class="mt-1 block text-sm text-slate-600">{{ $address->country }} / {{ $address->city }} / {{ $address->town }} / {{ $address->street }} - {{ $address->phone }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="store-panel p-6">
                    <h2 class="text-2xl font-black">{{ __('Shipping Method') }}</h2>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach($shippingMethods as $method)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="shipping_method_id" value="{{ $method->id }}" class="sr-only" @checked($loop->first)>
                                <span class="block font-black">{{ $method->name }}</span>
                                <span class="mt-1 block text-sm text-slate-600">{{ $method->description }}</span>
                                <span class="mt-3 block text-lg font-black text-red-700">USD {{ number_format((float)$method->cost, 2) }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="store-panel p-6">
                    <h2 class="text-2xl font-black">{{ __('Payment Method') }}</h2>
                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @foreach($paymentMethods as $method)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="payment_method_id" value="{{ $method->id }}" class="sr-only" @checked($loop->first)>
                                <span class="block font-black">{{ $method->name }}</span>
                                <span class="mt-1 block text-sm text-slate-600">{{ $method->description }}</span>
                                @if($method->wallet_url)
                                    <span class="mt-3 block rounded-xl bg-slate-50 p-3 text-sm font-bold text-slate-700">{{ __('Wallet Link') }}: {{ $method->wallet_url }}</span>
                                @endif
                                @if($method->barcode_image && file_exists(public_path('storage/'.$method->barcode_image)))
                                    <img src="{{ asset('storage/'.$method->barcode_image) }}" class="mt-3 h-28 object-contain" alt="{{ $method->name }}">
                                @endif
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="store-panel grid gap-4 p-6">
                    <h2 class="text-2xl font-black">{{ __('Order Notes') }}</h2>
                    <input type="file" name="payment_receipt" accept="image/*" class="store-field">
                    <input name="coupon_code" placeholder="{{ __('Coupon Code') }}" class="store-field">
                    <textarea name="notes" rows="4" placeholder="{{ __('Notes') }}" class="store-field"></textarea>
                </section>
            </div>

            <aside class="store-panel h-fit p-6 lg:sticky lg:top-44">
                <h2 class="text-2xl font-black">{{ __('Order Summary') }}</h2>
                <div class="mt-5 grid gap-4">
                    @foreach($items as $item)
                        <div class="flex justify-between gap-3 text-sm">
                            <span class="font-bold text-slate-600">{{ $item->product->name }} × {{ $item->quantity }}</span>
                            <span class="font-black">USD {{ number_format($item->quantity * (float)$item->unit_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 border-t border-slate-100 pt-5">
                    <div class="flex justify-between text-lg font-black">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="text-red-700">USD {{ number_format($subtotal, 2) }}</span>
                    </div>
                </div>
                <button class="store-button-primary mt-6 w-full">{{ __('Place Order') }}</button>
            </aside>
        </form>
    @endif
</section>
@endsection
