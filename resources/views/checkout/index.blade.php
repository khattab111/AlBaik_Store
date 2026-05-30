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
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="grid gap-8 lg:grid-cols-[1fr_360px]"
            data-checkout-shipping
            data-carriers-url="{{ route('checkout.shipping-carriers') }}"
            data-quote-url="{{ route('checkout.shipping-quote') }}">
            @csrf
            <div class="grid gap-6">
                <section class="store-panel p-6">
                    <h2 class="text-2xl font-black">{{ __('Shipping Address') }}</h2>
                    <div class="mt-5 grid gap-3">
                        @foreach($addresses as $address)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" data-address-city-id="{{ $address->city_id }}" class="sr-only" @checked($loop->first)>
                                <span class="block font-black">{{ $address->label ?: __('Address') }}</span>
                                <span class="mt-1 block text-sm text-slate-600">{{ $address->country }} / {{ $address->city }} / {{ $address->town }} / {{ $address->street }} - {{ $address->phone }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="store-panel p-6">
                    <h2 class="text-2xl font-black">{{ __('Shipping') }}</h2>
                    <div class="mt-5 grid gap-4">
                        <label class="grid gap-2">
                            <span class="text-sm font-black">{{ __('City') }}</span>
                            <select name="shipping_city_id" class="store-field" data-shipping-city @disabled(! $requiresShipping)>
                                <option value="">{{ __('Choose city') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }} - {{ $city->country }}</option>
                                @endforeach
                            </select>
                        </label>

                        @if(! $requiresShipping)
                            <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ __('This cart does not require shipping.') }}</div>
                        @endif

                        <div class="grid gap-3 sm:grid-cols-2" data-shipping-carriers>
                            @forelse($availableCarriers as $carrier)
                                <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                    <input type="radio" name="shipping_carrier_id" value="{{ $carrier['id'] }}" data-shipping-cost="{{ $carrier['cost'] }}" class="sr-only" @checked($loop->first) @disabled(! $requiresShipping)>
                                    <span class="block font-black">{{ $carrier['name'] }}</span>
                                    <span class="mt-1 block text-sm text-slate-600">{{ $carrier['estimated_delivery_time'] ?: __('Delivery time will be confirmed.') }}</span>
                                    <span class="mt-3 block text-lg font-black text-red-700">USD {{ number_format((float)$carrier['cost'], 2) }}</span>
                                </label>
                            @empty
                                <div class="rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800" data-no-carriers>{{ __('No shipping carriers are available for this city right now.') }}</div>
                            @endforelse
                        </div>
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
                    <input type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="store-field">
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
                        <span class="text-red-700">USD <span data-checkout-subtotal="{{ number_format($subtotal, 2, '.', '') }}">{{ number_format($subtotal, 2) }}</span></span>
                    </div>
                    <div class="mt-3 flex justify-between text-sm font-bold text-slate-600">
                        <span>{{ __('Shipping') }}</span>
                        <span>USD <span data-checkout-shipping-cost>0.00</span></span>
                    </div>
                    <div class="mt-3 flex justify-between text-xl font-black">
                        <span>{{ __('Total') }}</span>
                        <span class="text-red-700">USD <span data-checkout-total>{{ number_format($subtotal, 2) }}</span></span>
                    </div>
                </div>
                <button class="store-button-primary mt-6 w-full">{{ __('Place Order') }}</button>
            </aside>
        </form>
    @endif
</section>
@endsection
