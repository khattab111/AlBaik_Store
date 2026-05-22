<?php

namespace App\Services;

use App\Models\ShippingMethod;

class ShippingService
{
    public function calculate(ShippingMethod $method, float $subtotal, int $quantity = 0, float $weight = 0, ?string $country = null, ?string $city = null, ?string $town = null): float
    {
        if ($method->free_shipping_minimum !== null && $subtotal >= (float) $method->free_shipping_minimum) {
            return 0.0;
        }

        $databaseRule = $method->shippingRules()
            ->where('is_active', true)
            ->with('zone')
            ->get()
            ->first(function ($rule) use ($subtotal, $quantity, $weight, $country, $city, $town) {
                $zone = $rule->zone;
                $matchesZone = ! $zone
                    || ((! $zone->country || $zone->country === $country)
                        && (! $zone->city || $zone->city === $city)
                        && (! $zone->town || $zone->town === $town));

                return $matchesZone
                    && ($rule->min_quantity === null || $quantity >= $rule->min_quantity)
                    && ($rule->max_quantity === null || $quantity <= $rule->max_quantity)
                    && ($rule->min_weight === null || $weight >= (float) $rule->min_weight)
                    && ($rule->max_weight === null || $weight <= (float) $rule->max_weight)
                    && ($rule->min_subtotal === null || $subtotal >= (float) $rule->min_subtotal);
            });

        if ($databaseRule) {
            if ($databaseRule->is_free || $databaseRule->calculation_type === 'free') {
                return 0.0;
            }

            if ($databaseRule->calculation_type === 'weight') {
                return round((float) $databaseRule->cost + ($weight * (float) $databaseRule->cost_per_kg), 2);
            }

            return round((float) $databaseRule->cost, 2);
        }

        $rules = $method->rules ?? [];
        $cost = (float) $method->cost;

        foreach ($rules as $rule) {
            $matchesQuantity = ! isset($rule['min_quantity']) || $quantity >= (int) $rule['min_quantity'];
            $matchesWeight = ! isset($rule['min_weight']) || $weight >= (float) $rule['min_weight'];

            if ($matchesQuantity && $matchesWeight && isset($rule['cost'])) {
                $cost = (float) $rule['cost'];
            }
        }

        return round($cost, 2);
    }
}
