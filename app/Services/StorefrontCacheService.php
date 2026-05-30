<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StorefrontCacheService
{
    public function clearHome(): void
    {
        foreach ($this->localeCodes() as $locale) {
            Cache::forget("storefront.home.{$locale}.v1");
        }
    }

    /**
     * @return array<int, string>
     */
    private function localeCodes(): array
    {
        try {
            if (Schema::hasTable('languages')) {
                $codes = Language::query()
                    ->where('is_active', true)
                    ->pluck('code')
                    ->filter()
                    ->values()
                    ->all();

                if ($codes !== []) {
                    return $codes;
                }
            }
        } catch (Throwable) {
            //
        }

        return ['ar', 'en'];
    }
}
