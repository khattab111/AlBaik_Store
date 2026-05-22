@extends('layouts.app')
@section('title', __('Categories'))
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10"><h1 class="mb-6 text-3xl font-bold">{{ __('Categories') }}</h1><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($categories as $category)<a href="{{ route('categories.show',$category->slug) }}" class="rounded-xl border bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="font-bold">{{ $category->name }}</h2><p class="mt-2 text-sm text-slate-500">{{ $category->products_count }} {{ __('Products') }}</p><p class="mt-3 line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ $category->description }}</p></a>@empty<p>{{ __('No categories found.') }}</p>@endforelse</div><div class="mt-8">{{ $categories->links() }}</div></section>
@endsection
