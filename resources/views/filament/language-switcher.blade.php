@php
    $locale = $currentLocale ?? app()->getLocale();
    $locales = $supportedLocales ?? config('locales.supported', []);
@endphp

<div class="flex items-center gap-2 rounded-xl bg-gray-50 p-1 text-sm font-semibold dark:bg-gray-800">
    @foreach ($locales as $localeCode => $localeConfig)
        <a
            href="{{ route('locale.switch', $localeCode) }}"
            class="rounded-lg px-3 py-1.5 transition {{ $locale === $localeCode ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
        >
            {{ $localeConfig['native'] }}
        </a>
    @endforeach
</div>
