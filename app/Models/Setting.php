<?php

namespace App\Models;

use App\Services\SiteSettingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['group', 'key', 'value', 'type', 'is_public'];

    protected $casts = ['value' => 'array', 'is_public' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn () => SiteSettingService::forgetCache());
        static::deleted(fn () => SiteSettingService::forgetCache());
    }
}
