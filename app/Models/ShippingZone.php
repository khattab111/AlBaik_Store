<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country', 'city', 'town', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function rules(): HasMany
    {
        return $this->hasMany(ShippingRule::class);
    }
}
