<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\ElectronicServiceResource\Pages;
use App\Models\ElectronicService;
use App\Models\ElectronicServiceCategory;
use App\Models\ElectronicServiceProvider;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElectronicServiceResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = ElectronicService::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Electronic Services';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Service publishing'))
                ->description(__('Choose the supplier service, where it appears, your profit percentage, and whether customers can order it.'))
                ->schema([
                    Forms\Components\Select::make('electronic_service_provider_id')
                        ->label(__('Supplier'))
                        ->options(fn () => ElectronicServiceProvider::query()->orderBy('id')->get()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set): mixed => $set('provider_service_id', null)),
                    Forms\Components\TextInput::make('provider_service_id')
                        ->label(__('Supplier product'))
                        ->helperText(__('This is the product/service code from the supplier. It is filled automatically when services are synced.'))
                        ->disabled(fn ($record): bool => filled($record?->provider_service_id))
                        ->dehydrated(true)
                        ->required(),
                    Forms\Components\Select::make('electronic_service_category_id')
                        ->label(__('Display category'))
                        ->options(fn () => ElectronicServiceCategory::query()->orderBy('sort_order')->get()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('Active'))
                        ->helperText(__('If disabled, customers cannot order this service.'))
                        ->default(true),
                    Forms\Components\Toggle::make('is_visible')
                        ->label(__('Visible in services portal'))
                        ->helperText(__('Show or hide this service from the services pages.'))
                        ->default(true),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('Pricing'))
                ->description(__('The customer price is calculated from the supplier price plus your profit percentage.'))
                ->schema([
                    Forms\Components\TextInput::make('provider_cost_price')
                        ->label(__('Supplier price'))
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set): mixed => static::refreshCalculatedPrices($get, $set)),
                    Forms\Components\TextInput::make('retail_profit_value')
                        ->label(__('Retail profit percentage'))
                        ->suffix('%')
                        ->numeric()
                        ->minValue(0)
                        ->default(15)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set): mixed => static::refreshCalculatedPrices($get, $set)),
                    Forms\Components\TextInput::make('price')
                        ->label(__('Customer price'))
                        ->numeric()
                        ->disabled()
                        ->dehydrated(true)
                        ->helperText(__('Calculated automatically when saving.')),
                    Forms\Components\TextInput::make('wholesale_profit_value')
                        ->label(__('Wholesale profit percentage'))
                        ->suffix('%')
                        ->numeric()
                        ->minValue(0)
                        ->default(8)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set): mixed => static::refreshCalculatedPrices($get, $set)),
                    Forms\Components\TextInput::make('wholesale_price')
                        ->label(__('Wholesale price'))
                        ->numeric()
                        ->disabled()
                        ->dehydrated(true)
                        ->helperText(__('Calculated automatically when saving.')),
                ])
                ->columns(3),

            Forms\Components\Section::make(__('Service text'))
                ->description(__('Usually synced from the supplier. Edit only if you want a clearer name or instructions for customers.'))
                ->schema([
                    static::translatableTabs(fn (string $code): array => [
                        Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required()->maxLength(255),
                        Forms\Components\Textarea::make("description.{$code}")->label(__('Description'))->rows(2),
                        Forms\Components\Textarea::make("instructions.{$code}")->label(__('Customer instructions'))->rows(2),
                    ])->columnSpanFull(),
                ])
                ->collapsed()
                ->columnSpanFull(),

            Forms\Components\Section::make(__('Advanced order fields'))
                ->description(__('These fields are normally synced from the supplier, such as player ID or phone number.'))
                ->schema([
                    Forms\Components\Repeater::make('fields_schema')
                        ->label(__('Order fields'))
                        ->schema([
                            Forms\Components\TextInput::make('name')->label(__('Field name'))->required()->regex('/^[A-Za-z0-9_]+$/'),
                            Forms\Components\TextInput::make('label')->label(__('Field label'))->required(),
                            Forms\Components\Select::make('type')->label(__('Field type'))->options([
                                'text' => __('Text'),
                                'textarea' => __('Textarea'),
                                'email' => __('Email'),
                                'tel' => __('Phone'),
                                'number' => __('Number'),
                                'url' => __('URL'),
                                'select' => __('Select'),
                            ])->default('text')->required(),
                            Forms\Components\TextInput::make('options')->label(__('Options'))->helperText(__('For select fields only. Separate options by comma.')),
                            Forms\Components\Toggle::make('required')->label(__('Required'))->default(true),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('min_amount')->label(__('Minimum amount'))->numeric(),
                    Forms\Components\TextInput::make('max_amount')->label(__('Maximum amount'))->numeric(),
                    Forms\Components\TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
                    Forms\Components\Toggle::make('requires_admin_review')->label(__('Requires admin review'))->default(false),
                    Forms\Components\Toggle::make('is_available')->label(__('Available at provider'))->default(true),
                ])
                ->collapsed()
                ->columns(2),

            Forms\Components\Section::make(__('Technical data'))
                ->schema([
                    Forms\Components\Hidden::make('service_type')->default(ElectronicService::TYPE_API),
                    Forms\Components\Hidden::make('retail_profit_type')->default(ElectronicServiceProvider::PROFIT_PERCENTAGE),
                    Forms\Components\Hidden::make('wholesale_profit_type')->default(ElectronicServiceProvider::PROFIT_PERCENTAGE),
                    Forms\Components\TextInput::make('slug')->label(__('Slug'))->disabled()->dehydrated(false)->visible(fn ($record): bool => $record !== null),
                    Forms\Components\TextInput::make('cost')->label(__('Internal cost'))->numeric()->disabled()->dehydrated(true),
                    Forms\Components\KeyValue::make('metadata')->label(__('Provider metadata'))->disabled()->columnSpanFull(),
                ])
                ->collapsed()
                ->columns(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('provider.name')->label(__('Provider'))->placeholder(__('Store team')),
                Tables\Columns\TextColumn::make('category.name')->label(__('Category'))->sortable(),
                Tables\Columns\TextColumn::make('provider_service_id')->label(__('Supplier product'))->toggleable(),
                Tables\Columns\TextColumn::make('provider_cost_price')->label(__('Supplier price'))->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('retail_profit_value')->label(__('Profit'))->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('price')->label(__('Customer price'))->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('orders_count')->label(__('Orders'))->counts('orders')->sortable(),
                Tables\Columns\IconColumn::make('is_visible')->label(__('Visible'))->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('electronic_service_provider_id')->label(__('Supplier'))->relationship('provider', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('electronic_service_category_id')->label(__('Category'))->relationship('category', 'name')->searchable()->preload(),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElectronicServices::route('/'),
            'create' => Pages\CreateElectronicService::route('/create'),
            'edit' => Pages\EditElectronicService::route('/{record}/edit'),
        ];
    }

    public static function preparePricingData(array $data): array
    {
        $cost = (float) ($data['provider_cost_price'] ?? $data['cost'] ?? 0);
        $retailProfit = (float) ($data['retail_profit_value'] ?? 0);
        $wholesaleProfit = (float) ($data['wholesale_profit_value'] ?? 0);

        $data['retail_profit_type'] = ElectronicServiceProvider::PROFIT_PERCENTAGE;
        $data['wholesale_profit_type'] = ElectronicServiceProvider::PROFIT_PERCENTAGE;
        $data['price'] = round($cost + ($cost * ($retailProfit / 100)), 2);
        $data['wholesale_price'] = round($cost + ($cost * ($wholesaleProfit / 100)), 2);
        $data['cost'] = $cost;

        return $data;
    }

    protected static function refreshCalculatedPrices(Get $get, Set $set): void
    {
        $data = static::preparePricingData([
            'provider_cost_price' => $get('provider_cost_price'),
            'retail_profit_value' => $get('retail_profit_value'),
            'wholesale_profit_value' => $get('wholesale_profit_value'),
        ]);

        $set('price', $data['price']);
        $set('wholesale_price', $data['wholesale_price']);
        $set('cost', $data['cost']);
    }
}
