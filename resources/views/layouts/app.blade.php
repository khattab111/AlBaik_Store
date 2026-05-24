@php
    $siteIdentity = $siteIdentity ?? [];
    $siteContact = $siteContact ?? [];
    $siteSocial = $siteSocial ?? [];
    $siteName = $siteIdentity['name'] ?? __('AlBaik Store');
    $siteTagline = $siteIdentity['tagline'] ?? __('Premium Market');
    $siteDescription = $siteIdentity['short_description'] ?? __('Original products, competitive prices, and a complete shopping experience for retail and wholesale customers.');
    $siteLogo = $siteIdentity['logo'] ?? null;
    $siteLogoUrl = $siteLogo && file_exists(public_path('storage/'.$siteLogo)) ? asset('storage/'.$siteLogo) : null;
    $siteFavicon = $siteIdentity['favicon'] ?? null;
    $siteFaviconUrl = $siteFavicon && file_exists(public_path('storage/'.$siteFavicon)) ? asset('storage/'.$siteFavicon) : null;
    $primaryColor = $siteIdentity['primary_color'] ?? '#b91c1c';
    $primaryHoverColor = $siteIdentity['primary_hover_color'] ?? '#991b1b';
    $accentColor = $siteIdentity['accent_color'] ?? '#f59e0b';
    $topbarColor = $siteIdentity['topbar_color'] ?? '#020617';
    $headerBgColor = $siteIdentity['header_bg_color'] ?? '#ffffff';
    $navBgColor = $siteIdentity['nav_bg_color'] ?? '#ffffff';
    $bodyBgColor = $siteIdentity['body_bg_color'] ?? '#f8fafc';
    $surfaceColor = $siteIdentity['surface_color'] ?? '#ffffff';
    $surfaceTintColor = $siteIdentity['surface_tint_color'] ?? '#fff5f5';
    $textColor = $siteIdentity['text_color'] ?? '#0f172a';
    $mutedTextColor = $siteIdentity['muted_text_color'] ?? '#64748b';
    $borderColor = $siteIdentity['border_color'] ?? '#e2e8f0';
    $heroOverlayFrom = $siteIdentity['hero_overlay_from'] ?? 'rgba(2,6,23,.96)';
    $heroOverlayTo = $siteIdentity['hero_overlay_to'] ?? 'rgba(185,28,28,.52)';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale ?? app()->getLocale()) }}" dir="{{ $textDirection ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('meta_description', $siteDescription)">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', $siteName)))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description', __('Shop original products from AlBaik Store.'))))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif
    @yield('structured_data')
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
    @endif
    <style>
        :root {
            --store-primary: {{ $primaryColor }};
            --store-primary-hover: {{ $primaryHoverColor }};
            --store-accent: {{ $accentColor }};
            --store-topbar: {{ $topbarColor }};
            --store-header-bg: {{ $headerBgColor }};
            --store-nav-bg: {{ $navBgColor }};
            --store-body-bg: {{ $bodyBgColor }};
            --store-surface: {{ $surfaceColor }};
            --store-surface-tint: {{ $surfaceTintColor }};
            --store-text: {{ $textColor }};
            --store-muted: {{ $mutedTextColor }};
            --store-border: {{ $borderColor }};
            --store-hero-overlay-from: {{ $heroOverlayFrom }};
            --store-hero-overlay-to: {{ $heroOverlayTo }};
        }
    </style>
    <script>
        (function () {
            try {
                var storedTheme = window.localStorage.getItem('storefront-theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = storedTheme || (prefersDark ? 'dark' : 'light');

                document.documentElement.dataset.theme = theme;
                document.documentElement.classList.toggle('dark', theme === 'dark');
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8fafc] text-slate-950 antialiased" style="background-color: var(--store-body-bg); color: var(--store-text)">
    <div class="store-progress" aria-hidden="true"><span data-scroll-progress></span></div>
    <a href="#main-content" class="skip-link">{{ __('Skip to main content') }}</a>

    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur" style="background-color: color-mix(in srgb, var(--store-header-bg) 95%, transparent); border-color: var(--store-border)" role="banner" data-store-header>
        <div class="border-b border-slate-100 text-white" style="background-color: var(--store-topbar)">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 py-2 text-xs font-semibold sm:text-sm">
                <span class="inline-flex items-center gap-2"><span aria-hidden="true">🚚</span>{{ __('Fast delivery, secure payment, and original products.') }}</span>
                <div class="flex items-center gap-4">
                    <a href="{{ route('offers.index') }}" class="inline-flex items-center gap-1 text-amber-300"><span aria-hidden="true">🔥</span>{{ __('Daily Offers') }}</a>
                    @foreach (($supportedLocales ?? config('locales.supported', [])) as $localeCode => $localeConfig)
                        @if (($currentLocale ?? app()->getLocale()) !== $localeCode)
                            <a href="{{ route('locale.switch', $localeCode) }}" class="inline-flex items-center gap-1"><span aria-hidden="true">🌐</span>{{ $localeConfig['native'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-7xl items-center gap-4 px-4 py-4 lg:grid-cols-[auto_minmax(280px,1fr)_auto]">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ __(':name home', ['name' => $siteName]) }}">
                @if ($siteLogoUrl)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-12 w-12 rounded-2xl object-contain shadow-lg">
                @else
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-black text-white shadow-lg shadow-red-700/20" style="background-color: var(--store-primary)" aria-hidden="true">{{ mb_substr($siteName, 0, 1) }}</span>
                @endif
                <span class="grid leading-tight">
                    <span class="text-lg font-black" style="color: var(--store-primary)">{{ $siteName }}</span>
                    <span class="text-xs font-semibold uppercase tracking-normal text-slate-500">{{ $siteTagline }}</span>
                </span>
            </a>

            <form method="GET" action="{{ route('products.index') }}" class="order-3 flex min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-1 shadow-inner lg:order-none" role="search" aria-label="{{ __('Search storefront') }}">
                <label for="storefront-search" class="sr-only">{{ __('Search products, brands, offers...') }}</label>
                <input id="storefront-search" name="search" type="search" value="{{ request('search') }}" autocomplete="off" class="min-w-0 flex-1 bg-transparent px-4 py-3 text-sm outline-none" placeholder="{{ __('Search products, brands, offers...') }}">
                <button class="rounded-xl px-5 py-3 text-sm font-bold text-white transition hover:opacity-90" style="background-color: var(--store-primary)" aria-label="{{ __('Submit product search') }}">{{ __('Search') }}</button>
            </form>

            <nav class="flex items-center justify-end gap-2 text-sm font-bold" aria-label="{{ __('Account navigation') }}">
                <button type="button" class="store-icon-button" data-theme-toggle data-light-label="{{ __('Switch to light mode') }}" data-dark-label="{{ __('Switch to dark mode') }}" aria-label="{{ __('Switch to dark mode') }}" aria-pressed="false">
                    <span data-theme-icon-light aria-hidden="true">☀</span>
                    <span data-theme-icon-dark aria-hidden="true" hidden>☾</span>
                </button>
                @auth
                    <a href="{{ route('favorites.index') }}" class="store-icon-button" aria-label="{{ __('Wishlist, :count items', ['count' => $wishlistCount ?? 0]) }}">
                        <span aria-hidden="true">♡</span>
                        <span class="store-badge" aria-hidden="true">{{ $wishlistCount ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="store-icon-button" aria-label="{{ __('Cart, :count items', ['count' => $cartCount ?? 0]) }}">
                        <span aria-hidden="true">🛒</span>
                        <span class="store-badge" aria-hidden="true">{{ $cartCount ?? 0 }}</span>
                    </a>
                    <a href="{{ route('account.dashboard') }}" class="hidden rounded-2xl border border-slate-200 px-4 py-3 transition hover:border-red-200 hover:text-red-700 sm:inline-flex">{{ __('Account') }}</a>
                @else
                    <a href="{{ route('customer.login') }}" class="store-icon-button" aria-label="{{ __('Wishlist, :count items', ['count' => 0]) }}">
                        <span aria-hidden="true">♡</span>
                        <span class="store-badge" aria-hidden="true">0</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="store-icon-button" aria-label="{{ __('Cart, :count items', ['count' => $cartCount ?? 0]) }}">
                        <span aria-hidden="true">🛒</span>
                        <span class="store-badge" aria-hidden="true">{{ $cartCount ?? 0 }}</span>
                    </a>
                    <a href="{{ route('customer.login') }}" class="rounded-2xl border border-slate-200 px-4 py-3 transition hover:border-red-200 hover:text-red-700">{{ __('Login') }}</a>
                    <a href="{{ route('customer.register') }}" class="hidden rounded-2xl bg-slate-950 px-4 py-3 text-white transition hover:bg-red-700 sm:inline-flex">{{ __('Register') }}</a>
                @endauth
            </nav>
        </div>

        <div class="border-t border-slate-100" style="background-color: var(--store-nav-bg); border-color: var(--store-border)">
            <nav class="mx-auto flex max-w-7xl items-center gap-2 overflow-x-auto px-4 py-3 text-sm font-bold" aria-label="{{ __('Primary navigation') }}">
                <a href="{{ route('home') }}" class="store-nav-link">{{ __('Home') }}</a>
                <a href="{{ route('products.index') }}" class="store-nav-link">{{ __('Products') }}</a>
                <a href="{{ route('offers.index') }}" class="store-nav-link">{{ __('Offers') }}</a>
                <a href="{{ route('categories.index') }}" class="store-nav-pill"><span aria-hidden="true">▦</span>{{ __('Categories') }}</a>
                <a href="{{ route('brands.index') }}" class="store-nav-link">{{ __('Brands') }}</a>
                <a href="{{ route('products.latest') }}" class="store-nav-link">{{ __('New Arrivals') }}</a>
                <a href="{{ route('about') }}" class="store-nav-link">{{ __('About') }}</a>
                <a href="{{ route('join-us.create') }}" class="store-nav-link">{{ __('Join Us') }}</a>
                @auth
                    <form method="POST" action="{{ route('customer.logout') }}" class="ms-auto shrink-0">
                        @csrf
                        <button class="store-nav-link">{{ __('Logout') }}</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        @if (session('status'))
            <div class="mx-auto mt-5 max-w-7xl px-4">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status" aria-live="polite">{{ session('status') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="mx-auto mt-5 max-w-7xl px-4">
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert" aria-live="assertive">
                    <p class="font-black">{{ __('Please fix the following errors:') }}</p>
                    <ul class="mt-2 list-disc space-y-1 ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="store-footer mt-16" role="contentinfo" data-store-footer>
        <div class="store-footer-newsletter border-y">
            <div class="mx-auto grid max-w-7xl items-center gap-6 px-4 py-8 lg:grid-cols-[1fr_minmax(360px,520px)]">
                <div>
                    <p class="store-footer-newsletter-eyebrow text-sm font-black uppercase tracking-normal">{{ __('Newsletter') }}</p>
                    <h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ __('Stay close to the best offers') }}</h2>
                    <p class="store-footer-newsletter-text mt-3 max-w-2xl text-sm leading-7">{{ __('Get weekly deals, new arrivals, and wholesale updates in your inbox.') }}</p>
                </div>
                <form method="POST" action="{{ route('newsletter.store') }}" class="store-footer-newsletter-form flex min-w-0 flex-col gap-3 rounded-3xl p-2 shadow-2xl shadow-red-950/20 sm:flex-row" aria-label="{{ __('Subscribe to newsletter') }}">
                    @csrf
                    <label for="footer-newsletter-email" class="sr-only">{{ __('Email address') }}</label>
                    <input id="footer-newsletter-email" type="email" name="email" autocomplete="email" required placeholder="{{ __('Email address') }}" class="min-w-0 flex-1 rounded-2xl border border-white/15 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none placeholder:text-slate-400">
                    <button class="rounded-2xl bg-amber-400 px-6 py-3 text-sm font-black text-slate-950 transition hover:bg-amber-300">{{ __('Subscribe') }}</button>
                </form>
            </div>
        </div>

        <div class="store-footer-main mx-auto grid max-w-7xl gap-10 px-4 py-12 lg:grid-cols-[1.25fr_0.8fr_0.8fr_0.9fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3" aria-label="{{ __(':name home', ['name' => $siteName]) }}">
                    @if ($siteLogoUrl)
                        <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-12 w-12 rounded-2xl object-contain shadow-lg">
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-black shadow-lg shadow-red-900/30" style="background-color: var(--store-primary)" aria-hidden="true">{{ mb_substr($siteName, 0, 1) }}</span>
                    @endif
                    <span class="grid leading-tight">
                        <span class="store-footer-brand text-xl font-black">{{ $siteName }}</span>
                        <span class="store-footer-muted text-xs font-bold uppercase tracking-normal">{{ $siteTagline }}</span>
                    </span>
                </a>
                <p class="store-footer-muted mt-4 max-w-sm text-sm leading-7">{{ $siteDescription }}</p>
                <div class="mt-6">
                    <p class="store-footer-heading text-sm font-black">{{ __('Secure payments') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-black">
                        <span class="rounded-xl bg-white px-3 py-2 text-slate-950">Visa</span>
                        <span class="rounded-xl bg-red-600 px-3 py-2 text-white">MasterCard</span>
                        <span class="rounded-xl bg-amber-400 px-3 py-2 text-slate-950">PayPal</span>
                    </div>
                </div>
            </div>

            <nav aria-labelledby="footer-company-title">
                <h3 id="footer-company-title" class="store-footer-heading text-sm font-black uppercase tracking-normal">{{ __('Company') }}</h3>
                <div class="store-footer-links mt-4 grid gap-3 text-sm font-semibold">
                    <a class="transition" href="{{ route('about') }}">{{ __('About') }}</a>
                    <a class="transition" href="{{ route('contact') }}">{{ __('Contact') }}</a>
                    <a class="transition" href="{{ route('join-us.create') }}">{{ __('Join Us') }}</a>
                    <a class="transition" href="{{ route('brands.index') }}">{{ __('Brands') }}</a>
                    <a class="transition" href="{{ route('accessibility') }}">{{ __('Accessibility') }}</a>
                    <a class="transition" href="{{ route('sitemap.index') }}">{{ __('Sitemap') }}</a>
                </div>
            </nav>

            <nav aria-labelledby="footer-shop-title">
                <h3 id="footer-shop-title" class="store-footer-heading text-sm font-black uppercase tracking-normal">{{ __('Shop') }}</h3>
                <div class="store-footer-links mt-4 grid gap-3 text-sm font-semibold">
                    <a class="transition" href="{{ route('products.index') }}">{{ __('All Products') }}</a>
                    <a class="transition" href="{{ route('offers.index') }}">{{ __('Offers') }}</a>
                    <a class="transition" href="{{ route('categories.index') }}">{{ __('Categories') }}</a>
                    <a class="transition" href="{{ route('brands.index') }}">{{ __('Brands') }}</a>
                    <a class="transition" href="{{ route('products.latest') }}">{{ __('New Arrivals') }}</a>
                </div>
            </nav>

            <nav aria-labelledby="footer-account-title">
                <h3 id="footer-account-title" class="store-footer-heading text-sm font-black uppercase tracking-normal">{{ __('Account links') }}</h3>
                <div class="store-footer-links mt-4 grid gap-3 text-sm font-semibold">
                    @auth
                        <a class="transition" href="{{ route('account.dashboard') }}">{{ __('Account') }}</a>
                        <a class="transition" href="{{ route('orders.index') }}">{{ __('Orders') }}</a>
                        <a class="transition" href="{{ route('cart.index') }}">{{ __('Cart') }}</a>
                        <a class="transition" href="{{ route('favorites.index') }}">{{ __('Wishlist') }}</a>
                        <a class="transition" href="{{ route('account.addresses.index') }}">{{ __('Addresses') }}</a>
                    @else
                        <a class="transition" href="{{ route('customer.login') }}">{{ __('Login') }}</a>
                        <a class="transition" href="{{ route('customer.register') }}">{{ __('Register') }}</a>
                    @endauth
                </div>
            </nav>

            <div>
                <h3 class="store-footer-heading text-sm font-black uppercase tracking-normal">{{ __('Contact details') }}</h3>
                <div class="store-footer-links mt-4 grid gap-3 text-sm font-semibold">
                    @if ($siteContact['email'] ?? null)
                        <a class="transition" href="mailto:{{ $siteContact['email'] }}">{{ $siteContact['email'] }}</a>
                    @endif
                    @if ($siteContact['phone'] ?? null)
                        <a class="transition" href="tel:{{ preg_replace('/\s+/', '', $siteContact['phone']) }}">{{ $siteContact['phone'] }}</a>
                    @endif
                    @if ($siteContact['whatsapp'] ?? null)
                        <a class="transition" href="https://wa.me/{{ preg_replace('/\D+/', '', $siteContact['whatsapp']) }}">{{ __('WhatsApp') }}: {{ $siteContact['whatsapp'] }}</a>
                    @endif
                    @if ($siteContact['address'] ?? null)
                        <span>{{ $siteContact['address'] }}</span>
                    @endif
                    @if ($siteContact['working_hours'] ?? null)
                        <span>{{ $siteContact['working_hours'] }}</span>
                    @endif
                </div>
                <div class="mt-6">
                    <p class="store-footer-heading text-sm font-black">{{ __('Follow us') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-black">
                        @forelse ($siteSocial as $network => $url)
                            <a href="{{ $url }}" class="store-footer-social-link rounded-xl border px-3 py-2 transition" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::headline($network) }}</a>
                        @empty
                            <a href="{{ route('contact') }}" class="store-footer-social-link rounded-xl border px-3 py-2 transition">{{ __('Contact') }}</a>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="store-footer-trust border-y">
            <div class="store-footer-trust-grid mx-auto grid max-w-7xl gap-3 px-4 py-5 text-sm font-bold sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex items-center gap-3"><span class="text-red-400" aria-hidden="true">🚚</span>{{ __('Fast shipping') }}</div>
                <div class="flex items-center gap-3"><span class="text-red-400" aria-hidden="true">🔒</span>{{ __('Secure checkout') }}</div>
                <div class="flex items-center gap-3"><span class="text-red-400" aria-hidden="true">✓</span>{{ __('Original guarantee') }}</div>
                <div class="flex items-center gap-3"><span class="text-red-400" aria-hidden="true">☎</span>{{ __('Customer support') }}</div>
            </div>
        </div>

        <div class="store-footer-bottom mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-sm font-semibold sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $siteName }}. {{ __('All rights reserved.') }}</p>
            <div class="flex flex-wrap gap-4">
                <a class="transition" href="{{ route('sitemap.index') }}">{{ __('Sitemap') }}</a>
                <a class="transition" href="{{ route('accessibility') }}">{{ __('Accessibility') }}</a>
                <a class="transition" href="{{ route('contact') }}">{{ __('Support') }}</a>
                <a class="transition" href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a>
                <a class="transition" href="{{ route('returns') }}">{{ __('Returns Policy') }}</a>
                <a class="transition" href="{{ route('shipping.policy') }}">{{ __('Shipping Policy') }}</a>
                <a class="transition" href="{{ route('terms') }}">{{ __('Terms') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
