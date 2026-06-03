@extends('layouts.app')

@section('title', __('Unsubscribed'))

@section('content')
    <section class="store-section py-16">
        <div class="mx-auto max-w-2xl rounded-3xl border bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-black text-amber-600">{{ __('Newsletter') }}</p>
            <h1 class="mt-3 text-3xl font-black text-slate-950">{{ __('You have been unsubscribed from the newsletter.') }}</h1>
            <p class="mt-4 text-sm leading-7 text-slate-600">{{ __('You can subscribe again anytime from the storefront newsletter form.') }}</p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white">{{ __('Back to home') }}</a>
        </div>
    </section>
@endsection
