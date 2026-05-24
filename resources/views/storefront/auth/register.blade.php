@extends('layouts.app')

@section('title', __('Register'))

@section('content')
<section class="store-section">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-slate-950/10 lg:grid-cols-[1fr_1fr]">
        <div class="bg-red-700 p-8 text-white sm:p-10">
            <p class="text-sm font-black text-amber-200">{{ __('New customer') }}</p>
            <h1 class="mt-3 text-4xl font-black">{{ __('Register') }}</h1>
            <p class="mt-4 leading-8 text-red-50">{{ __('Create an account to save products, manage addresses, and move through checkout faster.') }}</p>
        </div>
        <form method="POST" action="{{ route('customer.register.store') }}" class="grid gap-4 p-8 sm:p-10">
            @csrf
            <input name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" class="store-field">
            <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="store-field">
            <input name="mobile" value="{{ old('mobile') }}" placeholder="{{ __('Mobile') }}" class="store-field">
            <input name="password" type="password" placeholder="{{ __('Password') }}" class="store-field">
            <input name="password_confirmation" type="password" placeholder="{{ __('Confirm Password') }}" class="store-field">
            <button class="store-button-primary">{{ __('Register') }}</button>
            <a href="{{ route('customer.login') }}" class="text-center text-sm font-black text-red-700">{{ __('Login') }}</a>
        </form>
    </div>
</section>
@endsection
