@extends('layouts.app')
@section('title', __('Orders'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-10"><h1 class="mb-6 text-3xl font-bold">{{ __('Orders') }}</h1>@forelse($orders as $order)<a href="{{ route('orders.show',$order) }}" class="mb-3 grid gap-2 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900 md:grid-cols-4"><strong>{{ $order->order_number }}</strong><span>{{ __($order->status) }}</span><span>{{ number_format((float)$order->total,2) }} USD</span><span>{{ $order->created_at->format('Y-m-d') }}</span></a>@empty<div class="rounded-xl border bg-white p-8 text-center dark:border-slate-800 dark:bg-slate-900">{{ __('No orders found.') }}</div>@endforelse<div class="mt-8">{{ $orders->links() }}</div></section>
@endsection
