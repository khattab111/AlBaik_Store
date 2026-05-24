<?php

namespace App\Filament\Resources\WholesaleApplicationResource\Pages;

use App\Filament\Resources\WholesaleApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWholesaleApplication extends EditRecord
{
    protected static string $resource = WholesaleApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
