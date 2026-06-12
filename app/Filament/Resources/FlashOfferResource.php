<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\FlashOfferResource\Pages;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlashOfferResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = FlashOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Offer Content'))->schema([
                static::translatableTabs(fn (string $code): array => [
                    Forms\Components\TextInput::make("title.{$code}")
                        ->label(__('Title'))
                        ->helperText(__('Write a clear promotional title that explains the customer benefit.'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make("description.{$code}")
                        ->label(__('Description'))
                        ->helperText(__('Explain the offer conditions, included products, and any important restrictions.'))
                        ->rows(3),
                ]),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->helperText(__('Generated automatically from the offer title when it is created.'))
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record): bool => $record !== null),
            ]),
            Forms\Components\Section::make(__('Offer Rules'))->schema([
                Forms\Components\Select::make('type')
                    ->label(__('Offer Type'))
                    ->options(FlashOffer::typeOptions())
                    ->required()
                    ->live()
                    ->default(FlashOffer::TYPE_PERCENTAGE_DISCOUNT)
                    ->helperText(__('Choose how the final price or shipping benefit will be calculated.')),
                Forms\Components\Select::make('offer_scope')
                    ->label(__('Offer Scope'))
                    ->options(FlashOffer::scopeOptions())
                    ->required()
                    ->default(FlashOffer::SCOPE_PRODUCT)
                    ->helperText(__('Product applies to selected products, Bundle sells several products together, and Cart applies to the whole order.')),
                Forms\Components\Select::make('audience')
                    ->label(__('Audience'))
                    ->options(FlashOffer::audienceOptions())
                    ->required()
                    ->default(FlashOffer::AUDIENCE_RETAIL)
                    ->helperText(__('Retail offers appear in the public store, wholesale offers appear only for approved wholesale customers, and both appears in both areas.')),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(FlashOffer::statusOptions())
                    ->required()
                    ->default(FlashOffer::STATUS_DRAFT)
                    ->helperText(__('Only active offers within their valid date range and available quantity appear in the storefront.')),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label(__('Starts at'))
                    ->helperText(__('Leave empty to make the offer available immediately after activating it.')),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label(__('Ends at'))
                    ->after('starts_at')
                    ->helperText(__('Leave empty if the offer has no scheduled end date.')),
                Forms\Components\TextInput::make('priority')
                    ->label(__('Priority'))
                    ->numeric()
                    ->default(0)
                    ->helperText(__('Higher priority offers are evaluated first.')),
                Forms\Components\Select::make('discount_type')
                    ->label(__('Discount type'))
                    ->options([
                        'percentage' => __('Percentage'),
                        'fixed' => __('Fixed amount'),
                    ])
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), [
                        FlashOffer::TYPE_PERCENTAGE_DISCOUNT,
                        FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT,
                    ], true))
                    ->required(fn (Forms\Get $get): bool => in_array($get('type'), [
                        FlashOffer::TYPE_PERCENTAGE_DISCOUNT,
                        FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT,
                    ], true))
                    ->default(fn (Forms\Get $get): string => $get('type') === FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT ? 'fixed' : 'percentage')
                    ->helperText(__('Percentage subtracts a percentage from the original price; fixed subtracts a specific amount.')),
                Forms\Components\TextInput::make('discount_value')
                    ->label(__('Discount value'))
                    ->numeric()
                    ->minValue(0)
                    ->helperText(__('Enter 15 for a 15% discount, or enter the amount to subtract for a fixed discount.'))
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), [
                        FlashOffer::TYPE_PERCENTAGE_DISCOUNT,
                        FlashOffer::TYPE_FIXED_AMOUNT_DISCOUNT,
                    ], true)),
                Forms\Components\TextInput::make('fixed_price')
                    ->label(__('Fixed price'))
                    ->numeric()
                    ->minValue(0)
                    ->helperText(__('The final price paid for the selected quantity or the complete bundle.'))
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), [
                        FlashOffer::TYPE_FIXED_PRICE_QUANTITY,
                        FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
                    ], true)),
                Forms\Components\TextInput::make('max_quantity')
                    ->label(__('Max quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->helperText(__('Maximum total quantity that can be sold through this offer.')),
                Forms\Components\TextInput::make('sold_quantity')
                    ->label(__('Sold quantity'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Updated automatically after successful purchases and cannot be edited manually.'))
                    ->visible(fn ($record): bool => $record !== null),
                Forms\Components\Toggle::make('free_shipping')
                    ->label(__('Free Shipping'))
                    ->helperText(__('Enable this when the selected offer should provide a shipping benefit.'))
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), [
                        FlashOffer::TYPE_FREE_SHIPPING_PRODUCT,
                        FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
                        FlashOffer::TYPE_CART_FREE_SHIPPING,
                    ], true)),
                Forms\Components\Select::make('free_shipping_scope')
                    ->label(__('Free Shipping Scope'))
                    ->options(FlashOffer::freeShippingScopeOptions())
                    ->required()
                    ->default(FlashOffer::FREE_SHIPPING_NONE)
                    ->helperText(__('Offer only exempts products inside this offer; Whole cart makes shipping free for the complete order.')),
                Forms\Components\TextInput::make('min_order_amount')
                    ->label(__('Min order amount'))
                    ->numeric()
                    ->minValue(0)
                    ->helperText(__('Reserved for future restrictions and is not currently enforced during checkout.')),
                Forms\Components\TextInput::make('usage_limit')
                    ->label(__('Usage limit'))
                    ->numeric()
                    ->minValue(1)
                    ->helperText(__('Reserved for future usage tracking and is not currently enforced during checkout.')),
                Forms\Components\TextInput::make('usage_per_user')
                    ->label(__('Usage per user'))
                    ->numeric()
                    ->minValue(1)
                    ->helperText(__('Reserved for future per-customer limits and is not currently enforced during checkout.')),
            ])->columns(2),
            Forms\Components\Section::make(__('Offer Products'))->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label(__('Product'))
                            ->helperText(__('Select a product included in the offer. Add multiple rows for bundles or buy X get Y offers.'))
                            ->options(fn () => Product::where('status', true)->orderBy('id')->get()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('Quantity'))
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->helperText(__('Number of units of this product included in one offer unit.')),
                        Forms\Components\TextInput::make('original_price')
                            ->label(__('Original price'))
                            ->numeric()
                            ->minValue(0)
                            ->helperText(__('Optional snapshot. Leave empty to use the current product price.')),
                        Forms\Components\TextInput::make('offer_price')
                            ->label(__('Offer price'))
                            ->numeric()
                            ->minValue(0)
                            ->helperText(__('Used by fixed price, bundle, and buy X get Y offers.')),
                        Forms\Components\Toggle::make('is_free_item')
                            ->label(__('Free Item'))
                            ->helperText(__('Mark the free product in buy X get Y offers.')),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->minItems(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label(__('Title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label(__('Offer Type'))->badge()->formatStateUsing(fn (string $state): string => FlashOffer::typeOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('offer_scope')->label(__('Offer Scope'))->badge()->formatStateUsing(fn (string $state): string => FlashOffer::scopeOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('audience')->label(__('Audience'))->badge()->formatStateUsing(fn (string $state): string => FlashOffer::audienceOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => FlashOffer::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('starts_at')->label(__('Starts at'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->label(__('Ends at'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('priority')->label(__('Priority'))->sortable(),
                Tables\Columns\TextColumn::make('sold_quantity')->label(__('Sold')),
                Tables\Columns\TextColumn::make('max_quantity')->label(__('Limit')),
                Tables\Columns\IconColumn::make('free_shipping')->label(__('Free Shipping'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(FlashOffer::statusOptions()),
                Tables\Filters\SelectFilter::make('type')->options(FlashOffer::typeOptions()),
                Tables\Filters\SelectFilter::make('audience')->options(FlashOffer::audienceOptions()),
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
            'index' => Pages\ListFlashOffers::route('/'),
            'create' => Pages\CreateFlashOffer::route('/create'),
            'edit' => Pages\EditFlashOffer::route('/{record}/edit'),
        ];
    }
}
