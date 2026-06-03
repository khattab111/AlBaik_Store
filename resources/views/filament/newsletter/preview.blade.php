<div class="space-y-4">
    <div>
        <div class="text-sm font-bold text-gray-500">{{ __('Subject') }}</div>
        <div class="text-lg font-black">{{ $subject }}</div>
    </div>
    @if ($preheader)
        <div>
            <div class="text-sm font-bold text-gray-500">{{ __('Preheader') }}</div>
            <div>{{ $preheader }}</div>
        </div>
    @endif
    <div class="rounded-lg border bg-white p-4">
        {!! $content !!}
    </div>
</div>
