<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale ?? app()->getLocale()) }}" dir="{{ $textDirection ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('AlBaik Store'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-950">
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="font-bold">{{ __('AlBaik Store') }}</a>
            <nav class="flex flex-wrap gap-4 text-sm">
                <a href="{{ route('shop.index') }}">{{ __('Shop') }}</a>
                <a href="{{ route('brands.index') }}">{{ __('Brands') }}</a>
                <a href="{{ route('offers.index') }}">{{ __('Offers') }}</a>
                <a href="{{ route('contact.create') }}">{{ __('Contact') }}</a>
                @auth
                    <a href="{{ route('cart.index') }}">{{ __('Cart') }}</a>
                    <a href="{{ route('account.dashboard') }}">{{ __('Account') }}</a>
                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button type="submit">{{ __('Logout') }}</button>
                    </form>
                @else
                    <a href="{{ route('customer.login') }}">{{ __('Login') }}</a>
                    <a href="{{ route('customer.register') }}">{{ __('Register') }}</a>
                @endauth
                @foreach (($supportedLocales ?? config('locales.supported', [])) as $localeCode => $localeConfig)
                    @if (($currentLocale ?? app()->getLocale()) !== $localeCode)
                        <a href="{{ route('locale.switch', $localeCode) }}">{{ $localeConfig['native'] }}</a>
                    @endif
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded border border-green-200 bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded border border-red-200 bg-red-50 p-3 text-red-800">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
