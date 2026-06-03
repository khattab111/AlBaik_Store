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
    @else
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]"
            data-checkout-shipping
            data-carriers-url="{{ route('checkout.shipping-carriers') }}"
            data-quote-url="{{ route('checkout.shipping-quote') }}">
            @csrf
            <div class="grid gap-6">
                <section class="store-panel p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl font-black">{{ __('Shipping Address') }}</h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __('Step') }} 1</span>
                    </div>
                    <div class="mt-5 grid gap-3">
                        @foreach($addresses as $address)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="user_address_id" value="{{ $address->id }}" data-address-city-id="{{ $address->city_id }}" data-address-mode="saved" class="sr-only" @checked($loop->first)>
                                <span class="store-safe-text block font-black">{{ $address->label ?: __('Address') }}</span>
                                @if($address->is_default)
                                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">{{ __('Default') }}</span>
                                @endif
                                <span class="store-safe-text mt-1 block text-sm text-slate-600">{{ $address->recipient_name }} - {{ $address->phone }}</span>
                                <span class="store-safe-text mt-1 block text-sm text-slate-600">{{ $address->city?->name }} / {{ $address->address_line }}</span>
                            </label>
                        @endforeach
                        <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                            <input type="radio" name="user_address_id" value="" data-address-mode="new" class="sr-only" @checked($addresses->isEmpty())>
                            <span class="block font-black">{{ __('Add New Address') }}</span>
                            <span class="mt-1 block text-sm text-slate-600">{{ __('Use it once or save it to your address book.') }}</span>
                        </label>
                        <input type="hidden" name="address_mode" value="{{ $addresses->isEmpty() ? 'new' : 'saved' }}" data-address-mode-input>
                    </div>

                    <div class="{{ $addresses->isEmpty() ? '' : 'hidden' }} mt-5 grid gap-3 rounded-2xl bg-slate-50 p-4" data-new-address-form>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="address[recipient_name]" placeholder="{{ __('Recipient name') }}" class="store-field">
                            <input name="address[phone]" placeholder="{{ __('Phone') }}" class="store-field">
                        </div>
                        <input name="address[address_line]" placeholder="{{ __('Address line') }}" class="store-field">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <input name="address[building_number]" placeholder="{{ __('Building number') }}" class="store-field">
                            <input name="address[floor]" placeholder="{{ __('Floor') }}" class="store-field">
                            <input name="address[apartment]" placeholder="{{ __('Apartment') }}" class="store-field">
                        </div>
                        <input name="address[landmark]" placeholder="{{ __('Landmark') }}" class="store-field">
                        <textarea name="address[notes]" rows="3" placeholder="{{ __('Address notes') }}" class="store-field"></textarea>
                        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                            <input name="address_label" placeholder="{{ __('Address label') }}" class="store-field">
                            <label class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold">
                                <input type="checkbox" name="save_address" value="1">
                                {{ __('Save this address') }}
                            </label>
                        </div>
                    </div>
                </section>

                <section class="store-panel p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl font-black">{{ __('Shipping') }}</h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __('Step') }} 2</span>
                    </div>
                    <div class="mt-5 grid gap-4">
                        <label class="grid gap-2">
                            <span class="text-sm font-black">{{ __('City') }}</span>
                            <select name="city_id" class="store-field" data-shipping-city @disabled(! $requiresShipping)>
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
                                    <span class="store-safe-text block font-black">{{ $carrier['name'] }}</span>
                                    <span class="store-safe-text mt-1 block text-sm text-slate-600">{{ $carrier['estimated_delivery_time'] ?: __('Delivery time will be confirmed.') }}</span>
                                    <span class="mt-3 block text-lg font-black text-red-700">{{ store_money((float) $carrier['cost']) }}</span>
                                </label>
                            @empty
                                <div class="rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800" data-no-carriers>{{ __('No shipping carriers are available for this city right now.') }}</div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="store-panel p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl font-black">{{ __('Payment Method') }}</h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __('Step') }} 3</span>
                    </div>
                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @foreach($paymentMethods as $method)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="payment_method_id" value="{{ $method->id }}" class="sr-only" @checked($loop->first)>
                                <span class="store-safe-text block font-black">{{ $method->name }}</span>
                                <span class="store-safe-text mt-1 block text-sm text-slate-600">{{ $method->description }}</span>
                                @if($method->wallet_url)
                                    <span class="store-safe-text mt-3 block rounded-xl bg-slate-50 p-3 text-sm font-bold text-slate-700">{{ __('Wallet Link') }}: {{ $method->wallet_url }}</span>
                                @endif
                                @if($method->barcode_image && file_exists(public_path('storage/'.$method->barcode_image)))
                                    <img src="{{ asset('storage/'.$method->barcode_image) }}" class="mt-3 h-28 object-contain" alt="{{ $method->name }}">
                                @endif
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="store-panel grid gap-4 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl font-black">{{ __('Order Notes') }}</h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __('Step') }} 4</span>
                    </div>
                    <input type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="store-field">
                    <input name="coupon_code" placeholder="{{ __('Coupon Code') }}" class="store-field">
                    <textarea name="notes" rows="4" placeholder="{{ __('Notes') }}" class="store-field"></textarea>
                </section>
            </div>

            <aside class="store-panel store-mobile-checkout-summary h-fit p-5 sm:p-6 lg:sticky lg:top-44">
                <h2 class="text-2xl font-black">{{ __('Order Summary') }}</h2>
                <div class="mt-5 grid gap-4">
                    @foreach($items as $item)
                        @php($isOffer = ($item->item_type ?? 'product') === 'offer')
                        <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-3 text-sm">
                            <span class="store-safe-text font-bold text-slate-600">
                                {{ $isOffer ? $item->title : $item->product->name }} × {{ $item->quantity }}
                                @if($isOffer)
                                    <span class="block text-xs text-amber-700">{{ __('Offer') }}</span>
                                @endif
                            </span>
                            <span class="font-black whitespace-nowrap">{{ store_money($item->quantity * (float) $item->unit_price) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 border-t border-slate-100 pt-5">
                    <div class="flex justify-between text-lg font-black">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="text-red-700" data-checkout-subtotal="{{ number_format($subtotal, 2, '.', '') }}">{{ store_money($subtotal) }}</span>
                    </div>
                    <div class="mt-3 flex justify-between text-sm font-bold text-slate-600">
                        <span>{{ __('Shipping') }}</span>
                        <span data-checkout-shipping-cost>{{ store_money(0) }}</span>
                    </div>
                    <div class="mt-3 flex justify-between text-xl font-black">
                        <span>{{ __('Total') }}</span>
                        <span class="text-red-700" data-checkout-total>{{ store_money($subtotal) }}</span>
                    </div>
                </div>
                <button class="store-button-primary mt-6 w-full text-base">{{ __('Place Order') }}</button>
            </aside>
        </form>
    @endif
</section>
@endsection
