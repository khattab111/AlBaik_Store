<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Models\City;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CityResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required()->maxLength(255),
            ]),
            Forms\Components\TextInput::make('slug')
                ->label(__('Slug'))
                ->helperText(__('Generated automatically from the city name.'))
                ->disabled()
                ->dehydrated(false)
                ->visible(fn ($record): bool => $record !== null),
            Forms\Components\TextInput::make('country')->label(__('Country'))->required()->maxLength(100),
            Forms\Components\TextInput::make('code')->label(__('Code'))->maxLength(50),
            Forms\Components\Toggle::make('is_active')->label(__('Is active'))->default(true),
            Forms\Components\TextInput::make('sort_order')->label(__('Sort order'))->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country')->label(__('Country'))->sortable(),
                Tables\Columns\TextColumn::make('code')->label(__('Code'))->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Sort order'))->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Is active'))->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
