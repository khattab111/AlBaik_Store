@extends('layouts.app')
@section('title', __('Brands'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10"><h1 class="mb-6 text-3xl font-bold">{{ __('Brands') }}</h1><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($brands as $brand)<a href="{{ route('brands.show',$brand->slug) }}" class="rounded-xl border bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">@if($brand->logo)<img src="{{ asset('storage/'.$brand->logo) }}" class="mx-auto mb-4 h-20 object-contain">@endif<h2 class="font-bold">{{ $brand->name }}</h2><p class="text-sm text-slate-500">{{ $brand->products_count }} {{ __('Products') }}</p></a>@empty<p>{{ __('No brands found.') }}</p>@endforelse</div><div class="mt-8">{{ $brands->links() }}</div></section>
@endsection
