<?php

namespace App\Filament\Resources\ElectronicServiceProviderResource\Pages;

use App\Filament\Resources\ElectronicServiceProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElectronicServiceProvider extends EditRecord
{
    protected static string $resource = ElectronicServiceProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ElectronicServiceProviderResource::normalizeSecretAuthConfig($data, $this->record->auth_config ?? []);
    }
}
