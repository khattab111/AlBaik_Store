<?php

namespace App\Services\Providers\DTOs;

class ProviderServiceDTO
{
    public function __construct(
        public readonly string $providerServiceId,
        public readonly string $name,
        public readonly ?string $category = null,
        public readonly float $costPrice = 0,
        public readonly bool $available = true,
        public readonly array $requiredFields = [],
        public readonly array $metadata = [],
        public readonly ?string $description = null,
        public readonly ?string $image = null,
    ) {
    }
}
