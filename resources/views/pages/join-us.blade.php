@extends('layouts.app')

@section('title', __('Join Us'))
@section('meta_description', __('Apply to become an approved wholesale partner and access quantity-based prices.'))

@section('content')
<section class="store-section">
    <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="rounded-[2rem] bg-slate-950 p-8 text-white shadow-2xl shadow-slate-950/20">
            <p class="store-eyebrow text-amber-300">{{ __('Wholesale Partnership') }}</p>
            <h1 class="mt-4 text-4xl font-black leading-tight sm:text-5xl">{{ __('Join us to access wholesale pricing') }}</h1>
            <p class="mt-5 text-base leading-8 text-slate-200">{{ __('Submit your business information. Our team will review the request, approve eligible partners, and activate wholesale tiers on your account.') }}</p>
            <div class="mt-8 grid gap-4 text-sm font-bold text-slate-200">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">{{ __('Quantity-based wholesale price tiers') }}</div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">{{ __('Manual review before account creation') }}</div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">{{ __('Special offers for approved business customers') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('join-us.store') }}" class="store-panel grid gap-5 p-6">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="full_name" class="store-label">{{ __('Full Name') }}</label>
                    <input id="full_name" name="full_name" value="{{ old('full_name') }}" required class="store-field">
                </div>
                <div>
                    <label for="email" class="store-label">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="store-field">
                </div>
                <div>
                    <label for="phone" class="store-label">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" required class="store-field">
                </div>
                <div>
                    <label for="whatsapp" class="store-label">{{ __('WhatsApp') }}</label>
                    <input id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" class="store-field">
                </div>
                <div>
                    <label for="business_name" class="store-label">{{ __('Business Name') }}</label>
                    <input id="business_name" name="business_name" value="{{ old('business_name') }}" required class="store-field">
                </div>
                <div>
                    <label for="business_type" class="store-label">{{ __('Business Type') }}</label>
                    <input id="business_type" name="business_type" value="{{ old('business_type') }}" required class="store-field">
                </div>
                <div>
                    <label for="city" class="store-label">{{ __('City') }}</label>
                    <input id="city" name="city" value="{{ old('city') }}" required class="store-field">
                </div>
                <div>
                    <label for="address" class="store-label">{{ __('Address / Location') }}</label>
                    <input id="address" name="address" value="{{ old('address') }}" required class="store-field">
                </div>
            </div>
            <div>
                <label for="notes" class="store-label">{{ __('Additional Notes') }}</label>
                <textarea id="notes" name="notes" rows="4" class="store-field">{{ old('notes') }}</textarea>
            </div>
            <button class="store-button-primary justify-center">{{ __('Submit Partnership Request') }}</button>
        </form>
    </div>
</section>
@endsection
