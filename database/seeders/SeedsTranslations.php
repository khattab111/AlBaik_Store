<?php

namespace Database\Seeders;

trait SeedsTranslations
{
    protected function tr(string $en, string $ar): array
    {
        return ['en' => $en, 'ar' => $ar];
    }
}
