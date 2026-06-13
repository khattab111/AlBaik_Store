<?php

namespace App\Services\Providers\Services;

use App\Models\ElectronicServiceProvider;
use App\Services\Providers\Contracts\ProviderGatewayInterface;
use App\Services\Providers\Gateways\GenericApiGateway;
use App\Services\Providers\Gateways\ManualGateway;

class ProviderManager
{
    public function gateway(ElectronicServiceProvider $provider): ProviderGatewayInterface
    {
        $type = $provider->providerType();

        if ($type === ElectronicServiceProvider::TYPE_CUSTOM_GATEWAY && filled($provider->gateway_class)) {
            $class = $provider->gateway_class;

            if (class_exists($class) && is_subclass_of($class, ProviderGatewayInterface::class)) {
                return app($class, ['provider' => $provider]);
            }
        }

        if ($type === ElectronicServiceProvider::TYPE_GENERIC_API) {
            return new GenericApiGateway($provider);
        }

        return new ManualGateway($provider);
    }
}
