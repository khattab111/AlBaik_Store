<?php

namespace App\Data;

use App\Models\ProductPriceTier;
use App\Models\FlashOffer;

class ProductPriceData
{
    public function __construct(
        public readonly float $price,
        public readonly string $priceType,
        public readonly ?int $appliedTierId = null,
        public readonly ?ProductPriceTier $tier = null,
        public readonly ?int $appliedFlashOfferId = null,
        public readonly ?FlashOffer $flashOffer = null,
        public readonly ?float $originalPrice = null,
        public readonly bool $freeShipping = false,
    ) {}
}
