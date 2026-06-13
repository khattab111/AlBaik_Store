<?php

namespace App\Filament\Resources\ElectronicServiceResource\Pages;

use App\Filament\Resources\ElectronicServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElectronicService extends EditRecord
{
    protected static string $resource = ElectronicServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ElectronicServiceResource::preparePricingData($data);
    }
}
