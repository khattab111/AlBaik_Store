<?php

namespace App\Services\Providers\DTOs;

class ProviderResponse
{
    public function __construct(
        public readonly bool $successful,
        public readonly ?string $message = null,
        public readonly array $data = [],
        public readonly ?string $providerStatus = null,
        public readonly ?string $providerOrderId = null,
    ) {
    }

    public static function success(?string $message = null, array $data = [], ?string $providerStatus = null, ?string $providerOrderId = null): self
    {
        return new self(true, $message, $data, $providerStatus, $providerOrderId);
    }

    public static function failure(?string $message = null, array $data = [], ?string $providerStatus = null): self
    {
        return new self(false, $message, $data, $providerStatus);
    }
}
