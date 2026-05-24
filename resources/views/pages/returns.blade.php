@extends('layouts.app')
@section('title', __('Returns Policy'))
@section('meta_description', __('Return and exchange rules for AlBaik Store orders.'))
@section('content')
<section class="store-section"><article class="store-panel mx-auto max-w-4xl p-8"><h1 class="text-4xl font-black">{{ __('Returns Policy') }}</h1><p class="mt-5 leading-8 text-slate-600">{{ __('Customers can request return or exchange review according to product condition, order status, and store approval. Some product categories may be non-returnable after opening.') }}</p><ul class="mt-8 grid gap-3 text-sm font-bold text-slate-700"><li>{{ __('Keep the invoice and payment receipt.') }}</li><li>{{ __('Contact support before sending any product back.') }}</li><li>{{ __('Refunds are reviewed after receiving and inspecting the product.') }}</li></ul></article></section>
@endsection
