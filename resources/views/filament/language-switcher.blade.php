@php($locale = app()->getLocale())

<div class="fi-language-switcher mx-3 my-2 flex items-center justify-center gap-1 rounded-xl bg-gray-100 p-1 text-sm font-medium shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
    <a
        href="{{ route('locale.switch', 'en') }}"
        class="rounded-lg px-3 py-1.5 transition {{ $locale === 'en' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
    >
        English
    </a>
    <a
        href="{{ route('locale.switch', 'ar') }}"
        class="rounded-lg px-3 py-1.5 transition {{ $locale === 'ar' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
    >
        العربية
    </a>
</div>
