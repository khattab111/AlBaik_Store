<?php

namespace App\Jobs;

use App\Models\ElectronicServiceOrder;
use App\Services\Providers\Services\ProviderOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckProviderOrdersStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ProviderOrderService $orders): void
    {
        ElectronicServiceOrder::query()
            ->with(['provider', 'user'])
            ->where('status', ElectronicServiceOrder::STATUS_PROCESSING)
            ->whereNotNull('electronic_service_provider_id')
            ->where(function ($query): void {
                $query->whereNotNull('provider_order_id')
                    ->orWhereNotNull('provider_reference');
            })
            ->latest()
            ->limit(100)
            ->get()
            ->each(fn (ElectronicServiceOrder $order) => $orders->checkStatus($order));
    }
}
