<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletTransactionResource\Pages;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletTransactionResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = WalletTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 71;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Transaction details'))->schema([
                Forms\Components\TextInput::make('transaction_number')
                    ->label(__('Transaction number'))
                    ->disabled(),
                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->disabled(),
                Forms\Components\Select::make('type')
                    ->label(__('Type'))
                    ->options(WalletTransaction::typeOptions())
                    ->disabled(),
                Forms\Components\Select::make('direction')
                    ->label(__('Direction'))
                    ->options(WalletTransaction::directionOptions())
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->disabled(),
                Forms\Components\TextInput::make('balance_before')
                    ->label(__('Balance before'))
                    ->disabled(),
                Forms\Components\TextInput::make('balance_after')
                    ->label(__('Balance after'))
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(WalletTransaction::statusOptions())
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metadata')
                    ->label(__('Metadata'))
                    ->disabled()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label(__('Transaction number'))
                    ->searchable()
                    ->copyable()
                    ->limit(10),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WalletTransaction::typeOptions()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('direction')
                    ->label(__('Direction'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WalletTransaction::directionOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === WalletTransaction::DIRECTION_CREDIT ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->formatStateUsing(fn (mixed $state, WalletTransaction $record): string => number_format((float) $record->amount, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_before')
                    ->label(__('Balance before'))
                    ->formatStateUsing(fn (mixed $state, WalletTransaction $record): string => number_format((float) $record->balance_before, 2))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label(__('Balance after'))
                    ->formatStateUsing(fn (mixed $state, WalletTransaction $record): string => number_format((float) $record->balance_after, 2))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WalletTransaction::statusOptions()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label(__('Reference'))
                    ->formatStateUsing(fn (?string $state, WalletTransaction $record): string => $state ? class_basename($state).' #'.$record->reference_id : '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label(__('Created by'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(WalletTransaction::typeOptions()),
                Tables\Filters\SelectFilter::make('direction')
                    ->label(__('Direction'))
                    ->options(WalletTransaction::directionOptions()),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(WalletTransaction::statusOptions()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletTransactions::route('/'),
            'view' => Pages\ViewWalletTransaction::route('/{record}'),
        ];
    }
}
