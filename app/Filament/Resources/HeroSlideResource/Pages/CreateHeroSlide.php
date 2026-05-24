<?php

namespace App\Filament\Resources\HeroSlideResource\Pages;

use App\Filament\Resources\HeroSlideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHeroSlide extends CreateRecord
{
    protected static string $resource = HeroSlideResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['placement'] = 'home';

        return $data;
    }
}
