<?php

namespace App\Providers;

use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\CurrencyService;
use App\Services\DiscountService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\ShippingService;
use App\Events\OrderPlaced;
use App\Listeners\QueueOrderInvoice;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
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
    }

    public function boot(): void
    {
        Event::listen(OrderPlaced::class, QueueOrderInvoice::class);
        Order::observe(OrderObserver::class);
        $this->configureFilamentTranslations();
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
