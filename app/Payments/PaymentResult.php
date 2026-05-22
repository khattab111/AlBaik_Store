<?php

namespace App\Payments;

readonly class PaymentResult
{
    public function __construct(
        public bool $successful,
        public string $status,
        public ?string $reference = null,
        public array $payload = [],
    ) {}
}
