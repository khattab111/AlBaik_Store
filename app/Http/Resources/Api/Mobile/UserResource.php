<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $wallet = $this->relationLoaded('wallet') ? $this->wallet : null;
        $defaultAddress = $this->relationLoaded('addresses') ? $this->addresses->firstWhere('is_default', true) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->mobile,
            'customer_type' => $this->isWholesaleCustomer() ? 'wholesale' : 'retail',
            'wholesale_status' => $this->isWholesaleCustomer() ? 'approved' : null,
            'wallet_balance' => $wallet ? (float) $wallet->balance : 0.0,
            'wallet_currency' => $wallet?->currency_code,
            'default_address' => $defaultAddress ? new AddressResource($defaultAddress) : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
