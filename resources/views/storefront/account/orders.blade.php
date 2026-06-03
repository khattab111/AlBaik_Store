@extends('storefront.layout')

@section('title', __('Orders'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Orders') }}</h1>
    <section class="grid gap-4">
        @foreach ($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="rounded border bg-white p-4">
                <strong>{{ $order->order_number }}</strong>
                <span>{{ $order->status }}</span>
                <span>{{ store_money((float) $order->total) }}</span>
            </a>
        @endforeach
    </section>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
