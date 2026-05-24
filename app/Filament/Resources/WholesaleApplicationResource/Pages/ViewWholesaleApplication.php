<?php

namespace App\Filament\Resources\WholesaleApplicationResource\Pages;

use App\Filament\Resources\WholesaleApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWholesaleApplication extends ViewRecord
{
    protected static string $resource = WholesaleApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
