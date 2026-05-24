@extends('layouts.app')

@section('title', __('Contact'))

@section('content')
<section class="store-section">
    <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr]">
        <div>
            <p class="store-eyebrow">{{ __('Support') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Contact') }}</h1>
            <p class="mt-4 max-w-xl leading-8 text-slate-600">{{ __('Send your message and we will respond as soon as possible.') }}</p>

            <div class="mt-8 grid gap-4">
                @foreach ([
                    ['title' => __('Email'), 'value' => 'support@albaikstore.local', 'text' => __('Best for order questions and business requests.')],
                    ['title' => __('Phone'), 'value' => '+963 900 000 000', 'text' => __('Use during working hours for urgent support.')],
                    ['title' => __('Location'), 'value' => 'Damascus, Syria', 'text' => __('Regional shipping support for city and wholesale orders.')],
                ] as $channel)
                    <article class="store-panel p-5">
                        <p class="text-sm font-black text-red-700">{{ $channel['title'] }}</p>
                        <p class="mt-2 text-lg font-black">{{ $channel['value'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $channel['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('contact.store') }}" class="store-panel grid gap-5 p-6 sm:p-8" aria-label="{{ __('Contact form') }}">
            @csrf
            <div>
                <label for="contact-name" class="mb-2 block text-sm font-black">{{ __('Name') }}</label>
                <input id="contact-name" name="name" value="{{ old('name') }}" autocomplete="name" class="store-field" required aria-invalid="@error('name') true @else false @enderror" @error('name') aria-describedby="contact-name-error" @enderror>
                @error('name') <p id="contact-name-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="contact-email" class="mb-2 block text-sm font-black">{{ __('Email') }}</label>
                    <input id="contact-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="store-field" required aria-invalid="@error('email') true @else false @enderror" @error('email') aria-describedby="contact-email-error" @enderror>
                    @error('email') <p id="contact-email-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contact-phone" class="mb-2 block text-sm font-black">{{ __('Phone') }}</label>
                    <input id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="store-field" aria-invalid="@error('phone') true @else false @enderror" @error('phone') aria-describedby="contact-phone-error" @enderror>
                    @error('phone') <p id="contact-phone-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="contact-message" class="mb-2 block text-sm font-black">{{ __('Message') }}</label>
                <textarea id="contact-message" name="message" rows="7" class="store-field" required aria-describedby="contact-message-help @error('message') contact-message-error @enderror" aria-invalid="@error('message') true @else false @enderror">{{ old('message') }}</textarea>
                <p id="contact-message-help" class="mt-2 text-sm text-slate-500">{{ __('Include your order number if your message is about an existing order.') }}</p>
                @error('message') <p id="contact-message-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
            </div>
            <button class="store-button-primary" aria-label="{{ __('Send contact message') }}">{{ __('Send') }}</button>
        </form>
    </div>
</section>
@endsection
