@extends('storefront.layout')

@section('title', __('Checkout'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Checkout') }}</h1>
    @if ($addresses->isEmpty())
        <p class="mb-4">{{ __('Please add an address before checkout.') }}</p>
        <a href="{{ route('account.addresses.index') }}" class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Addresses') }}</a>
    @else
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="grid gap-4 rounded border bg-white p-4">
            @csrf
            <select name="shipping_address_id" class="rounded border px-3 py-2">
                @foreach ($addresses as $address)
                    <option value="{{ $address->id }}">{{ $address->label }} - {{ $address->country }} / {{ $address->city }} / {{ $address->town }} / {{ $address->street }} - {{ __('Phone') }}: {{ $address->phone }} - {{ __('WhatsApp') }}: {{ $address->whatsapp }}</option>
                @endforeach
            </select>
            <select name="shipping_city_id" class="rounded border px-3 py-2">
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }} - {{ $city->country }}</option>
                @endforeach
            </select>
            @if($requiresShipping)
                <select name="shipping_carrier_id" class="rounded border px-3 py-2">
                    @forelse ($availableCarriers as $carrier)
                        <option value="{{ $carrier['id'] }}">{{ $carrier['name'] }} - {{ number_format((float) $carrier['cost'], 2) }} USD</option>
                    @empty
                        <option value="">{{ __('No shipping carriers are available for this city right now.') }}</option>
                    @endforelse
                </select>
            @else
                <p class="rounded bg-green-50 p-3 text-sm font-semibold text-green-700">{{ __('This cart does not require shipping.') }}</p>
            @endif
            <select name="payment_method_id" id="payment_method_id" class="rounded border px-3 py-2">
                @foreach ($paymentMethods as $method)
                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                @endforeach
            </select>
            <section class="grid gap-4 md:grid-cols-2">
                @foreach ($paymentMethods as $method)
                    <div class="rounded border p-4" data-payment-info="{{ $method->id }}">
                        <h2 class="font-semibold">{{ $method->name }}</h2>
                        <p class="text-sm text-gray-600">{{ $method->description }}</p>
                        @if ($method->image)
                            <img src="{{ asset('storage/'.$method->image) }}" alt="{{ $method->name }}" class="mt-3 h-20 object-contain">
                        @endif
                        @if ($method->wallet_url)
                            <p class="mt-3 text-sm"><strong>{{ __('Wallet Link') }}:</strong> {{ $method->wallet_url }}</p>
                        @endif
                        @if ($method->barcode_image)
                            <img src="{{ asset('storage/'.$method->barcode_image) }}" alt="{{ __('Barcode Image') }}" class="mt-3 h-32 object-contain">
                        @endif
                    </div>
                @endforeach
            </section>
            <label class="grid gap-2">
                <span>{{ __('Payment Receipt') }}</span>
                <input type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="rounded border px-3 py-2">
            </label>
            <input name="coupon_code" placeholder="{{ __('Coupon Code') }}" class="rounded border px-3 py-2">
            <textarea name="notes" rows="3" placeholder="{{ __('Notes') }}" class="rounded border px-3 py-2"></textarea>
            <p class="font-bold">{{ __('Subtotal') }}: {{ number_format($subtotal, 2) }} USD</p>
            <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Place Order') }}</button>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const select = document.getElementById('payment_method_id');
                const cards = document.querySelectorAll('[data-payment-info]');
                const syncPaymentInfo = () => {
                    cards.forEach((card) => {
                        card.hidden = card.dataset.paymentInfo !== select.value;
                    });
                };

                select.addEventListener('change', syncPaymentInfo);
                syncPaymentInfo();
            });
        </script>
    @endif
@endsection
