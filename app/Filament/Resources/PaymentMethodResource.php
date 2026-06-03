<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Models\PaymentMethod;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required(),
                Forms\Components\Textarea::make("description.{$code}")->label(__('Description'))->rows(3),
            ]),
            Forms\Components\TextInput::make('slug')->required()->unique(PaymentMethod::class, 'slug', ignoreRecord: true),
            Forms\Components\Select::make('type')->options([
                'cod' => __('Cash on Delivery'),
                'bank_transfer' => __('Bank Transfer'),
                'manual' => __('Manual'),
            ])->required(),
            Forms\Components\FileUpload::make('image')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('payment-methods')
                ->visibility('public')
                ->imagePreviewHeight('120')
                ->openable()
                ->downloadable(),
            Forms\Components\TextInput::make('wallet_url')
                ->label(__('Wallet Link'))
                ->maxLength(255)
                ->helperText(__('Wallet link, account number, phone number, or transfer destination shown to the customer.')),
            Forms\Components\FileUpload::make('barcode_image')
                ->label(__('Barcode Image'))
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('payment-method-barcodes')
                ->visibility('public')
                ->imagePreviewHeight('120')
                ->openable()
                ->downloadable(),
            Forms\Components\KeyValue::make('settings'),
            Forms\Components\TextInput::make('fee')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->disk('public')->circular(),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->sortable(),
            Tables\Columns\TextColumn::make('wallet_url')->label(__('Wallet Link'))->limit(40),
            Tables\Columns\TextColumn::make('fee')->money('USD')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
