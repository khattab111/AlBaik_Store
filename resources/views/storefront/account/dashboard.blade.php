@extends('storefront.layout')

@section('title', __('Account'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Account') }}</h1>
    <nav class="mb-6 flex gap-4">
        <a href="{{ route('account.profile.edit') }}">{{ __('Profile') }}</a>
        <a href="{{ route('account.addresses.index') }}">{{ __('Addresses') }}</a>
        <a href="{{ route('account.orders.index') }}">{{ __('Orders') }}</a>
        <a href="{{ route('wishlist.index') }}">{{ __('Wishlist') }}</a>
    </nav>
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded border bg-white p-4">{{ __('Orders') }}: {{ $ordersCount }}</div>
        <div class="rounded border bg-white p-4">{{ __('Addresses') }}: {{ $addressesCount }}</div>
        <div class="rounded border bg-white p-4">{{ __('Wishlist') }}: {{ $wishlistCount }}</div>
    </section>
@endsection
