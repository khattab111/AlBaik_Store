@extends('layouts.app')
@section('title', __('Wishlist'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10"><h1 class="mb-6 text-3xl font-bold">{{ __('Wishlist') }}</h1><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($items as $item)<div>@include('partials.product-card',['product'=>$item->product])<form method="POST" action="{{ route('favorites.toggle',$item->product) }}" class="mt-2">@csrf<button class="w-full rounded-lg border bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">{{ __('Remove') }}</button></form></div>@empty<p>{{ __('No favorite products.') }}</p>@endforelse</div><div class="mt-8">{{ $items->links() }}</div></section>
@endsection
