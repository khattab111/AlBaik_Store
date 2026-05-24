@extends('layouts.app')
@section('title', __('Shipping Policy'))
@section('meta_description', __('Shipping methods, city coverage, fees, and delivery expectations.'))
@section('content')
<section class="store-section"><article class="store-panel mx-auto max-w-4xl p-8"><h1 class="text-4xl font-black">{{ __('Shipping Policy') }}</h1><p class="mt-5 leading-8 text-slate-600">{{ __('Shipping cost is calculated during checkout based on country, city, town, shipping method, order value, and product weight when applicable.') }}</p><div class="mt-8 grid gap-5 md:grid-cols-3">@foreach([__('Free shipping rules'), __('Fixed city fees'), __('Weight based fees')] as $title)<section class="rounded-3xl bg-slate-50 p-5"><h2 class="font-black">{{ $title }}</h2><p class="mt-2 text-sm leading-7 text-slate-600">{{ __('The admin can manage these rules from the shipping section in Filament.') }}</p></section>@endforeach</div></article></section>
@endsection
