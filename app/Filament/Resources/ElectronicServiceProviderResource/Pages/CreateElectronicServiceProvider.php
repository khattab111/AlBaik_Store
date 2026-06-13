<?php

namespace App\Filament\Resources\ElectronicServiceProviderResource\Pages;

use App\Filament\Resources\ElectronicServiceProviderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectronicServiceProvider extends CreateRecord
{
    protected static string $resource = ElectronicServiceProviderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ElectronicServiceProviderResource::normalizeSecretAuthConfig($data);
    }
}
