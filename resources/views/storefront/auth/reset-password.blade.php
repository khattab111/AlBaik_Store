@extends('layouts.app')

@section('title', __('Create Password'))

@section('content')
<section class="store-section">
    <div class="mx-auto max-w-xl">
        <div class="store-panel p-6">
            <p class="store-eyebrow">{{ __('Wholesale Account') }}</p>
            <h1 class="mt-2 text-3xl font-black">{{ __('Create Password') }}</h1>
            <p class="mt-3 text-sm font-semibold text-slate-500">{{ __('Set your password, then log in to browse wholesale prices and quantity tiers.') }}</p>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 grid gap-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="store-label">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required class="store-field">
                </div>
                <div>
                    <label for="password" class="store-label">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="store-field">
                </div>
                <div>
                    <label for="password_confirmation" class="store-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="store-field">
                </div>
                <button class="store-button-primary justify-center">{{ __('Save Password') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection
