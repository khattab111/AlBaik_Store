<?php

namespace App\Services\Providers\Services;

use App\Models\ElectronicServiceOrder;
use App\Models\WalletTransaction;
use App\Services\Providers\DTOs\ProviderResponse;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class ProviderOrderService
{
    public function __construct(
        private readonly ProviderManager $providers,
        private readonly WalletService $wallets,
    ) {
    }

    public function submit(ElectronicServiceOrder $order): ProviderResponse
    {
        $order->loadMissing(['provider', 'service', 'user']);
        $provider = $order->provider;

        if (! $provider) {
            return ProviderResponse::failure(__('Service provider is missing.'));
        }

        $response = $this->providers->gateway($provider)->createOrder($order);
        $status = $this->mapStatus($provider->status_mapping ?? [], $response->providerStatus, $response->successful);

        DB::transaction(function () use ($order, $response, $status): void {
            $locked = ElectronicServiceOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'provider_order_id' => $response->providerOrderId ?: $locked->provider_order_id,
                'provider_reference' => $response->providerOrderId ?: $locked->provider_reference,
                'provider_status' => $response->providerStatus,
                'provider_response' => $response->data ?: null,
                'status' => $status,
                'failure_reason' => $response->successful ? null : $response->message,
                'processed_at' => now(),
            ]);

            if (! $response->successful || $status === ElectronicServiceOrder::STATUS_FAILED) {
                $this->refundIfNeeded($locked, $response->message);
            }
        });

        return $response;
    }

    public function checkStatus(ElectronicServiceOrder $order): ProviderResponse
    {
        $order->loadMissing(['provider', 'user']);

        if (! $order->provider) {
            return ProviderResponse::failure(__('Service provider is missing.'));
        }

        $response = $this->providers->gateway($order->provider)->checkOrderStatus($order);
        $status = $this->mapStatus($order->provider->status_mapping ?? [], $response->providerStatus, $response->successful);

        DB::transaction(function () use ($order, $response, $status): void {
            $locked = ElectronicServiceOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $updates = [
                'provider_status' => $response->providerStatus,
                'provider_response' => $response->data ?: $locked->provider_response,
            ];

            if ($status !== ElectronicServiceOrder::STATUS_PROCESSING) {
                $updates['status'] = $status;
            }

            if ($status === ElectronicServiceOrder::STATUS_COMPLETED) {
                $updates['completed_at'] = now();
            }

            if ($status === ElectronicServiceOrder::STATUS_FAILED) {
                $updates['failure_reason'] = $response->message;
            }

            $locked->update($updates);

            if ($status === ElectronicServiceOrder::STATUS_FAILED) {
                $this->refundIfNeeded($locked, $response->message);
            }
        });

        return $response;
    }

    private function mapStatus(array $mapping, ?string $providerStatus, bool $successful): string
    {
        if ($providerStatus && isset($mapping[$providerStatus])) {
            return $mapping[$providerStatus];
        }

        return $successful ? ElectronicServiceOrder::STATUS_PROCESSING : ElectronicServiceOrder::STATUS_FAILED;
    }

    private function refundIfNeeded(ElectronicServiceOrder $order, ?string $reason = null): void
    {
        if ($order->payment_status === ElectronicServiceOrder::PAYMENT_REFUNDED) {
            return;
        }

        $this->wallets->refund(
            $order->user,
            (float) $order->amount,
            $order,
            $reason ?: __('Provider order failed and was refunded.'),
        );

        $order->update([
            'payment_status' => ElectronicServiceOrder::PAYMENT_REFUNDED,
            'status' => ElectronicServiceOrder::STATUS_REFUNDED,
            'cancelled_at' => now(),
        ]);
    }
}
