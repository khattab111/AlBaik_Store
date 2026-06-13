@extends('layouts.services')

@section('title', $service->name)
@section('meta_description', $service->description ?: __('Electronic service from AlBaik Store.'))
@section('canonical', route('services.show', $service->slug))

@section('content')
@php
    $wallet = auth()->user()?->wallet;
    $walletBalance = (float) ($wallet?->balance ?? 0);
    $canPay = auth()->check() && $wallet && $wallet->isActive() && $walletBalance >= (float) $service->price;
@endphp
<section class="service-detail-page">
    <style>
        .service-detail-page{background:#f8fafc;padding:28px 0 56px}
        .service-detail-shell{width:min(1100px,calc(100% - 32px));margin-inline:auto}
        .service-detail-grid{display:grid;grid-template-columns:1fr 420px;gap:18px;align-items:start}
        .service-detail-main,.service-order-panel{border:1px solid #e2e8f0;border-radius:24px;background:#fff;box-shadow:0 12px 34px rgba(15,23,42,.07)}
        .service-detail-main{padding:28px}
        .service-kicker{display:inline-flex;border-radius:999px;background:#fffbeb;color:#b45309;padding:8px 12px;font-weight:900;margin-bottom:14px}
        .service-detail-main h1{font-size:clamp(30px,4vw,52px);font-weight:900;line-height:1.08;margin:0 0 12px;color:#0f172a}
        .service-detail-main p{font-weight:700;color:#64748b;line-height:1.9}
        .service-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:22px}
        .service-stats div{border:1px solid #edf2f7;border-radius:18px;padding:14px;background:#f8fafc}
        .service-stats span{display:block;color:#64748b;font-size:12px;font-weight:800}
        .service-stats strong{display:block;color:#0f172a;font-weight:900;margin-top:4px}
        .service-order-panel{padding:22px;position:sticky;top:128px}
        .service-price{display:flex;align-items:baseline;justify-content:space-between;gap:12px;border-bottom:1px solid #f1f5f9;padding-bottom:16px;margin-bottom:16px}
        .service-price strong{font-size:28px;font-weight:900;color:#dc2626}
        .service-form-field{display:grid;gap:7px;margin-bottom:13px}
        .service-form-field label{font-weight:900;color:#0f172a}
        .service-form-field input,.service-form-field textarea,.service-form-field select{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:12px 13px;background:#fff;font-weight:700;outline:none}
        .service-form-field textarea{min-height:96px;resize:vertical}
        .service-submit{width:100%;border:0;border-radius:16px;background:#f59e0b;color:#111827;padding:14px 18px;font-weight:900;cursor:pointer}
        .service-submit:disabled{opacity:.55;cursor:not-allowed}
        .service-login,.service-alert{display:block;border-radius:16px;padding:13px 14px;font-weight:900;text-align:center}
        .service-login{background:#111827;color:#fff}
        .service-alert{background:#fff7ed;color:#9a3412;margin-bottom:14px;text-align:start}
        @media (max-width:900px){.service-detail-grid{grid-template-columns:1fr}.service-order-panel{position:static}.service-stats{grid-template-columns:1fr}}
    </style>

    <div class="service-detail-shell">
        <nav class="store-breadcrumb mb-4" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('Home') }}</a>
            <span>/</span>
            <a href="{{ route('services.index') }}">{{ __('Electronic services') }}</a>
            <span>/</span>
            <span>{{ $service->name }}</span>
        </nav>

        @if(session('status'))
            <div class="service-alert">{{ session('status') }}</div>
        @endif

        <div class="service-detail-grid">
            <article class="service-detail-main">
                <span class="service-kicker">{{ $service->category?->icon ?: '⚡' }} {{ $service->category?->name }}</span>
                <h1>{{ $service->name }}</h1>
                <p>{{ $service->description ?: __('This electronic service is processed by the store team after successful wallet payment.') }}</p>

                @if($service->instructions)
                    <div class="mt-6 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm font-bold leading-8 text-amber-900">
                        {!! nl2br(e($service->instructions)) !!}
                    </div>
                @endif

                <div class="service-stats">
                    <div><span>{{ __('Price') }}</span><strong>{{ store_money((float) $service->price) }}</strong></div>
                    <div><span>{{ __('Fulfillment') }}</span><strong>{{ $service->service_type === 'api' ? __('API fulfillment') : __('Manual fulfillment') }}</strong></div>
                    <div><span>{{ __('Provider') }}</span><strong>{{ $service->provider?->name ?? __('Store team') }}</strong></div>
                </div>
            </article>

            <aside class="service-order-panel">
                <div class="service-price">
                    <span>{{ __('Service total') }}</span>
                    <strong>{{ store_money((float) $service->price) }}</strong>
                </div>

                @guest
                    <a href="{{ route('customer.login') }}" class="service-login">{{ __('Login to order this service') }}</a>
                @else
                    @if(! $canPay)
                        <div class="service-alert">
                            {{ __('Your wallet balance is :balance. Please charge your wallet before ordering.', ['balance' => store_money($walletBalance)]) }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('services.orders.store', $service->slug) }}">
                        @csrf
                        @foreach($service->visibleFields() as $field)
                            @php
                                $name = $field['name'];
                                $type = $field['type'] ?? 'text';
                                $label = $field['label'] ?? $name;
                                $required = (bool) ($field['required'] ?? false);
                                $options = collect(explode(',', (string) ($field['options'] ?? '')))->map(fn ($option) => trim($option))->filter();
                            @endphp
                            <div class="service-form-field">
                                <label for="field-{{ $name }}">{{ $label }} @if($required)<span class="text-red-600">*</span>@endif</label>
                                @if($type === 'textarea')
                                    <textarea id="field-{{ $name }}" name="fields[{{ $name }}]" @required($required)>{{ old("fields.{$name}") }}</textarea>
                                @elseif($type === 'select')
                                    <select id="field-{{ $name }}" name="fields[{{ $name }}]" @required($required)>
                                        <option value="">{{ __('Choose') }}</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option }}" @selected(old("fields.{$name}") === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="field-{{ $name }}" type="{{ in_array($type, ['email','number','url','tel'], true) ? $type : 'text' }}" name="fields[{{ $name }}]" value="{{ old("fields.{$name}") }}" @required($required)>
                                @endif
                                @error("fields.{$name}")<small class="text-red-600">{{ $message }}</small>@enderror
                            </div>
                        @endforeach

                        @if(count($service->visibleFields()) === 0)
                            <div class="service-alert">{{ __('This service does not require extra information. The admin team will process it after payment.') }}</div>
                        @endif

                        @error('wallet')<div class="service-alert">{{ $message }}</div>@enderror
                        @error('service')<div class="service-alert">{{ $message }}</div>@enderror

                        <button class="service-submit" @disabled(! $canPay)>{{ __('Pay from wallet and submit') }}</button>
                    </form>
                @endguest
            </aside>
        </div>
    </div>
</section>
@endsection
