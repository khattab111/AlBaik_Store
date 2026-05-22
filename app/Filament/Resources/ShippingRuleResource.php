<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingRuleResource\Pages;
use App\Models\ShippingRule;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingRuleResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = ShippingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('shipping_method_id')->relationship('method', 'name')->required()->searchable(),
            Forms\Components\Select::make('shipping_zone_id')->relationship('zone', 'name')->searchable(),
            Forms\Components\Select::make('calculation_type')
                ->options([
                    'free' => __('Free Shipping'),
                    'fixed' => __('Fixed Cost'),
                    'weight' => __('By Weight'),
                ])
                ->default('fixed')
                ->required()
                ->live(),
            Forms\Components\TextInput::make('min_quantity')->numeric(),
            Forms\Components\TextInput::make('max_quantity')->numeric(),
            Forms\Components\TextInput::make('min_weight')->numeric(),
            Forms\Components\TextInput::make('max_weight')->numeric(),
            Forms\Components\TextInput::make('min_subtotal')->numeric(),
            Forms\Components\TextInput::make('cost')
                ->label(__('Base Cost'))
                ->numeric()
                ->required()
                ->default(0)
                ->helperText(__('For fixed shipping this is the full shipping cost. For weight shipping this is added before cost per kg.')),
            Forms\Components\TextInput::make('cost_per_kg')
                ->numeric()
                ->default(0)
                ->visible(fn (Forms\Get $get): bool => $get('calculation_type') === 'weight'),
            Forms\Components\Toggle::make('is_free')
                ->label(__('Free Shipping'))
                ->default(false)
                ->helperText(__('If enabled, shipping cost will be zero for this rule.')),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('method.name')->label(__('Method'))->sortable(),
            Tables\Columns\TextColumn::make('zone.name')->label(__('Zone'))->sortable(),
            Tables\Columns\TextColumn::make('calculation_type')->sortable(),
            Tables\Columns\TextColumn::make('min_subtotal')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('cost')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('cost_per_kg')->money('USD')->sortable(),
            Tables\Columns\IconColumn::make('is_free')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingRules::route('/'),
            'create' => Pages\CreateShippingRule::route('/create'),
            'edit' => Pages\EditShippingRule::route('/{record}/edit'),
        ];
    }
}
