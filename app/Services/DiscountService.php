<?php

namespace App\Services;

use App\Models\Product;

class DiscountService
{
    public function __construct(private readonly FlashOfferService $flashOffers) {}

    public function productPrice(Product $product, int $quantity, ?float $basePrice = null): float
    {
        $price = $basePrice ?? $product->getPriceForQuantity($quantity);
        $offer = $this->flashOffers->calculateProductOffer($product, $quantity, (float) $price);

        return round($offer?->offerPrice ?? $price, 2);
    }
}
