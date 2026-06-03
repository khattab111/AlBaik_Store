<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\ShippingCarrierResource\Pages;
use App\Models\ShippingCarrier;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingCarrierResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = ShippingCarrier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 41;

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required()->maxLength(255),
            ]),
            Forms\Components\TextInput::make('slug')->label(__('Slug'))->disabled()->dehydrated(false)->visible(fn ($record): bool => $record !== null),
            Forms\Components\FileUpload::make('logo')
                ->label(__('Logo'))
                ->image()
                ->disk('public')
                ->directory('shipping-carriers')
                ->visibility('public')
                ->imagePreviewHeight('100')
                ->openable()
                ->downloadable(),
            Forms\Components\TextInput::make('tracking_url')->label(__('Tracking URL'))->url()->maxLength(255),
            Forms\Components\TextInput::make('api_endpoint')->label(__('API Endpoint'))->url()->maxLength(255),
            Forms\Components\TextInput::make('api_key')->label(__('API Key'))->password()->revealable()->maxLength(255),
            Forms\Components\Select::make('status')->label(__('Status'))->options(ShippingCarrier::statusOptions())->default(ShippingCarrier::STATUS_ACTIVE)->required(),
            Forms\Components\TextInput::make('sort_order')->label(__('Sort order'))->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label(__('Logo'))->disk('public'),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => ShippingCarrier::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Sort order'))->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ShippingCarrier::statusOptions()),
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
            'index' => Pages\ListShippingCarriers::route('/'),
            'create' => Pages\CreateShippingCarrier::route('/create'),
            'edit' => Pages\EditShippingCarrier::route('/{record}/edit'),
        ];
    }
}
