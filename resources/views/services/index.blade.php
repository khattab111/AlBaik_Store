@extends('layouts.services')

@section('title', __('Electronic services'))
@section('meta_description', __('Buy digital and electronic services from AlBaik Store using your wallet balance.'))
@section('canonical', route('services.index'))

@section('content')
<section class="services-page">
    <style>
        .services-page{background:#f8fafc;padding:28px 0 56px}
        .services-shell{width:min(1180px,calc(100% - 32px));margin-inline:auto}
        .services-hero{display:grid;grid-template-columns:1.35fr .65fr;gap:18px;align-items:center;border:1px solid #e5e7eb;border-radius:24px;background:linear-gradient(135deg,#08090b,#181a20 62%,#f0a500);color:#fff;padding:30px;box-shadow:0 18px 45px rgba(15,23,42,.12)}
        .services-hero h1{font-size:clamp(30px,4vw,54px);font-weight:900;line-height:1.05;margin:0 0 12px}
        .services-hero p{margin:0;color:#d1d5db;font-weight:700;max-width:660px}
        .services-hero-badge{display:inline-flex;border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:8px 14px;margin-bottom:14px;color:#fbbf24;font-weight:900}
        .services-hero-card{border:1px solid rgba(255,255,255,.18);border-radius:22px;background:rgba(255,255,255,.08);padding:20px}
        .services-hero-card strong{display:block;font-size:34px;font-weight:900;color:#fbbf24}
        .services-categories{display:flex;gap:10px;overflow-x:auto;padding:18px 2px}
        .services-categories a{white-space:nowrap;border:1px solid #e2e8f0;background:#fff;border-radius:16px;padding:12px 16px;font-weight:900;color:#111827}
        .services-categories a.is-active{border-color:#f59e0b;background:#fffbeb;color:#b45309}
        .services-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
        .service-card{border:1px solid #e2e8f0;border-radius:20px;background:#fff;padding:18px;box-shadow:0 10px 28px rgba(15,23,42,.06);transition:.2s}
        .service-card:hover{transform:translateY(-3px);border-color:#f59e0b}
        .service-icon{display:grid;place-items:center;width:54px;height:54px;border-radius:18px;background:#fffbeb;color:#d97706;font-size:24px;margin-bottom:14px}
        .service-card h2{margin:0 0 8px;font-size:18px;font-weight:900;color:#0f172a;line-height:1.35}
        .service-card p{margin:0 0 16px;color:#64748b;font-weight:700;font-size:13px;line-height:1.7;min-height:44px}
        .service-meta{display:flex;align-items:center;justify-content:space-between;gap:10px;border-top:1px solid #f1f5f9;padding-top:14px}
        .service-meta strong{font-size:17px;font-weight:900;color:#dc2626}
        .service-meta span{border-radius:999px;background:#f1f5f9;color:#475569;font-size:12px;font-weight:900;padding:7px 10px}
        .services-empty{border:1px dashed #cbd5e1;border-radius:20px;background:#fff;padding:32px;text-align:center;font-weight:900;color:#64748b}
        .services-pagination{margin-top:20px}
        @media (max-width:1024px){.services-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.services-hero{grid-template-columns:1fr}}
        @media (max-width:720px){.services-page{padding-top:16px}.services-shell{width:min(100% - 20px,1180px)}.services-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.service-card{padding:14px;border-radius:16px}.services-hero{padding:22px;border-radius:20px}.services-hero-card{display:none}}
    </style>

    <div class="services-shell">
        <nav class="store-breadcrumb mb-4" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('Home') }}</a>
            <span>/</span>
            <span>{{ __('Electronic services') }}</span>
        </nav>

        <section class="services-hero">
            <div>
                <span class="services-hero-badge">{{ __('Wallet powered services') }}</span>
                <h1>{{ __('Electronic services') }}</h1>
                <p>{{ __('Charge accounts, request digital services, and pay securely from your AlBaik wallet while the team processes your order from the admin panel.') }}</p>
            </div>
            <div class="services-hero-card">
                <span>{{ __('Available services') }}</span>
                <strong>{{ $services->total() }}</strong>
                <small>{{ __('More services can be added from Filament at any time.') }}</small>
            </div>
        </section>

        <nav class="services-categories" aria-label="{{ __('Service categories') }}">
            <a href="{{ route('services.index') }}" class="{{ blank($categorySlug) ? 'is-active' : '' }}">{{ __('All') }}</a>
            @foreach($categories as $category)
                <a href="{{ route('services.index', ['category' => $category->slug]) }}" class="{{ $categorySlug === $category->slug ? 'is-active' : '' }}">
                    {{ $category->icon ?: '▣' }} {{ $category->name }} ({{ $category->services_count }})
                </a>
            @endforeach
        </nav>

        @if($services->count())
            <div class="services-grid">
                @foreach($services as $service)
                    <a href="{{ route('services.show', $service->slug) }}" class="service-card">
                        <span class="service-icon" aria-hidden="true">{{ $service->category?->icon ?: '⚡' }}</span>
                        <h2>{{ $service->name }}</h2>
                        <p>{{ $service->description ?: __('Digital service processed after wallet payment.') }}</p>
                        <div class="service-meta">
                            <strong>{{ store_money((float) $service->price) }}</strong>
                            <span>{{ $service->provider?->name ?? __('Manual') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="services-pagination">{{ $services->links() }}</div>
        @else
            <div class="services-empty">{{ __('No electronic services are available right now.') }}</div>
        @endif
    </div>
</section>
@endsection
