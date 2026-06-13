<?php

namespace App\Filament\Resources\ElectronicServiceCategoryResource\Pages;

use App\Filament\Resources\ElectronicServiceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElectronicServiceCategory extends EditRecord
{
    protected static string $resource = ElectronicServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
