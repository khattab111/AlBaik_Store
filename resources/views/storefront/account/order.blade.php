@extends('storefront.layout')

@section('title', $order->order_number)

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ $order->order_number }}</h1>
    <div class="mb-6 rounded border bg-white p-4">
        <p>{{ __('Status') }}: {{ $order->status }}</p>
        <p>{{ __('Total') }}: {{ number_format((float) $order->total, 2) }} USD</p>
        <p>{{ __('Payment method') }}: {{ $order->paymentMethod?->name }}</p>
        <p>{{ __('Shipping method') }}: {{ $order->shippingMethod?->name }}</p>
        <p>{{ __('Phone') }}: {{ $order->customer_phone }}</p>
        <p>{{ __('WhatsApp') }}: {{ $order->customer_whatsapp }}</p>
        <p>{{ __('Shipping Address') }}: {{ $order->shipping_country }} / {{ $order->shipping_city }} / {{ $order->shipping_town }} / {{ $order->shipping_street }}</p>
        @foreach ($order->payments as $payment)
            <div class="mt-4 rounded border p-3">
                <p>{{ __('Payment Status') }}: {{ $payment->status }}</p>
                @if ($payment->receipt_image)
                    <a href="{{ asset('storage/'.$payment->receipt_image) }}" target="_blank" class="text-blue-700">{{ __('Payment Receipt') }}</a>
                @endif
            </div>
        @endforeach
    </div>
    <h2 class="mb-3 text-xl font-semibold">{{ __('Items') }}</h2>
    <section class="grid gap-3">
        @foreach ($order->items as $item)
            <div class="rounded border bg-white p-4">
                {{ $item->product->name }} x {{ $item->quantity }} = {{ number_format((float) $item->total_price, 2) }} USD
            </div>
        @endforeach
    </section>
@endsection
