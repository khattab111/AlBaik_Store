@extends('layouts.app')

@section('title', __('Reset Password'))

@section('content')
<section class="store-section">
    <div class="mx-auto max-w-xl">
        <div class="store-panel p-6">
            <p class="store-eyebrow">{{ __('Account Access') }}</p>
            <h1 class="mt-2 text-3xl font-black">{{ __('Reset Password') }}</h1>
            <p class="mt-3 text-sm font-semibold text-slate-500">{{ __('Enter your email and we will send you a secure password reset link.') }}</p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 grid gap-4">
                @csrf
                <div>
                    <label for="email" class="store-label">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="store-field">
                </div>
                <button class="store-button-primary justify-center">{{ __('Send Reset Link') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection
