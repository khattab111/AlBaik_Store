<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->unique(Coupon::class, 'code', ignoreRecord: true),
            Forms\Components\Select::make('type')->options(['fixed' => __('Fixed'), 'percentage' => __('Percentage')])->required(),
            Forms\Components\TextInput::make('value')->numeric()->required(),
            Forms\Components\TextInput::make('minimum_order_amount')->numeric()->default(0),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('expires_at'),
            Forms\Components\TextInput::make('usage_limit')->numeric(),
            Forms\Components\TextInput::make('used_count')->numeric()->default(0)->disabled()->dehydrated(false),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->sortable(),
            Tables\Columns\TextColumn::make('value')->sortable(),
            Tables\Columns\TextColumn::make('used_count')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
