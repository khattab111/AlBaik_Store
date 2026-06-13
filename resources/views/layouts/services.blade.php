@php
    $siteIdentity = $siteIdentity ?? [];
    $siteName = $siteIdentity['name'] ?? __('AlBaik Store');
    $siteTagline = $siteIdentity['tagline'] ?? __('Premium Market');
    $siteFavicon = $siteIdentity['favicon'] ?? null;
    $siteFaviconUrl = $siteFavicon && file_exists(public_path('storage/'.$siteFavicon)) ? asset('storage/'.$siteFavicon) : null;
    $currentLocale = $currentLocale ?? app()->getLocale();
    $textDirection = $textDirection ?? (in_array($currentLocale, ['ar', 'fa', 'ur'], true) ? 'rtl' : 'ltr');
    $wallet = auth()->user()?->wallet;
    $walletBalance = (float) ($wallet?->balance ?? 0);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" dir="{{ $textDirection }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Electronic services'))</title>
    <meta name="description" content="@yield('meta_description', __('Electronic services center'))">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--services-black:#090b10;--services-gold:#f59e0b;--services-border:#e2e8f0;--services-muted:#64748b;--services-bg:#f8fafc}
        *{box-sizing:border-box}
        body{margin:0;background:var(--services-bg);color:#0f172a;font-family:Cairo,Tajawal,Inter,system-ui,sans-serif}
        a{text-decoration:none;color:inherit}
        .services-portal-header{position:sticky;top:0;z-index:50;border-bottom:1px solid var(--services-border);background:rgba(255,255,255,.94);backdrop-filter:blur(16px)}
        .services-portal-shell{width:min(1220px,calc(100% - 32px));margin-inline:auto}
        .services-portal-top{display:grid;grid-template-columns:auto 1fr auto;gap:16px;align-items:center;padding:14px 0}
        .services-portal-brand{display:flex;align-items:center;gap:12px;min-width:0}
        .services-portal-logo{display:grid;place-items:center;width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#fbbf24,#f59e0b);font-weight:900;color:#111827;box-shadow:0 10px 26px rgba(245,158,11,.22)}
        .services-portal-brand strong{display:block;font-size:18px;font-weight:900;line-height:1.1}
        .services-portal-brand span{display:block;color:var(--services-muted);font-size:12px;font-weight:800}
        .services-portal-badge{justify-self:center;border:1px solid #fde68a;background:#fffbeb;color:#92400e;border-radius:999px;padding:9px 14px;font-size:13px;font-weight:900}
        .services-portal-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px}
        .services-portal-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;border:1px solid var(--services-border);border-radius:14px;background:#fff;padding:10px 14px;font-size:13px;font-weight:900;white-space:nowrap}
        .services-portal-button.is-dark{border-color:#111827;background:#111827;color:#fff}
        .services-portal-button.is-gold{border-color:#f59e0b;background:#f59e0b;color:#111827}
        .services-portal-nav{display:flex;align-items:center;gap:8px;overflow-x:auto;padding:0 0 12px}
        .services-portal-nav a{border:1px solid transparent;border-radius:999px;padding:10px 14px;color:#334155;font-size:13px;font-weight:900;white-space:nowrap}
        .services-portal-nav a.is-active{border-color:#111827;background:#111827;color:#fff}
        .services-portal-main{min-height:calc(100vh - 160px)}
        .services-portal-footer{border-top:1px solid var(--services-border);background:#fff;padding:20px 0;color:#64748b;font-size:13px;font-weight:800}
        .store-breadcrumb{display:flex;align-items:center;gap:8px;color:#64748b;font-size:13px;font-weight:800;flex-wrap:wrap}
        .store-breadcrumb a{color:#0f172a}
        @media(max-width:760px){.services-portal-shell{width:min(100% - 20px,1220px)}.services-portal-top{grid-template-columns:1fr;gap:10px}.services-portal-badge{justify-self:start}.services-portal-actions{justify-content:start;overflow-x:auto}.services-portal-button{padding-inline:12px}.services-portal-brand strong{font-size:16px}}
    </style>
    @stack('styles')
</head>
<body>
    <header class="services-portal-header">
        <div class="services-portal-shell">
            <div class="services-portal-top">
                <a href="{{ route('services.index') }}" class="services-portal-brand" aria-label="{{ __('Electronic services') }}">
                    <span class="services-portal-logo" aria-hidden="true">⚡</span>
                    <span>
                        <strong>{{ __('AlBaik Services') }}</strong>
                        <span>{{ __('Electronic services center') }}</span>
                    </span>
                </a>

                <div class="services-portal-badge">
                    {{ __('Wallet balance') }}:
                    {{ auth()->check() ? store_money($walletBalance) : __('Login required') }}
                </div>

                <nav class="services-portal-actions" aria-label="{{ __('Service account navigation') }}">
                    @auth
                        <a href="{{ route('account.wallet.index') }}" class="services-portal-button is-gold">{{ __('Charge wallet') }}</a>
                        <a href="{{ route('services.orders.index') }}" class="services-portal-button">{{ __('My service orders') }}</a>
                    @else
                        <a href="{{ route('customer.login') }}" class="services-portal-button is-dark">{{ __('Login') }}</a>
                    @endauth
                    <a href="{{ route('home') }}" class="services-portal-button">{{ __('Back to store') }}</a>
                </nav>
            </div>

            <nav class="services-portal-nav" aria-label="{{ __('Electronic services navigation') }}">
                <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.index', 'services.show') ? 'is-active' : '' }}">{{ __('Services') }}</a>
                @auth
                    <a href="{{ route('services.orders.index') }}" class="{{ request()->routeIs('services.orders.index') ? 'is-active' : '' }}">{{ __('My service orders') }}</a>
                    <a href="{{ route('account.wallet.index') }}">{{ __('Wallet') }}</a>
                @endauth
                <a href="{{ route('products.index') }}">{{ __('Products store') }}</a>
                <a href="{{ route('offers.index') }}">{{ __('Store offers') }}</a>
            </nav>
        </div>
    </header>

    <main class="services-portal-main">
        @yield('content')
    </main>

    <footer class="services-portal-footer">
        <div class="services-portal-shell">
            {{ $siteName }} · {{ __('Electronic services are processed through the same secure account and wallet.') }}
        </div>
    </footer>
</body>
</html>
