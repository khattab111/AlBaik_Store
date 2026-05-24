<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'direction',
        'is_default',
        'is_active',
        'flag',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Language $language): void {
            if ($language->is_default) {
                static::where('id', '!=', $language->id)->update(['is_default' => false]);
            }
        });

        static::saved(fn () => \App\Services\LanguageService::forgetCache());
        static::deleted(fn () => \App\Services\LanguageService::forgetCache());
    }
}
