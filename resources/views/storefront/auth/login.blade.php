@extends('storefront.layout')

@section('title', __('Login'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Login') }}</h1>
    <form method="POST" action="{{ route('customer.login.store') }}" class="grid max-w-md gap-4 rounded border bg-white p-4">
        @csrf
        <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="rounded border px-3 py-2">
        <input name="password" type="password" placeholder="{{ __('Password') }}" class="rounded border px-3 py-2">
        <label class="flex gap-2"><input type="checkbox" name="remember" value="1"> {{ __('Remember me') }}</label>
        <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Login') }}</button>
    </form>
@endsection
