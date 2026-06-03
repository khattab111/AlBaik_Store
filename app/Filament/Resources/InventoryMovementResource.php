<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryMovementResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('warehouse_id')->options(fn () => Warehouse::where('is_active', true)->get()->pluck('name', 'id'))->required()->searchable(),
            Forms\Components\Select::make('product_variant_id')->options(fn () => ProductVariant::query()->pluck('sku', 'id'))->required()->searchable(),
            Forms\Components\Select::make('type')->options(['purchase' => __('Purchase'), 'sale' => __('Sale'), 'adjustment' => __('Adjustment'), 'return' => __('Return')])->required(),
            Forms\Components\TextInput::make('quantity')->numeric()->required(),
            Forms\Components\TextInput::make('source_type'),
            Forms\Components\TextInput::make('source_id')->numeric(),
            Forms\Components\KeyValue::make('metadata'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('warehouse.name')->sortable(),
            Tables\Columns\TextColumn::make('variant.sku')->searchable(),
            Tables\Columns\TextColumn::make('type')->sortable(),
            Tables\Columns\TextColumn::make('quantity')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
            'edit' => Pages\EditInventoryMovement::route('/{record}/edit'),
        ];
    }
}
