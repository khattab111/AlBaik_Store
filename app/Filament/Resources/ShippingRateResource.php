<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingRateResource\Pages;
use App\Models\City;
use App\Models\ShippingCarrier;
use App\Models\ShippingRate;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class ShippingRateResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = ShippingRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 42;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('shipping_carrier_id')
                ->label(__('Shipping Carrier'))
                ->options(fn () => ShippingCarrier::where('status', ShippingCarrier::STATUS_ACTIVE)->orderBy('sort_order')->get()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->rule(fn ($record, Forms\Get $get) => Rule::unique('shipping_rates', 'shipping_carrier_id')
                    ->where('city_id', $get('city_id'))
                    ->ignore($record?->id)),
            Forms\Components\Select::make('city_id')
                ->label(__('City'))
                ->options(fn () => City::where('is_active', true)->orderBy('sort_order')->get()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('base_cost')->numeric()->required()->default(0),
            Forms\Components\TextInput::make('cost_per_kg')->numeric()->required()->default(0),
            Forms\Components\TextInput::make('min_weight')->numeric(),
            Forms\Components\TextInput::make('max_weight')->numeric(),
            Forms\Components\TextInput::make('free_shipping_threshold')->numeric(),
            Forms\Components\TextInput::make('estimated_delivery_time')->maxLength(255),
            Forms\Components\TextInput::make('remote_area_fee')->numeric(),
            Forms\Components\TextInput::make('sort_order')->numeric(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('carrier.name')->label(__('Carrier'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label(__('City'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('base_cost')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('cost_per_kg')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('estimated_delivery_time')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shipping_carrier_id')->relationship('carrier', 'name')->label(__('Carrier')),
                Tables\Filters\SelectFilter::make('city_id')->relationship('city', 'name')->label(__('City')),
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
            'index' => Pages\ListShippingRates::route('/'),
            'create' => Pages\CreateShippingRate::route('/create'),
            'edit' => Pages\EditShippingRate::route('/{record}/edit'),
        ];
    }
}
