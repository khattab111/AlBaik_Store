<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Order;
use App\Models\Setting;
use App\Models\ShippingCarrier;
use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShippingService
{
    public function getAvailableCarriersForCity(City $city): EloquentCollection
    {
        return ShippingCarrier::query()
            ->where('status', ShippingCarrier::STATUS_ACTIVE)
            ->whereHas('shippingRates', fn ($query) => $query
                ->where('city_id', $city->id)
                ->where('is_active', true))
            ->with(['shippingRates' => fn ($query) => $query
                ->where('city_id', $city->id)
                ->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function calculateCartWeight(Cart $cart): float
    {
        $cart->loadMissing('items.product');
        $defaultWeight = $this->settingFloat('shipping.default_product_weight', 0.0);

        return round($cart->items->sum(function (CartItem $item) use ($defaultWeight): float {
            if (! (bool) $item->product->requires_shipping) {
                return 0.0;
            }

            $weight = (float) ($item->product->weight ?: $defaultWeight);

            return $weight * (int) $item->quantity;
        }), 3);
    }

    public function requiresShipping(Cart $cart): bool
    {
        $cart->loadMissing('items.product');

        return $cart->items->contains(fn (CartItem $item): bool => (bool) $item->product->requires_shipping);
    }

    public function calculateShippingCost(City $city, ShippingCarrier $carrier, Cart $cart, float $subtotal = 0.0, bool $hasFreeShippingOffer = false): array
    {
        if (! $this->requiresShipping($cart)) {
            return $this->quote(0.0, 0.0, null, true, true);
        }

        $rate = $this->validateCarrierForCity($city, $carrier);
        $weight = $this->calculateCartWeight($cart);

        if ($rate->min_weight !== null && $weight < (float) $rate->min_weight) {
            throw ValidationException::withMessages([
                'shipping_carrier_id' => __('This carrier is not available for the current cart weight.'),
            ]);
        }

        if ($rate->max_weight !== null && $weight > (float) $rate->max_weight) {
            throw ValidationException::withMessages([
                'shipping_carrier_id' => __('This carrier is not available for the current cart weight.'),
            ]);
        }

        $isFree = $hasFreeShippingOffer
            || $cart->items->contains(fn (CartItem $item): bool => (bool) $item->product->free_shipping)
            || ($rate->free_shipping_threshold !== null && $subtotal >= (float) $rate->free_shipping_threshold)
            || ($this->settingBool('shipping.enable_free_shipping') && $subtotal >= $this->settingFloat('shipping.global_free_shipping_threshold', PHP_FLOAT_MAX));

        if ($isFree) {
            return $this->quote(0.0, $weight, $rate, true, false);
        }

        $cost = (float) $rate->base_cost + ($weight * (float) $rate->cost_per_kg) + (float) ($rate->remote_area_fee ?? 0);

        return $this->quote(round($cost, 2), $weight, $rate, false, false);
    }

    public function validateCarrierForCity(City $city, ShippingCarrier $carrier): ShippingRate
    {
        if (! $city->is_active || $carrier->status !== ShippingCarrier::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'shipping_carrier_id' => __('No shipping carriers are available for this city right now.'),
            ]);
        }

        $rate = ShippingRate::query()
            ->where('city_id', $city->id)
            ->where('shipping_carrier_id', $carrier->id)
            ->where('is_active', true)
            ->first();

        if (! $rate) {
            throw ValidationException::withMessages([
                'shipping_carrier_id' => __('The selected shipping carrier does not cover this city.'),
            ]);
        }

        return $rate;
    }

    public function createShippingSnapshot(Order $order, City $city, ?ShippingCarrier $carrier, Cart $cart, array $quote, string $addressText): void
    {
        $order->update([
            'shipping_city_id' => $city->id,
            'shipping_city_name' => $city->name,
            'shipping_carrier_id' => $carrier?->id,
            'shipping_carrier_name' => $carrier?->name,
            'shipping_cost' => $quote['cost'],
            'shipping_weight' => $quote['weight'],
            'shipping_delivery_time' => $quote['estimated_delivery_time'],
            'shipping_address_text' => $addressText,
            'is_free_shipping' => $quote['is_free_shipping'],
        ]);
    }

    public function formatCarriersForCheckout(City $city, Cart $cart, float $subtotal, bool $hasFreeShippingOffer = false): Collection
    {
        return $this->getAvailableCarriersForCity($city)
            ->map(function (ShippingCarrier $carrier) use ($city, $cart, $subtotal, $hasFreeShippingOffer): array {
                $quote = $this->calculateShippingCost($city, $carrier, $cart, $subtotal, $hasFreeShippingOffer);

                return [
                    'id' => $carrier->id,
                    'name' => $carrier->name,
                    'logo' => $carrier->logo,
                    'cost' => $quote['cost'],
                    'weight' => $quote['weight'],
                    'estimated_delivery_time' => $quote['estimated_delivery_time'],
                    'is_free_shipping' => $quote['is_free_shipping'],
                ];
            })
            ->values();
    }

    private function quote(float $cost, float $weight, ?ShippingRate $rate, bool $isFree, bool $noShippingRequired): array
    {
        return [
            'cost' => $cost,
            'weight' => $weight,
            'estimated_delivery_time' => $rate?->estimated_delivery_time,
            'is_free_shipping' => $isFree,
            'no_shipping_required' => $noShippingRequired,
        ];
    }

    private function settingFloat(string $key, float $default): float
    {
        $value = Setting::where('key', $key)->value('value');

        return (float) data_get($value, 'value', $default);
    }

    private function settingBool(string $key): bool
    {
        $value = Setting::where('key', $key)->value('value');

        return filter_var(data_get($value, 'value', false), FILTER_VALIDATE_BOOL);
    }
}
