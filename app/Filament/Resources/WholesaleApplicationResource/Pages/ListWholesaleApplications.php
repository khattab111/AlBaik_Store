<?php

namespace App\Filament\Resources\WholesaleApplicationResource\Pages;

use App\Filament\Resources\WholesaleApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWholesaleApplications extends ListRecords
{
    protected static string $resource = WholesaleApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
