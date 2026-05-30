<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashOffer;
use App\Models\Product;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('store:generate-missing-slugs', function () {
    $models = [
        Product::class,
        Category::class,
        Brand::class,
        FlashOffer::class,
        Banner::class,
    ];

    $total = 0;

    foreach ($models as $modelClass) {
        $model = new $modelClass;

        if (! Schema::hasColumn($model->getTable(), 'slug')) {
            $this->warn("Skipping {$modelClass}: no slug column.");
            continue;
        }

        $count = 0;

        $modelClass::query()
            ->where(fn ($query) => $query->whereNull('slug')->orWhere('slug', ''))
            ->orderBy($model->getKeyName())
            ->each(function ($record) use (&$count): void {
                $record->generateSlugIfMissing();
                $record->saveQuietly();
                $count++;
            });

        $total += $count;
        $this->info("{$modelClass}: {$count} slugs generated.");
    }

    $this->info("Done. {$total} records updated.");
})->purpose('Generate missing slugs for store content models');
