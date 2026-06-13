@extends('layouts.services')

@section('title', __('My service orders'))
@section('meta_description', __('Track your electronic service orders from AlBaik Services.'))
@section('canonical', route('services.orders.index'))

@section('content')
<section class="service-orders-page">
    <style>
        .service-orders-page{padding:28px 0 56px;background:#f8fafc}
        .service-orders-shell{width:min(1100px,calc(100% - 32px));margin-inline:auto}
        .service-orders-head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:18px}
        .service-orders-head h1{margin:0;font-size:clamp(28px,4vw,46px);font-weight:900;color:#0f172a}
        .service-orders-head p{margin:7px 0 0;color:#64748b;font-weight:800}
        .service-orders-list{display:grid;gap:12px}
        .service-order-card{display:grid;grid-template-columns:1fr auto;gap:18px;border:1px solid #e2e8f0;border-radius:20px;background:#fff;padding:18px;box-shadow:0 10px 30px rgba(15,23,42,.06)}
        .service-order-title{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px}
        .service-order-title strong{font-size:17px;font-weight:900;color:#0f172a}
        .service-order-title span,.service-order-status{border-radius:999px;padding:6px 10px;font-size:12px;font-weight:900}
        .service-order-title span{background:#f1f5f9;color:#475569}
        .service-order-status{background:#fffbeb;color:#92400e}
        .service-order-status.is-completed{background:#dcfce7;color:#166534}
        .service-order-status.is-cancelled,.service-order-status.is-refunded{background:#fee2e2;color:#991b1b}
        .service-order-meta{display:flex;gap:14px;flex-wrap:wrap;color:#64748b;font-size:13px;font-weight:800}
        .service-order-price{text-align:end}
        .service-order-price strong{display:block;color:#dc2626;font-size:20px;font-weight:900}
        .service-order-inputs{grid-column:1/-1;border-top:1px solid #f1f5f9;padding-top:12px;display:flex;gap:8px;flex-wrap:wrap}
        .service-order-inputs span{border:1px solid #e2e8f0;border-radius:999px;padding:7px 10px;color:#334155;font-size:12px;font-weight:800}
        .service-orders-empty{border:1px dashed #cbd5e1;border-radius:22px;background:#fff;padding:34px;text-align:center;color:#64748b;font-weight:900}
        .service-orders-empty a{display:inline-flex;margin-top:14px;border-radius:14px;background:#f59e0b;color:#111827;padding:11px 16px;font-weight:900}
        .service-orders-pagination{margin-top:18px}
        @media(max-width:720px){.service-orders-shell{width:min(100% - 20px,1100px)}.service-orders-head{display:grid}.service-order-card{grid-template-columns:1fr}.service-order-price{text-align:start}}
    </style>

    <div class="service-orders-shell">
        <nav class="store-breadcrumb mb-4" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('services.index') }}">{{ __('Electronic services') }}</a>
            <span>/</span>
            <span>{{ __('My service orders') }}</span>
        </nav>

        @if(session('status'))
            <div class="mb-4 rounded-2xl bg-emerald-50 p-4 font-black text-emerald-800">{{ session('status') }}</div>
        @endif

        <header class="service-orders-head">
            <div>
                <h1>{{ __('My service orders') }}</h1>
                <p>{{ __('Track processing, completion, and refunds for electronic services.') }}</p>
            </div>
            <a href="{{ route('services.index') }}" class="services-portal-button is-gold">{{ __('Order new service') }}</a>
        </header>

        @if($orders->count())
            <div class="service-orders-list">
                @foreach($orders as $order)
                    @php($statusClass = 'is-'.$order->status)
                    <article class="service-order-card">
                        <div>
                            <div class="service-order-title">
                                <strong>{{ $order->service?->name ?? data_get($order->service_snapshot, 'name.'.app()->getLocale(), __('Electronic service')) }}</strong>
                                <span>{{ $order->order_number }}</span>
                                <small class="service-order-status {{ $statusClass }}">{{ \App\Models\ElectronicServiceOrder::statusOptions()[$order->status] ?? $order->status }}</small>
                            </div>
                            <div class="service-order-meta">
                                <span>{{ __('Created at') }}: {{ $order->created_at?->format('Y-m-d H:i') }}</span>
                                <span>{{ __('Provider') }}: {{ $order->provider?->name ?? __('Store team') }}</span>
                                <span>{{ __('Payment status') }}: {{ \App\Models\ElectronicServiceOrder::paymentStatusOptions()[$order->payment_status] ?? $order->payment_status }}</span>
                            </div>
                        </div>
                        <div class="service-order-price">
                            <span>{{ __('Amount') }}</span>
                            <strong>{{ store_money((float) $order->amount) }}</strong>
                        </div>
                        @if($order->customer_inputs)
                            <div class="service-order-inputs">
                                @foreach($order->customer_inputs as $key => $value)
                                    <span>{{ $key }}: {{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
            <div class="service-orders-pagination">{{ $orders->links() }}</div>
        @else
            <div class="service-orders-empty">
                <div>{{ __('You have no electronic service orders yet.') }}</div>
                <a href="{{ route('services.index') }}">{{ __('Browse services') }}</a>
            </div>
        @endif
    </div>
</section>
@endsection
