<?php

namespace App\Filament\Resources\FlashOfferResource\Pages;

use App\Filament\Resources\FlashOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlashOffers extends ListRecords
{
    protected static string $resource = FlashOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
