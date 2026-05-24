@extends('layouts.app')
@section('title', __('Privacy Policy'))
@section('meta_description', __('How AlBaik Store handles customer account, order, shipping, and payment information.'))
@section('content')
<section class="store-section"><article class="store-panel mx-auto max-w-4xl p-8"><h1 class="text-4xl font-black">{{ __('Privacy Policy') }}</h1><p class="mt-5 leading-8 text-slate-600">{{ __('We collect only the information needed to process orders, provide support, arrange shipping, and improve the shopping experience. Customer data is not sold to third parties.') }}</p><div class="mt-8 grid gap-5">@foreach([__('Account and contact data'), __('Order and payment review data'), __('Shipping address data'), __('Support messages')] as $title)<section><h2 class="text-xl font-black">{{ $title }}</h2><p class="mt-2 text-sm leading-7 text-slate-600">{{ __('This information is used for store operations, customer service, fraud prevention, and legal compliance where required.') }}</p></section>@endforeach</div></article></section>
@endsection
