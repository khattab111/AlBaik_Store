@extends('layouts.app')

@section('title', __('About'))

@section('content')
<section class="store-section">
    <div class="grid items-center gap-8 overflow-hidden rounded-[2rem] bg-slate-950 p-8 text-white shadow-2xl shadow-slate-950/10 lg:grid-cols-[1.1fr_0.9fr] lg:p-10">
        <div>
            <p class="text-sm font-black text-amber-300">{{ __('Company') }}</p>
            <h1 class="mt-3 max-w-3xl text-4xl font-black leading-tight sm:text-5xl">{{ __('About AlBaik Store') }}</h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-200">{{ __('We provide original retail and wholesale products through a clear ordering experience, flexible payment, and shipping that fits cities and regions.') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="store-button-primary">{{ __('Browse Products') }}</a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white hover:text-slate-950">{{ __('Contact') }}</a>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['value' => '15k+', 'label' => __('Customers')],
                ['value' => '2.5k+', 'label' => __('Products')],
                ['value' => '350+', 'label' => __('Brands')],
                ['value' => '99%', 'label' => __('Customer Satisfaction')],
            ] as $stat)
                <div class="rounded-3xl bg-white/10 p-6">
                    <p class="text-4xl font-black text-amber-300">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm font-bold text-slate-200">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-3">
        @foreach([
            [__('Vision'), __('A trusted storefront that makes product discovery, ordering, and follow-up simple.')],
            [__('Mission'), __('Deliver original products with clear pricing and reliable customer support.')],
            [__('Values'), __('Quality, transparency, speed, and practical customer service.')],
        ] as [$title, $text])
            <article class="store-panel p-6 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-xl font-black text-red-700">{{ mb_substr($title, 0, 1) }}</div>
                <h2 class="text-xl font-black">{{ $title }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $text }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
            <p class="store-eyebrow">{{ __('Shopping journey') }}</p>
            <h2 class="mt-2 text-3xl font-black">{{ __('A clearer path from discovery to delivery') }}</h2>
            <p class="mt-4 leading-8 text-slate-600">{{ __('Every primary page is structured to help customers find products, understand trust signals, and complete orders without confusion.') }}</p>
        </div>
        <div class="grid gap-4">
            @foreach ([
                [__('Discover'), __('Browse products, offers, brands, and categories from one consistent storefront.')],
                [__('Order'), __('Use clear product cards, stock signals, and checkout summaries before placing an order.')],
                [__('Follow up'), __('Track orders, addresses, payment receipts, and support channels from the account area.')],
            ] as [$title, $text])
                <div class="store-panel grid gap-4 p-5 sm:grid-cols-[auto_1fr]">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg font-black text-white">{{ $loop->iteration }}</span>
                    <div>
                        <h3 class="font-black">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
