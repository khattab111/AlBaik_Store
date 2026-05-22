<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'متجر البيك')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased dark:bg-slate-950 dark:text-slate-50">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="text-xl font-bold text-red-700">متجر البيك</a>
            <nav class="hidden items-center gap-5 text-sm font-medium md:flex">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <a href="{{ route('products.index') }}">{{ __('Products') }}</a>
                <a href="{{ route('offers.index') }}">{{ __('Offers') }}</a>
                <a href="{{ route('brands.index') }}">{{ __('Brands') }}</a>
                <a href="{{ route('about') }}">{{ __('About') }}</a>
                <a href="{{ route('contact') }}">{{ __('Contact') }}</a>
            </nav>
            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('cart.index') }}" class="rounded-full border px-3 py-2">{{ __('Cart') }}</a>
                    <a href="{{ route('orders.index') }}" class="rounded-full border px-3 py-2">{{ __('Orders') }}</a>
                    <form method="POST" action="{{ route('customer.logout') }}">@csrf<button class="rounded-full bg-slate-950 px-3 py-2 text-white dark:bg-white dark:text-slate-950">{{ __('Logout') }}</button></form>
                @else
                    <a href="{{ route('customer.login') }}">{{ __('Login') }}</a>
                    <a href="{{ route('customer.register') }}" class="rounded-full bg-red-700 px-4 py-2 text-white">{{ __('Register') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @if (session('status'))
            <div class="mx-auto mt-4 max-w-7xl px-4"><div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">{{ session('status') }}</div></div>
        @endif
        @if ($errors->any())
            <div class="mx-auto mt-4 max-w-7xl px-4"><div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div></div>
        @endif
        @yield('content')
    </main>

    <footer class="mt-16 border-t bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 md:grid-cols-4">
            <div><h3 class="font-bold">متجر البيك</h3><p class="mt-2 text-sm text-slate-600 dark:text-slate-300">منتجات أصلية، أسعار منافسة، وتجربة طلب سهلة.</p></div>
            <div><h3 class="font-semibold">{{ __('Quick Links') }}</h3><div class="mt-3 grid gap-2 text-sm"><a href="{{ route('products.index') }}">{{ __('Products') }}</a><a href="{{ route('offers.index') }}">{{ __('Offers') }}</a><a href="{{ route('brands.index') }}">{{ __('Brands') }}</a></div></div>
            <div><h3 class="font-semibold">{{ __('Account') }}</h3><div class="mt-3 grid gap-2 text-sm"><a href="{{ route('cart.index') }}">{{ __('Cart') }}</a><a href="{{ route('favorites.index') }}">{{ __('Wishlist') }}</a><a href="{{ route('orders.index') }}">{{ __('Orders') }}</a></div></div>
            <div><h3 class="font-semibold">{{ __('Contact') }}</h3><p class="mt-3 text-sm text-slate-600 dark:text-slate-300">support@albaikstore.local<br>+963 900 000 000</p></div>
        </div>
        <div class="border-t py-4 text-center text-sm text-slate-500 dark:border-slate-800">© {{ date('Y') }} متجر البيك</div>
    </footer>
</body>
</html>
