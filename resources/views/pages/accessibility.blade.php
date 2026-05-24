@extends('layouts.app')

@section('title', __('Accessibility'))

@section('content')
<section class="store-section">
    <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
            <p class="store-eyebrow">{{ __('Accessibility') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Accessibility statement') }}</h1>
            <p class="mt-4 max-w-xl leading-8 text-slate-600">{{ __('We build the storefront so blind users, low-vision users, keyboard users, and assistive technology users can browse products and complete key actions with fewer barriers.') }}</p>
            <ul class="mt-6 grid gap-3 text-sm font-bold text-slate-700">
                <li class="rounded-2xl bg-white px-4 py-3 shadow-sm">{{ __('Screen reader labels for navigation, search, cart, wishlist, products, and forms.') }}</li>
                <li class="rounded-2xl bg-white px-4 py-3 shadow-sm">{{ __('Visible keyboard focus and skip links for faster navigation.') }}</li>
                <li class="rounded-2xl bg-white px-4 py-3 shadow-sm">{{ __('Text alternatives for meaningful images and clear status messages.') }}</li>
            </ul>
            <a href="{{ route('contact') }}" class="store-button-primary mt-7">{{ __('Report an accessibility issue') }}</a>
        </div>

        <div class="grid gap-4">
            @foreach ([
                [__('Screen reader support'), __('Landmarks, headings, alt text, aria labels, and live status regions are used so assistive technologies can announce page structure and actions.')],
                [__('Keyboard navigation'), __('A skip link, visible focus styles, and keyboard-reachable controls help users move through the page without a mouse.')],
                [__('Blind and low-vision support'), __('Important actions do not depend on color alone, and core text uses strong contrast against its background.')],
                [__('Readable structure'), __('Headings, labels, and grouped sections are used to make content easier to scan and understand.')],
                [__('RTL and LTR support'), __('Arabic and English layouts use the correct page direction and language attributes.')],
                [__('Accessible forms'), __('Forms use visible labels, autocomplete hints, validation feedback, and field-level error messages connected to inputs.')],
                [__('Reduced motion'), __('The interface respects reduced-motion preferences by minimizing animation and transition effects.')],
            ] as [$title, $text])
                <article class="store-panel p-6">
                    <h2 class="text-xl font-black">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
