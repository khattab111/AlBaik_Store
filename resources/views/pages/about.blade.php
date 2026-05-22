@extends('layouts.app')
@section('title', __('About'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12"><h1 class="text-4xl font-black">{{ __('About') }} متجر البيك</h1><p class="mt-5 text-lg text-slate-600 dark:text-slate-300">نحن متجر متخصص بتوفير منتجات أصلية للتجزئة والجملة، مع تجربة طلب واضحة، دفع مرن، وشحن يناسب المدن والمناطق.</p><div class="mt-10 grid gap-5 md:grid-cols-3">@foreach([__('Vision'),__('Mission'),__('Values')] as $item)<div class="rounded-xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><h2 class="font-bold">{{ $item }}</h2><p class="mt-3 text-sm text-slate-600 dark:text-slate-300">الجودة، الشفافية، والسرعة في خدمة العملاء.</p></div>@endforeach</div></section>
@endsection
