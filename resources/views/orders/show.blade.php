@extends('layouts.app')

@section('title', $order->order_number)

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('orders.index') }}" class="transition hover:text-red-700">{{ __('Orders') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ $order->order_number }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Order Details') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ $order->order_number }}</h1>
    </div>

    <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
        <div class="grid gap-4">
            @foreach($order->items as $item)
                @php($isOffer = ($item->item_type ?? 'product') === 'offer')
                <div class="store-panel flex flex-wrap items-center justify-between gap-4 p-5">
                    <div>
                        <h2 class="font-black">{{ $isOffer ? $item->offer_title : $item->product->name }}</h2>
                        @if($isOffer)
                            <p class="mt-1 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">{{ __('Offer') }}</p>
                            <div class="mt-3 grid gap-1 text-xs font-bold text-slate-500">
                                @foreach(collect($item->components_snapshot ?? [])->take(5) as $component)
                                    <p>{{ $component['product_name'] ?? __('Product') }} × {{ $component['quantity'] ?? 1 }}</p>
                                @endforeach
                            </div>
                        @endif
                        <p class="text-sm font-bold text-slate-500">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                    </div>
                    <p class="text-lg font-black text-red-700">USD {{ number_format((float)$item->total_price, 2) }}</p>
                </div>
            @endforeach
        </div>

        <aside class="store-panel h-fit p-6">
            <h2 class="text-2xl font-black">{{ __('Summary') }}</h2>
            <div class="mt-5 grid gap-3 text-sm font-bold text-slate-600">
                <p>{{ __('Status') }}: <span class="text-slate-950">{{ __($order->status) }}</span></p>
                <p>{{ __('Total') }}: <span class="text-red-700">USD {{ number_format((float)$order->total, 2) }}</span></p>
                <p>{{ __('Shipping Address') }}: {{ $order->shipping_country }} / {{ $order->shipping_city }} / {{ $order->shipping_town }} / {{ $order->shipping_street }}</p>
                <p>{{ __('Phone') }}: {{ $order->customer_phone }}</p>
                <p>{{ __('WhatsApp') }}: {{ $order->customer_whatsapp }}</p>
                @foreach($order->payments as $payment)
                    <p>{{ __('Payment Status') }}: <span class="text-slate-950">{{ __($payment->status) }}</span></p>
                    @if($payment->receipt_image)
                        <a class="font-black text-red-700" href="{{ asset('storage/'.$payment->receipt_image) }}" target="_blank">{{ __('Payment Receipt') }}</a>
                    @endif
                @endforeach
            </div>
        </aside>
    </div>
</section>
@endsection
