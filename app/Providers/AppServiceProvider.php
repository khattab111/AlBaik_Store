<?php

namespace App\Providers;

use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\CurrencyService;
use App\Services\DiscountService;
use App\Services\GuestCartService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\ShippingService;
use App\Services\SiteSettingService;
use App\Events\OrderPlaced;
use App\Listeners\QueueOrderInvoice;
use App\Models\Category;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(CartRepository::class);
        $this->app->singleton(OrderRepository::class);

        $this->app->singleton(ProductService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(CurrencyService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(ShippingService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(DiscountService::class);
        $this->app->singleton(OrderWorkflowService::class);
        $this->app->singleton(SiteSettingService::class);
    }

    public function boot(): void
    {
        Event::listen(OrderPlaced::class, QueueOrderInvoice::class);
        Order::observe(OrderObserver::class);
        $this->shareStorefrontNavigation();
        $this->configureFilamentTranslations();
    }

    private function shareStorefrontNavigation(): void
    {
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $siteSettings = app(SiteSettingService::class);
            $currencyService = app(CurrencyService::class);
            $cartCount = 0;
            $wishlistCount = 0;

            if ($user) {
                $cart = $user->cart()->first();
                $cartCount = $cart ? (int) $cart->items()->sum('quantity') : 0;
                $wishlistCount = (int) $user->wishlist()->count();
            } else {
                $cartCount = app(GuestCartService::class)->count();
            }

            $view->with([
                'navCategories' => Category::where('status', true)
                    ->whereNull('parent_id')
                    ->withCount('products')
                    ->orderBy('name->'.app()->getLocale())
                    ->take(6)
                    ->get(),
                'cartCount' => $cartCount,
                'wishlistCount' => $wishlistCount,
                'siteIdentity' => $siteSettings->identity(),
                'siteContact' => $siteSettings->contact(),
                'siteSocial' => $siteSettings->social(),
                'supportedCurrencies' => $currencyService->activeCurrencies(),
                'currentCurrency' => $currencyService->currentCurrency(),
            ]);
        });
    }

    private function configureFilamentTranslations(): void
    {
        $components = [
            \Filament\Forms\Components\TextInput::class,
            \Filament\Forms\Components\Textarea::class,
            \Filament\Forms\Components\Select::class,
            \Filament\Forms\Components\Toggle::class,
            \Filament\Forms\Components\DateTimePicker::class,
            \Filament\Forms\Components\FileUpload::class,
            \Filament\Forms\Components\KeyValue::class,
            \Filament\Forms\Components\Repeater::class,
            \Filament\Forms\Components\RichEditor::class,
            \Filament\Tables\Columns\TextColumn::class,
            \Filament\Tables\Columns\IconColumn::class,
            \Filament\Tables\Columns\ImageColumn::class,
        ];

        foreach ($components as $component) {
            if (method_exists($component, 'configureUsing')) {
                $component::configureUsing(function ($instance): void {
                    if (method_exists($instance, 'translateLabel')) {
                        $instance->translateLabel();
                    }
                });
            }
        }
    }
}
