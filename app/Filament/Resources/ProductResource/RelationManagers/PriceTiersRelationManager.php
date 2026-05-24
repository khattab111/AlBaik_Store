<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PriceTiersRelationManager extends RelationManager
{
    protected static string $relationship = 'priceTiers';

    protected static ?string $title = 'Price Tiers';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label(__('Price Type'))
                ->options([
                    'retail' => __('Retail'),
                    'wholesale' => __('Wholesale'),
                ])
                ->required()
                ->default('retail'),
            Forms\Components\TextInput::make('min_quantity')
                ->label(__('Minimum Quantity'))
                ->required()
                ->numeric()
                ->minValue(1)
                ->default(1),
            Forms\Components\TextInput::make('price')
                ->label(__('Price'))
                ->required()
                ->numeric()
                ->minValue(0),
            Forms\Components\TextInput::make('sort_order')
                ->label(__('Sort Order'))
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->label(__('Active'))
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                Tables\Columns\TextColumn::make('type')->label(__('Type'))->badge()->sortable(),
                Tables\Columns\TextColumn::make('min_quantity')->label(__('Minimum Quantity'))->sortable(),
                Tables\Columns\TextColumn::make('price')->label(__('Price'))->money('USD')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Sort Order'))->sortable(),
            ])
            ->defaultSort('min_quantity')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'retail' => __('Retail'),
                        'wholesale' => __('Wholesale'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
