<?php

namespace App\Filament\Resources\ElectronicServiceCategoryResource\Pages;

use App\Filament\Resources\ElectronicServiceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElectronicServiceCategories extends ListRecords
{
    protected static string $resource = ElectronicServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
