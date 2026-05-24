@extends('layouts.app')
@section('title', __('Terms and Conditions'))
@section('meta_description', __('Terms for using AlBaik Store and placing orders.'))
@section('content')
<section class="store-section"><article class="store-panel mx-auto max-w-4xl p-8"><h1 class="text-4xl font-black">{{ __('Terms and Conditions') }}</h1><p class="mt-5 leading-8 text-slate-600">{{ __('By using the store, creating an account, or placing an order, customers agree to provide accurate information and follow payment, shipping, and return rules shown during checkout and in order communications.') }}</p><div class="mt-8 grid gap-5">@foreach([__('Orders'), __('Payments'), __('Shipping'), __('Account security')] as $title)<section><h2 class="text-xl font-black">{{ $title }}</h2><p class="mt-2 text-sm leading-7 text-slate-600">{{ __('Store policies may be updated to improve service quality and comply with operational requirements.') }}</p></section>@endforeach</div></article></section>
@endsection
