<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Inventory';

public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Admin') ?? false;
}

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required(),
                Forms\Components\Textarea::make("address.{$code}")->label(__('Address'))->rows(3),
                Forms\Components\TextInput::make("city.{$code}")->label(__('City')),
                Forms\Components\TextInput::make("country.{$code}")->label(__('Country')),
            ]),
            Forms\Components\TextInput::make('code')->required()->unique(Warehouse::class, 'code', ignoreRecord: true),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('city')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
