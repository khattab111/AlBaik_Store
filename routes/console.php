<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use App\Jobs\CheckProviderOrdersStatusJob;
use App\Jobs\SendNewsletterCampaignJob;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\FlashOffer;
use App\Models\NewsletterCampaign;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;

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

Artisan::command('wallets:ensure {--fresh-currency : Fill missing wallet currency with the current default currency}', function (WalletService $wallets) {
    $created = 0;
    $updated = 0;

    User::query()
        ->orderBy('id')
        ->each(function (User $user) use ($wallets, &$created, &$updated): void {
            $hadWallet = Wallet::where('user_id', $user->id)->exists();
            $wallet = $wallets->getOrCreateWallet($user);

            if (! $hadWallet) {
                $created++;
            }

            if ($this->option('fresh-currency') && blank($wallet->currency_code)) {
                $defaultCurrency = Currency::query()->where('is_default', true)->value('code');

                if ($defaultCurrency) {
                    $wallet->forceFill(['currency_code' => $defaultCurrency])->save();
                    $updated++;
                }
            }
        });

    $this->info("Wallets created: {$created}");
    $this->info("Wallets updated: {$updated}");
})->purpose('Create missing wallets for existing users');

Schedule::call(function (): void {
    NewsletterCampaign::readyToSend()
        ->orderBy('scheduled_at')
        ->each(fn (NewsletterCampaign $campaign) => SendNewsletterCampaignJob::dispatch($campaign->id));
})->everyMinute()->name('newsletter-scheduled-campaigns')->withoutOverlapping();

Schedule::job(new CheckProviderOrdersStatusJob())
    ->everyFiveMinutes()
    ->name('provider-orders-status-check')
    ->withoutOverlapping();
