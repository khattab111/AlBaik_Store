@extends('layouts.app')

@section('title', __('Sitemap'))

@section('content')
<section class="store-section">
    <div class="rounded-[2rem] bg-white p-8 shadow-sm">
        <p class="store-eyebrow">{{ __('Site navigation') }}</p>
        <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('Sitemap') }}</h1>
        <p class="mt-4 max-w-2xl leading-8 text-slate-600">{{ __('Use this page to quickly reach the main public areas of the store.') }}</p>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        @foreach($sections as $section)
            <section class="store-panel p-6" aria-labelledby="sitemap-section-{{ $loop->index }}">
                <h2 id="sitemap-section-{{ $loop->index }}" class="text-2xl font-black">{{ $section['title'] }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $section['description'] }}</p>
                <div class="mt-5 grid gap-3">
                    @forelse($section['links'] as $link)
                        <a href="{{ $link['url'] }}" class="rounded-2xl border border-slate-100 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">{{ $link['label'] }}</a>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No links available.') }}</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</section>
@endsection
