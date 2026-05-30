@extends('layouts.app')

@section('title', __('Admin Documentation'))

@section('content')
<section class="store-section" data-no-motion>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="store-eyebrow text-red-700">{{ __('Administration') }}</p>
            <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ __('Store Documentation') }}</h1>
        </div>
        <a href="{{ url('/admin') }}" class="store-button-secondary">{{ __('Back to Admin') }}</a>
    </div>

    @if (! empty($headings))
        <nav class="store-panel store-documentation-mobile-nav mb-5 p-4 lg:hidden" aria-label="{{ __('Documentation navigation') }}">
            <label for="documentation-jump" class="mb-2 block text-sm font-black text-slate-700">{{ __('Jump to section') }}</label>
            <select id="documentation-jump" data-documentation-jump class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800">
                @foreach ($headings as $heading)
                    <option value="{{ $heading['id'] }}">{{ str_repeat('- ', max(0, $heading['level'] - 1)).$heading['title'] }}</option>
                @endforeach
            </select>
        </nav>
    @endif

    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        @if (! empty($headings))
            <aside class="hidden lg:block">
                <nav class="store-panel store-documentation-sidebar p-4" aria-label="{{ __('Documentation navigation') }}">
                    <p class="px-3 pb-3 text-xs font-black uppercase tracking-wider text-slate-400">{{ __('Contents') }}</p>
                    <div class="space-y-1">
                        @foreach ($headings as $heading)
                            <a
                                href="#{{ $heading['id'] }}"
                                class="store-documentation-nav-link store-documentation-nav-link--level-{{ $heading['level'] }}"
                            >
                                {{ $heading['title'] }}
                            </a>
                        @endforeach
                    </div>
                </nav>
            </aside>
        @endif

        <article class="store-panel store-documentation-content max-w-none p-6 sm:p-8">
            {!! $html !!}
        </article>
    </div>
</section>
@endsection
