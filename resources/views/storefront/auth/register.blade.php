@extends('storefront.layout')

@section('title', __('Register'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Register') }}</h1>
    <form method="POST" action="{{ route('customer.register.store') }}" class="grid max-w-md gap-4 rounded border bg-white p-4">
        @csrf
        <input name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" class="rounded border px-3 py-2">
        <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="rounded border px-3 py-2">
        <input name="mobile" value="{{ old('mobile') }}" placeholder="{{ __('Mobile') }}" class="rounded border px-3 py-2">
        <input name="password" type="password" placeholder="{{ __('Password') }}" class="rounded border px-3 py-2">
        <input name="password_confirmation" type="password" placeholder="{{ __('Confirm Password') }}" class="rounded border px-3 py-2">
        <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Register') }}</button>
    </form>
@endsection
