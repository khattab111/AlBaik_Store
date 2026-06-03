<x-filament-panels::page>
    @php($stats = $this->stats())

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            __('Total subscribers') => $stats['total_subscribers'],
            __('Active subscribers') => $stats['active_subscribers'],
            __('Unsubscribed') => $stats['unsubscribed'],
            __('Sent campaigns') => $stats['sent_campaigns'],
            __('Success rate') => $stats['success_rate'].'%',
        ] as $label => $value)
            <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900">
                <div class="text-sm font-bold text-gray-500">{{ $label }}</div>
                <div class="mt-2 text-3xl font-black">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900">
            <h3 class="text-lg font-black">{{ __('Top sources') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($stats['sources'] as $source => $total)
                    <div class="flex items-center justify-between text-sm">
                        <span>{{ \App\Models\NewsletterSubscriber::sourceOptions()[$source] ?? $source }}</span>
                        <strong>{{ $total }}</strong>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No data available.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900">
            <h3 class="text-lg font-black">{{ __('Latest campaigns') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($stats['latest_campaigns'] as $campaign)
                    <div class="text-sm">
                        <div class="font-bold">{{ $campaign->title }}</div>
                        <div class="text-gray-500">{{ \App\Models\NewsletterCampaign::statusOptions()[$campaign->status] ?? $campaign->status }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No campaigns found.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900">
            <h3 class="text-lg font-black">{{ __('Latest subscribers') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($stats['latest_subscribers'] as $subscriber)
                    <div class="text-sm">
                        <div class="font-bold">{{ $subscriber->email }}</div>
                        <div class="text-gray-500">{{ $subscriber->created_at?->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No subscribers found.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
