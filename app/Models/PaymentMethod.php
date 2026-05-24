<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'wallet_url',
        'barcode_image',
        'type',
        'settings',
        'fee',
        'is_active',
    ];

    protected $casts = ['settings' => 'array', 'fee' => 'decimal:2', 'is_active' => 'boolean'];
}
