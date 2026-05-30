<?php

namespace App\Data;

use App\Models\FlashOffer;
use App\Models\FlashOfferItem;

class FlashOfferPriceData
{
    public function __construct(
        public readonly FlashOffer $offer,
        public readonly ?FlashOfferItem $item,
        public readonly float $originalPrice,
        public readonly float $offerPrice,
        public readonly bool $freeShipping = false,
    ) {}
}
