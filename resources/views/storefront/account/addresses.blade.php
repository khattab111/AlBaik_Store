@extends('storefront.layout')

@section('title', __('Addresses'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Addresses') }}</h1>
    <form method="POST" action="{{ route('account.addresses.store') }}" class="mb-8 grid gap-4 rounded border bg-white p-4 md:grid-cols-2">
        @csrf
        <input name="label" placeholder="{{ __('Label') }}" class="rounded border px-3 py-2">
        <input name="country" placeholder="{{ __('Country') }}" class="rounded border px-3 py-2">
        <input name="city" placeholder="{{ __('City') }}" class="rounded border px-3 py-2">
        <input name="town" placeholder="{{ __('Town') }}" class="rounded border px-3 py-2">
        <input name="state" placeholder="{{ __('State') }}" class="rounded border px-3 py-2">
        <input name="street" placeholder="{{ __('Street') }}" class="rounded border px-3 py-2">
        <input name="postal_code" placeholder="{{ __('Postal Code') }}" class="rounded border px-3 py-2">
        <input name="phone" placeholder="{{ __('Phone') }}" class="rounded border px-3 py-2">
        <input name="whatsapp" placeholder="{{ __('WhatsApp') }}" class="rounded border px-3 py-2">
        <label class="flex items-center gap-2"><input type="checkbox" name="is_default" value="1"> {{ __('Default') }}</label>
        <button class="rounded bg-gray-950 px-4 py-2 text-white md:col-span-2">{{ __('Add Address') }}</button>
    </form>
    <section class="grid gap-4">
        @foreach ($addresses as $address)
            <div class="rounded border bg-white p-4">
                <strong>{{ $address->label ?: __('Address') }}</strong>
                <p>{{ $address->country }} / {{ $address->city }} / {{ $address->town }} / {{ $address->street }}</p>
                <p>{{ __('Phone') }}: {{ $address->phone }} - {{ __('WhatsApp') }}: {{ $address->whatsapp }}</p>
                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button class="rounded border px-3 py-2">{{ __('Delete') }}</button>
                </form>
            </div>
        @endforeach
    </section>
@endsection
