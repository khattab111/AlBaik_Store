<?php

namespace App\Filament\Resources\WalletDepositRequestResource\Pages;

use App\Filament\Resources\WalletDepositRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWalletDepositRequests extends ListRecords
{
    protected static string $resource = WalletDepositRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
