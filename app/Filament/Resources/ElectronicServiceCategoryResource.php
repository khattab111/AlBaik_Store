<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\ElectronicServiceCategoryResource\Pages;
use App\Models\ElectronicServiceCategory;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElectronicServiceCategoryResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = ElectronicServiceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Electronic Services';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required()->maxLength(255),
                Forms\Components\Textarea::make("description.{$code}")->label(__('Description'))->rows(3),
            ]),
            Forms\Components\TextInput::make('slug')
                ->label(__('Slug'))
                ->disabled()
                ->dehydrated(false)
                ->visible(fn ($record): bool => $record !== null),
            Forms\Components\TextInput::make('icon')
                ->label(__('Icon'))
                ->helperText(__('Small symbol shown beside the category, such as phone, game, or lightning.'))
                ->maxLength(20),
            Forms\Components\TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')->label(__('Icon')),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('services_count')->label(__('Services'))->counts('services')->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Sort order'))->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElectronicServiceCategories::route('/'),
            'create' => Pages\CreateElectronicServiceCategory::route('/create'),
            'edit' => Pages\EditElectronicServiceCategory::route('/{record}/edit'),
        ];
    }
}
