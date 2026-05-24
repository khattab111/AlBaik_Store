@extends('layouts.app')

@section('title', __('Profile'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('account.dashboard') }}" class="transition hover:text-red-700">{{ __('Account') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Profile') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Account settings') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Profile') }}</h1>
    </div>
    <form method="POST" action="{{ route('account.profile.update') }}" class="store-panel grid max-w-2xl gap-4 p-6">
        @csrf
        @method('PATCH')
        <input name="name" value="{{ old('name', $user->name) }}" placeholder="{{ __('Name') }}" class="store-field">
        <input name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="{{ __('Email') }}" class="store-field">
        <input name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="{{ __('Mobile') }}" class="store-field">
        <button class="store-button-primary">{{ __('Save') }}</button>
    </form>
</section>
@endsection
