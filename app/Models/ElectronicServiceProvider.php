<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectronicServiceProvider extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public const TYPE_MANUAL = 'manual';
    public const TYPE_GENERIC_API = 'generic_api';
    public const TYPE_CUSTOM_GATEWAY = 'custom_gateway';
    public const TYPE_API = self::TYPE_GENERIC_API;

    public const AUTH_NO_AUTH = 'no_auth';
    public const AUTH_API_KEY_HEADER = 'api_key_header';
    public const AUTH_BEARER_TOKEN = 'bearer_token';
    public const AUTH_BASIC_AUTH = 'basic_auth';
    public const AUTH_QUERY_KEY = 'query_key';
    public const AUTH_BODY_KEY = 'body_key';
    public const AUTH_CUSTOM_HEADERS = 'custom_headers';

    public const PROFIT_PERCENTAGE = 'percentage';
    public const PROFIT_FIXED = 'fixed';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public array $translatable = ['name'];

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'provider_type',
        'gateway_class',
        'base_url',
        'auth_type',
        'auth_config',
        'endpoints_config',
        'request_mapping',
        'response_mapping',
        'status_mapping',
        'default_profit_type',
        'default_profit_value',
        'default_wholesale_profit_type',
        'default_wholesale_profit_value',
        'auto_sync_services',
        'auto_sync_prices',
        'last_sync_at',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'settings',
        'admin_note',
    ];

    protected $casts = [
        'settings' => 'array',
        'auth_config' => 'encrypted:array',
        'endpoints_config' => 'array',
        'request_mapping' => 'array',
        'response_mapping' => 'array',
        'status_mapping' => 'array',
        'default_profit_value' => 'decimal:2',
        'default_wholesale_profit_value' => 'decimal:2',
        'auto_sync_services' => 'boolean',
        'auto_sync_prices' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(ElectronicService::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ElectronicServiceOrder::class);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MANUAL => __('Manual'),
            self::TYPE_GENERIC_API => __('Generic API'),
            self::TYPE_CUSTOM_GATEWAY => __('Custom gateway'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive'),
        ];
    }

    public static function authTypeOptions(): array
    {
        return [
            self::AUTH_NO_AUTH => __('No Auth'),
            self::AUTH_API_KEY_HEADER => __('API Key Header'),
            self::AUTH_BEARER_TOKEN => __('Bearer Token'),
            self::AUTH_BASIC_AUTH => __('Basic Auth'),
            self::AUTH_QUERY_KEY => __('Query Key'),
            self::AUTH_BODY_KEY => __('Body Key'),
            self::AUTH_CUSTOM_HEADERS => __('Custom Headers'),
        ];
    }

    public static function profitTypeOptions(): array
    {
        return [
            self::PROFIT_PERCENTAGE => __('Percentage'),
            self::PROFIT_FIXED => __('Fixed amount'),
        ];
    }

    public function providerType(): string
    {
        return $this->provider_type ?: $this->type ?: self::TYPE_MANUAL;
    }

    public function isApiProvider(): bool
    {
        return in_array($this->providerType(), [self::TYPE_GENERIC_API, self::TYPE_CUSTOM_GATEWAY], true);
    }
}
