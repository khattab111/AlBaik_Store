<?php

namespace App\Services\Providers\Services;

use App\Models\ElectronicService;
use App\Models\ElectronicServiceCategory;
use App\Models\ElectronicServiceProvider;
use App\Services\Providers\DTOs\ProviderServiceDTO;
use Illuminate\Support\Str;

class ServiceSyncService
{
    public function __construct(
        private readonly ProviderManager $providers,
        private readonly PricingEngine $pricing,
    ) {
    }

    public function sync(ElectronicServiceProvider $provider): array
    {
        $services = $this->providers->gateway($provider)->syncServices();
        $created = 0;
        $updated = 0;

        foreach ($services as $providerService) {
            if (! $providerService instanceof ProviderServiceDTO) {
                continue;
            }

            $category = $this->categoryFor($providerService);
            $retailPrice = $this->pricing->calculate(
                $providerService->costPrice,
                $provider->default_profit_type,
                (float) $provider->default_profit_value,
            );
            $wholesalePrice = $this->pricing->calculate(
                $providerService->costPrice,
                $provider->default_wholesale_profit_type,
                (float) $provider->default_wholesale_profit_value,
            );

            $service = ElectronicService::query()->firstOrNew([
                'electronic_service_provider_id' => $provider->id,
                'provider_service_id' => $providerService->providerServiceId,
            ]);

            $service->fill([
                'electronic_service_category_id' => $category->id,
                'name' => $service->exists ? $service->name : ['ar' => $providerService->name, 'en' => $providerService->name],
                'description' => $service->exists ? $service->description : ['ar' => $providerService->description, 'en' => $providerService->description],
                'image' => $providerService->image,
                'service_type' => ElectronicService::TYPE_API,
                'provider_cost_price' => $this->decimal($providerService->costPrice),
                'cost' => $this->decimal($providerService->costPrice),
                'retail_profit_type' => $service->retail_profit_type ?: $provider->default_profit_type,
                'retail_profit_value' => $service->retail_profit_value ?: $provider->default_profit_value,
                'wholesale_profit_type' => $service->wholesale_profit_type ?: $provider->default_wholesale_profit_type,
                'wholesale_profit_value' => $service->wholesale_profit_value ?: $provider->default_wholesale_profit_value,
                'price' => $provider->auto_sync_prices || ! $service->exists ? $this->decimal($retailPrice) : $service->price,
                'wholesale_price' => $provider->auto_sync_prices || ! $service->exists ? $this->decimal($wholesalePrice) : $service->wholesale_price,
                'fields_schema' => $providerService->requiredFields,
                'required_fields' => $providerService->requiredFields,
                'metadata' => $providerService->metadata,
                'is_available' => $providerService->available,
                'is_visible' => $service->exists ? $service->is_visible : true,
                'is_active' => $providerService->available,
            ]);

            $service->save();

            $service->wasRecentlyCreated ? $created++ : $updated++;
        }

        $provider->forceFill(['last_sync_at' => now()])->save();

        return ['created' => $created, 'updated' => $updated, 'total' => $created + $updated];
    }

    private function categoryFor(ProviderServiceDTO $service): ElectronicServiceCategory
    {
        $name = $service->category ?: __('Uncategorized services');
        $slug = Str::slug($name) ?: 'uncategorized-services';

        return ElectronicServiceCategory::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ['ar' => $name, 'en' => $name],
                'description' => ['ar' => null, 'en' => null],
                'icon' => '⚡',
                'sort_order' => 99,
            'is_active' => true,
            ],
        );
    }

    private function decimal(float $value): string
    {
        return number_format($value, 6, '.', '');
    }
}
