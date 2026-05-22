@extends('storefront.layout')

@section('title', __('Profile'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Profile') }}</h1>
    <form method="POST" action="{{ route('account.profile.update') }}" class="grid max-w-xl gap-4 rounded border bg-white p-4">
        @csrf
        @method('PATCH')
        <input name="name" value="{{ old('name', $user->name) }}" placeholder="{{ __('Name') }}" class="rounded border px-3 py-2">
        <input name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="{{ __('Email') }}" class="rounded border px-3 py-2">
        <input name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="{{ __('Mobile') }}" class="rounded border px-3 py-2">
        <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Save') }}</button>
    </form>
@endsection
