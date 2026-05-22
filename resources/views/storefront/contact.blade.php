@extends('storefront.layout')

@section('title', __('Contact'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Contact') }}</h1>
    <form method="POST" action="{{ route('contact.store') }}" class="grid max-w-2xl gap-4 rounded border bg-white p-4">
        @csrf
        <input name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" class="rounded border px-3 py-2">
        <input name="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="rounded border px-3 py-2">
        <textarea name="message" rows="6" placeholder="{{ __('Message') }}" class="rounded border px-3 py-2">{{ old('message') }}</textarea>
        <button class="rounded bg-gray-950 px-4 py-2 text-white">{{ __('Send') }}</button>
    </form>
@endsection
