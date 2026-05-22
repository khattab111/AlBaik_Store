<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Product Information'))->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->unique(Product::class, 'slug', ignoreRecord: true),
                Forms\Components\TextInput::make('sku')->required()->unique(Product::class, 'sku', ignoreRecord: true),
                Forms\Components\TextInput::make('barcode')->maxLength(255),
                Forms\Components\Select::make('brand_id')->relationship('brand', 'name')->searchable(),
                Forms\Components\Select::make('supplier_id')->relationship('supplier', 'name')->searchable(),
                Forms\Components\Select::make('category_id')->relationship('category', 'name')->searchable(),
                Forms\Components\Select::make('tags')->relationship('tags', 'name')->multiple()->preload(),
                Forms\Components\TextInput::make('retail_price')->required()->numeric(),
                Forms\Components\TextInput::make('wholesale_price')->numeric(),
                Forms\Components\TextInput::make('wholesale_minimum_quantity')->numeric(),
                Forms\Components\TextInput::make('stock_quantity')->numeric()->default(0),
                Forms\Components\TextInput::make('low_stock_threshold')->numeric()->default(5),
                Forms\Components\TextInput::make('weight')->numeric()->default(0),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('status')->default(true),
            ]),
            Forms\Components\Section::make(__('Variants'))->schema([
                Forms\Components\Repeater::make('variants')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('sku')->required()->maxLength(255),
                        Forms\Components\TextInput::make('barcode')->maxLength(255),
                        Forms\Components\KeyValue::make('attributes')->keyLabel('Attribute')->valueLabel('Value'),
                        Forms\Components\TextInput::make('stock')->numeric()->default(0),
                        Forms\Components\TextInput::make('reserved_stock')->numeric()->default(0)->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('low_stock_threshold')->numeric()->default(5),
                        Forms\Components\TextInput::make('price')->numeric()->default(0),
                    ])
                    ->columns(2),
            ])->collapsed(),
            Forms\Components\Section::make(__('Images'))->schema([
                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->schema([
                        Forms\Components\FileUpload::make('path')->image()->directory('products'),
                        Forms\Components\TextInput::make('alt_text')->maxLength(255),
                        Forms\Components\Toggle::make('is_primary'),
                    ])
                    ->columns(3),
            ])->collapsed(),
            Forms\Components\Section::make(__('Description & SEO'))->schema([
                Forms\Components\Textarea::make('short_description')->rows(3),
                Forms\Components\RichEditor::make('description'),
                Forms\Components\TextInput::make('video_url')->url(),
                Forms\Components\TextInput::make('seo_title')->maxLength(80),
                Forms\Components\TextInput::make('seo_description')->maxLength(160),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->label(__('Brand'))->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label(__('Supplier'))->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label(__('Category'))->sortable(),
                Tables\Columns\TextColumn::make('retail_price')->money('USD')->sortable(),
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
}
