<?php

namespace App\Services\ElectronicServices;

use App\Models\ElectronicService;
use App\Models\ElectronicServiceOrder;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Providers\Services\ProviderOrderService;
use App\Services\WalletService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ElectronicServiceOrderService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly ProviderOrderService $providerOrders,
    ) {
    }

    public function create(User $user, ElectronicService $service, array $inputs): ElectronicServiceOrder
    {
        if (! $service->is_active || ! $service->category?->is_active) {
            throw ValidationException::withMessages([
                'service' => __('This service is currently unavailable.'),
            ]);
        }

        $cleanInputs = $this->sanitizeInputs($service, $inputs);
        $amount = round($service->priceForUser($user), 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'service' => __('Service price must be greater than zero.'),
            ]);
        }

        if ($service->min_amount !== null && $amount < (float) $service->min_amount) {
            throw ValidationException::withMessages(['service' => __('Service amount is below the minimum allowed.')]);
        }

        if ($service->max_amount !== null && $amount > (float) $service->max_amount) {
            throw ValidationException::withMessages(['service' => __('Service amount is above the maximum allowed.')]);
        }

        return DB::transaction(function () use ($user, $service, $cleanInputs, $amount): ElectronicServiceOrder {
            $order = ElectronicServiceOrder::create([
                'user_id' => $user->id,
                'electronic_service_id' => $service->id,
                'electronic_service_provider_id' => $service->electronic_service_provider_id,
                'service_snapshot' => $this->serviceSnapshot($service),
                'customer_inputs' => $cleanInputs,
                'input_data' => $cleanInputs,
                'amount' => $amount,
                'total' => $amount,
                'cost' => (float) $service->cost,
                'provider_cost_at_order' => (float) ($service->provider_cost_price ?: $service->cost),
                'selling_price_at_order' => $amount,
                'profit_at_order' => max(0, $amount - (float) ($service->provider_cost_price ?: $service->cost)),
                'quantity' => 1,
                'execution_type' => $service->service_type,
                'status' => ElectronicServiceOrder::STATUS_PENDING,
                'payment_status' => ElectronicServiceOrder::PAYMENT_PAID,
            ]);

            try {
                $transaction = $this->walletService->debit(
                    $user,
                    $amount,
                    WalletTransaction::TYPE_PURCHASE,
                    $order,
                    __('Electronic service order #:number', ['number' => $order->order_number]),
                    ['service_id' => $service->id, 'service_order_id' => $order->id],
                );
            } catch (DomainException $exception) {
                throw ValidationException::withMessages([
                    'wallet' => $exception->getMessage(),
                ]);
            }

            $order->forceFill(['wallet_transaction_id' => $transaction->id])->save();

            $order = $order->refresh();

            if ($service->provider?->isApiProvider()) {
                $this->providerOrders->submit($order);
                $order->refresh();
            }

            return $order;
        });
    }

    private function sanitizeInputs(ElectronicService $service, array $inputs): array
    {
        $clean = [];

        foreach ($service->visibleFields() as $field) {
            $name = (string) $field['name'];
            $label = $field['label'] ?? $name;
            $required = (bool) ($field['required'] ?? false);
            $value = $inputs[$name] ?? null;

            if ($required && blank($value)) {
                throw ValidationException::withMessages([
                    "fields.{$name}" => __(':field is required.', ['field' => $label]),
                ]);
            }

            if (blank($value)) {
                continue;
            }

            $clean[$name] = is_scalar($value) ? trim((string) $value) : $value;
        }

        return $clean;
    }

    private function serviceSnapshot(ElectronicService $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->getTranslations('name'),
            'slug' => $service->slug,
            'price' => (float) $service->price,
            'wholesale_price' => (float) $service->wholesale_price,
            'cost' => (float) $service->cost,
            'provider_cost_price' => (float) $service->provider_cost_price,
            'provider_service_id' => $service->provider_service_id,
            'service_type' => $service->service_type,
            'provider_id' => $service->electronic_service_provider_id,
            'provider_name' => $service->provider?->getTranslations('name'),
            'fields_schema' => $service->fields_schema,
        ];
    }
}
