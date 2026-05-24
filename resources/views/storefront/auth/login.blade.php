@extends('layouts.app')

@section('title', __('Login'))

@section('content')
<section class="store-section">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-slate-950/10 lg:grid-cols-[1fr_1fr]">
        <div class="bg-slate-950 p-8 text-white sm:p-10">
            <p class="text-sm font-black text-amber-300">{{ __('Welcome back') }}</p>
            <h1 class="mt-3 text-4xl font-black">{{ __('Login') }}</h1>
            <p class="mt-4 leading-8 text-slate-300">{{ __('Access your cart, saved products, addresses, and order history from one clean account center.') }}</p>
        </div>
        <form method="POST" action="{{ route('customer.login.store') }}" class="grid gap-4 p-8 sm:p-10">
            @csrf
            <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="store-field">
            <input name="password" type="password" placeholder="{{ __('Password') }}" class="store-field">
            <label class="flex gap-2 text-sm font-bold"><input type="checkbox" name="remember" value="1"> {{ __('Remember me') }}</label>
            <button class="store-button-primary">{{ __('Login') }}</button>
            <a href="{{ route('customer.register') }}" class="text-center text-sm font-black text-red-700">{{ __('Create Account') }}</a>
        </form>
    </div>
</section>
@endsection
