<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['name', 'slug', 'description', 'logo', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
