<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingMethodResource\Pages;
use App\Models\ShippingMethod;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingMethodResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(ShippingMethod::class, 'slug', ignoreRecord: true),
            Forms\Components\Textarea::make('description')->rows(3),
            Forms\Components\TextInput::make('zone'),
            Forms\Components\Select::make('type')->options(['flat_rate' => __('Flat Rate'), 'rule_based' => __('Rule Based')])->required(),
            Forms\Components\TextInput::make('cost')->numeric()->default(0),
            Forms\Components\TextInput::make('free_shipping_minimum')->numeric(),
            Forms\Components\KeyValue::make('rules')->helperText('Optional rule map for future gateway/rate integrations.'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('zone')->sortable(),
            Tables\Columns\TextColumn::make('cost')->money('USD')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingMethods::route('/'),
            'create' => Pages\CreateShippingMethod::route('/create'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
