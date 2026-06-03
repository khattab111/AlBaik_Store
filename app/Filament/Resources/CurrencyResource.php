<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required(),
            ]),
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(3)
                ->unique(Currency::class, 'code', ignoreRecord: true)
                ->helperText(__('Use ISO code, for example USD, TRY, SYP.')),
            Forms\Components\TextInput::make('symbol')->required()->maxLength(8),
            Forms\Components\TextInput::make('rate')
                ->required()
                ->numeric()
                ->minValue(0.000001)
                ->helperText(__('Exchange rate against the default currency. USD must stay 1.')),
            Forms\Components\Toggle::make('is_default')
                ->helperText(__('Only one default currency is allowed. Enabling this will unset the previous default currency.')),
            Forms\Components\Toggle::make('status')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('symbol'),
            Tables\Columns\TextColumn::make('rate')->sortable(),
            Tables\Columns\IconColumn::make('is_default')->boolean(),
            Tables\Columns\IconColumn::make('status')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
