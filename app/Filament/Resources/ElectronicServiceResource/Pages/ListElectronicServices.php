<?php

namespace App\Filament\Resources\ElectronicServiceResource\Pages;

use App\Filament\Resources\ElectronicServiceResource;
use App\Models\ElectronicServiceProvider;
use App\Services\Providers\Services\ServiceSyncService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListElectronicServices extends ListRecords
{
    protected static string $resource = ElectronicServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncProviderServices')
                ->label(__('Sync supplier services'))
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Forms\Components\Select::make('provider_id')
                        ->label(__('Supplier'))
                        ->options(fn () => ElectronicServiceProvider::query()
                            ->where('status', ElectronicServiceProvider::STATUS_ACTIVE)
                            ->whereIn('provider_type', [
                                ElectronicServiceProvider::TYPE_GENERIC_API,
                                ElectronicServiceProvider::TYPE_CUSTOM_GATEWAY,
                            ])
                            ->orderBy('id')
                            ->get()
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data, ServiceSyncService $sync): void {
                    $provider = ElectronicServiceProvider::query()->findOrFail($data['provider_id']);
                    $result = $sync->sync($provider);

                    Notification::make()
                        ->title(__('Supplier services synced'))
                        ->body(__('Created: :created, Updated: :updated', [
                            'created' => $result['created'] ?? 0,
                            'updated' => $result['updated'] ?? 0,
                        ]))
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
                ->label(__('Create manual service')),
        ];
    }
}
