<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\PriceTiersRelationManager;
use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tag;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Product Information'))->schema([
                static::translatableTabs(fn (string $code): array => [
                    Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required()->maxLength(255),
                    Forms\Components\Textarea::make("short_description.{$code}")->label(__('Short description'))->rows(3),
                    Forms\Components\RichEditor::make("description.{$code}")->label(__('Description')),
                    Forms\Components\TextInput::make("seo_title.{$code}")->label(__('SEO Title'))->maxLength(80),
                    Forms\Components\TextInput::make("seo_description.{$code}")->label(__('SEO Description'))->maxLength(160),
                ]),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->helperText(__('Generated automatically when the product is created and kept stable after that.'))
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record): bool => $record !== null),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->helperText(__('Internal stock code used to identify the product in inventory and orders.'))
                    ->required()
                    ->unique(Product::class, 'sku', ignoreRecord: true),
                Forms\Components\TextInput::make('barcode')
                    ->label(__('Barcode'))
                    ->helperText(__('Optional barcode printed on the physical product or scanned by warehouse devices.'))
                    ->maxLength(255),
                Forms\Components\Select::make('brand_id')->options(fn () => Brand::where('status', true)->get()->pluck('name', 'id'))->searchable(),
                Forms\Components\Select::make('supplier_id')->options(fn () => Supplier::where('is_active', true)->get()->pluck('name', 'id'))->searchable(),
                Forms\Components\Select::make('category_id')->options(fn () => Category::where('status', true)->get()->pluck('name', 'id'))->searchable(),
                Forms\Components\Select::make('tags')->relationship('tags', 'name')->getOptionLabelFromRecordUsing(fn (Tag $record): string => $record->name)->multiple()->preload(),
                Forms\Components\TextInput::make('retail_price')->label(__('Retail Price'))->required()->numeric()->helperText(__('One shared price for all languages.')),
                Forms\Components\TextInput::make('wholesale_price')
                    ->label(__('Wholesale price'))
                    ->numeric()
                    ->minValue(0)
                    ->helperText(__('Fallback wholesale unit price when no active wholesale price tier matches the quantity.')),
                Forms\Components\TextInput::make('wholesale_minimum_quantity')
                    ->label(__('Wholesale minimum quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->helperText(__('Lowest quantity allowed when a wholesale customer adds this product from the wholesale area.')),
                Forms\Components\Toggle::make('is_wholesale_available')
                    ->label(__('Available for wholesale'))
                    ->helperText(__('Enable this product in the wholesale products page. Keep it off for retail-only products.'))
                    ->default(false),
                Forms\Components\TextInput::make('stock_quantity')->label(__('Stock Quantity'))->numeric()->default(0)->helperText(__('One shared stock quantity for all languages.')),
                Forms\Components\TextInput::make('low_stock_threshold')->label(__('Low stock threshold'))->numeric()->default(5),
                Forms\Components\TextInput::make('weight')->label(__('Weight'))->numeric()->default(0)->helperText(__('Used by shipping rules and is not language-specific.')),
                Forms\Components\TextInput::make('length')->label(__('Length'))->numeric(),
                Forms\Components\TextInput::make('width')->label(__('Width'))->numeric(),
                Forms\Components\TextInput::make('height')->label(__('Height'))->numeric(),
                Forms\Components\Toggle::make('requires_shipping')->label(__('Requires Shipping'))->default(true),
                Forms\Components\Toggle::make('free_shipping')->label(__('Free Shipping'))->default(false),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('status')->default(true),
            ]),
            Forms\Components\Section::make(__('Variants'))->schema([
                Forms\Components\Repeater::make('variants')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('sku')->label('SKU')->helperText(__('Variant-specific stock code.'))->required()->maxLength(255),
                        Forms\Components\TextInput::make('barcode')->label(__('Barcode'))->helperText(__('Optional barcode for this variant.'))->maxLength(255),
                        Forms\Components\KeyValue::make('attributes')->keyLabel('Attribute')->valueLabel('Value'),
                        Forms\Components\TextInput::make('stock')->label(__('Stock'))->numeric()->default(0)->helperText(__('Variant stock is shared across all languages.')),
                        Forms\Components\TextInput::make('reserved_stock')->label(__('Reserved Stock'))->numeric()->default(0)->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('low_stock_threshold')->label(__('Low stock threshold'))->numeric()->default(5),
                        Forms\Components\TextInput::make('price')->label(__('Price'))->numeric()->default(0)->helperText(__('Variant price is shared across all languages.')),
                    ])
                    ->columns(2),
            ])->collapsed(),
            Forms\Components\Section::make(__('Images'))->schema([
                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->openable()
                            ->downloadable()
                            ->required(),
                        static::translatableTabs(fn (string $code): array => [
                            Forms\Components\TextInput::make("alt_text.{$code}")->label(__('Alt text'))->maxLength(255),
                        ]),
                        Forms\Components\Toggle::make('is_primary'),
                    ])
                    ->columns(2),
            ])->collapsed(),
            Forms\Components\Section::make(__('Media'))->schema([
                Forms\Components\TextInput::make('video_url')->url(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.path')
                    ->label(__('Image'))
                    ->disk('public')
                    ->square()
                    ->stacked()
                    ->limit(3),
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->label(__('Brand'))->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label(__('Supplier'))->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label(__('Category'))->sortable(),
                Tables\Columns\TextColumn::make('retail_price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('wholesale_price')->label(__('Wholesale price'))->money('USD')->sortable(),
                Tables\Columns\IconColumn::make('is_wholesale_available')->label(__('Wholesale'))->boolean(),
                Tables\Columns\TextColumn::make('average_rating')->label(__('Average rating'))->sortable(),
                Tables\Columns\TextColumn::make('reviews_count')->label(__('Reviews'))->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')->sortable(),
                Tables\Columns\IconColumn::make('status')->boolean(),
            ])
            ->filters([])
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PriceTiersRelationManager::class,
        ];
    }
}
