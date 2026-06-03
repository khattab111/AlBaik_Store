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

    <div class="grid gap-8 lg:grid-cols-[360px_minmax(0,1fr)]">
        <form method="POST" action="{{ route('account.addresses.store') }}" class="store-panel grid h-fit gap-4 p-5 sm:p-6">
            @csrf
            <h2 class="text-2xl font-black">{{ __('Add Address') }}</h2>
            <input name="label" placeholder="{{ __('Label') }}" class="store-field">
            <input name="recipient_name" placeholder="{{ __('Recipient name') }}" class="store-field" required>
            <input name="phone" placeholder="{{ __('Phone') }}" class="store-field" required>
            <select name="city_id" class="store-field" required>
                <option value="">{{ __('Choose city') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }} - {{ $city->country }}</option>
                @endforeach
            </select>
            <input name="address_line" placeholder="{{ __('Address line') }}" class="store-field" required>
            <input name="building_number" placeholder="{{ __('Building number') }}" class="store-field">
            <input name="floor" placeholder="{{ __('Floor') }}" class="store-field">
            <input name="apartment" placeholder="{{ __('Apartment') }}" class="store-field">
            <input name="landmark" placeholder="{{ __('Landmark') }}" class="store-field">
            <textarea name="notes" rows="3" placeholder="{{ __('Notes') }}" class="store-field"></textarea>
            <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="is_default" value="1"> {{ __('Default') }}</label>
            <button class="store-button-primary">{{ __('Add Address') }}</button>
        </form>

        <section class="grid gap-4">
            @forelse ($addresses as $address)
                <div class="store-panel p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <strong class="store-safe-text text-lg">{{ $address->label ?: __('Address') }}</strong>
                            @if($address->is_default)
                                <span class="ms-2 rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700">{{ __('Default') }}</span>
                            @endif
                            <p class="store-safe-text mt-1 text-sm font-bold text-slate-500">{{ $address->recipient_name }} - {{ __('Phone') }}: {{ $address->phone }}</p>
                            <p class="store-safe-text mt-2 text-sm leading-6 text-slate-600">{{ $address->city?->name }} / {{ $address->address_line }}</p>
                            @if($address->landmark)
                                <p class="store-safe-text mt-1 text-sm text-slate-500">{{ __('Landmark') }}: {{ $address->landmark }}</p>
                            @endif
                        </div>
                        <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-2">
                            @unless($address->is_default)
                                <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-2xl border border-emerald-200 px-4 py-3 text-sm font-black text-emerald-700 hover:bg-emerald-50">{{ __('Set Default') }}</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                                @csrf
                                @method('DELETE')
                                <button class="w-full rounded-2xl border border-red-200 px-4 py-3 text-sm font-black text-red-700 hover:bg-red-50">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="store-panel p-10 text-center">{{ __('No addresses found.') }}</div>
            @endforelse
        </section>
    </div>
</section>
@endsection
