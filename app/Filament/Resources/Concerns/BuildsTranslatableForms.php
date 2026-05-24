<?php

namespace App\Filament\Resources\Concerns;

use App\Services\LanguageService;
use Closure;
use Filament\Forms;

trait BuildsTranslatableForms
{
    protected static function activeLanguages(): array
    {
        return app(LanguageService::class)->active();
    }

    protected static function translatableTabs(array|Closure $fields, ?string $label = null): Forms\Components\Tabs
    {
        return Forms\Components\Tabs::make($label ?? __('Translations'))
            ->tabs(collect(static::activeLanguages())
                ->map(function (array $language, string $code) use ($fields): Forms\Components\Tabs\Tab {
                    $schema = $fields instanceof Closure ? $fields($code, $language) : $fields;

                    return Forms\Components\Tabs\Tab::make(($language['flag'] ?? '').' '.$language['native'])
                        ->schema($schema);
                })
                ->values()
                ->all());
    }
}
