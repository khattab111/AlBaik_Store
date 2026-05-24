<?php

namespace App\Data;

use App\Models\ProductPriceTier;

class ProductPriceData
{
    public function __construct(
        public readonly float $price,
        public readonly string $priceType,
        public readonly ?int $appliedTierId = null,
        public readonly ?ProductPriceTier $tier = null,
    ) {}
}
