@extends('layouts.app')

@section('title', __('Addresses'))

@section('content')
<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('account.dashboard') }}" class="transition hover:text-red-700">{{ __('Account') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('Addresses') }}</span>
    </nav>

    <div class="store-page-hero mb-8">
        <p class="store-eyebrow">{{ __('Delivery profile') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Addresses') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Keep delivery information clear for faster checkout.') }}</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-[360px_1fr]">
        <form method="POST" action="{{ route('account.addresses.store') }}" class="store-panel grid h-fit gap-4 p-6">
            @csrf
            <h2 class="text-2xl font-black">{{ __('Add Address') }}</h2>
            <input name="label" placeholder="{{ __('Label') }}" class="store-field">
            <input name="country" placeholder="{{ __('Country') }}" class="store-field">
            <input name="city" placeholder="{{ __('City') }}" class="store-field">
            <input name="town" placeholder="{{ __('Town') }}" class="store-field">
            <input name="state" placeholder="{{ __('State') }}" class="store-field">
            <input name="street" placeholder="{{ __('Street') }}" class="store-field">
            <input name="postal_code" placeholder="{{ __('Postal Code') }}" class="store-field">
            <input name="phone" placeholder="{{ __('Phone') }}" class="store-field">
            <input name="whatsapp" placeholder="{{ __('WhatsApp') }}" class="store-field">
            <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="is_default" value="1"> {{ __('Default') }}</label>
            <button class="store-button-primary">{{ __('Add Address') }}</button>
        </form>

        <section class="grid gap-4">
            @forelse ($addresses as $address)
                <div class="store-panel p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <strong class="text-lg">{{ $address->label ?: __('Address') }}</strong>
                            @if($address->is_default)
                                <span class="ms-2 rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700">{{ __('Default') }}</span>
                            @endif
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $address->country }} / {{ $address->city }} / {{ $address->town }} / {{ $address->street }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-500">{{ __('Phone') }}: {{ $address->phone }} - {{ __('WhatsApp') }}: {{ $address->whatsapp }}</p>
                        </div>
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-2xl border border-red-200 px-4 py-3 text-sm font-black text-red-700 hover:bg-red-50">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="store-panel p-10 text-center">{{ __('No addresses found.') }}</div>
            @endforelse
        </section>
    </div>
</section>
@endsection
