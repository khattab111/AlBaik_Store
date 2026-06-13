<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletDepositRequestResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'proof_image' => $this->imageUrl($this->proof_image),
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
