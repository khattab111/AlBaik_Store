<?php

namespace App\Filament\Resources\ElectronicServiceResource\Pages;

use App\Filament\Resources\ElectronicServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectronicService extends CreateRecord
{
    protected static string $resource = ElectronicServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ElectronicServiceResource::preparePricingData($data);
    }
}
