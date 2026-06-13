<?php

namespace App\Filament\Resources\ElectronicServiceProviderResource\Pages;

use App\Filament\Resources\ElectronicServiceProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElectronicServiceProviders extends ListRecords
{
    protected static string $resource = ElectronicServiceProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
