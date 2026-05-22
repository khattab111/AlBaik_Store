<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommerceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Revenue', '$'.number_format((float) Order::sum('total'), 2))
                ->description('All-time gross sales')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Orders', Order::count())
                ->description('Total order count')
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Products', Product::count())
                ->description('Catalog items')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Customers', User::role(['Customer', 'Wholesale Customer'])->count())
                ->description('Retail and wholesale accounts')
                ->icon('heroicon-o-users'),
        ];
    }
}
