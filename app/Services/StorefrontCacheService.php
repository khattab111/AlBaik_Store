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
            Cache::forget("storefront.home.{$locale}.v2");
        }
    }

    public function clearCategory(int $categoryId): void
    {
        Cache::forget("storefront.category.{$categoryId}.v1");
        $this->clearHome();
    }

    public function clearBrand(int $brandId): void
    {
        Cache::forget("storefront.brand.{$brandId}.v1");
        $this->clearHome();
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
