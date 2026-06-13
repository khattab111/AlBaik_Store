<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Traits\TranslationTrait;
use DomainException;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use InvalidArgumentException;

class WalletResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Wallet details'))->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('balance')
                    ->label(__('Balance'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Wallet balance is changed only through wallet transactions.')),
                Forms\Components\TextInput::make('currency_code')
                    ->label(__('Currency'))
                    ->maxLength(3)
                    ->placeholder('USD'),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(Wallet::statusOptions())
                    ->required()
                    ->default(Wallet::STATUS_ACTIVE),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('Email'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label(__('Balance'))
                    ->formatStateUsing(fn (mixed $state, Wallet $record): string => trim(($record->currency_code ?: '').' '.number_format((float) $record->balance, 2)))
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label(__('Currency'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Wallet::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Wallet::STATUS_ACTIVE => 'success',
                        Wallet::STATUS_FROZEN => 'warning',
                        Wallet::STATUS_DISABLED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(Wallet::statusOptions()),
                Tables\Filters\SelectFilter::make('currency_code')
                    ->label(__('Currency'))
                    ->options(fn () => Wallet::query()->whereNotNull('currency_code')->distinct()->pluck('currency_code', 'currency_code')->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('adjust')
                    ->label(__('Adjust balance'))
                    ->icon('heroicon-o-plus-minus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('direction')
                            ->label(__('Direction'))
                            ->options([
                                WalletTransaction::DIRECTION_CREDIT => __('Credit'),
                                WalletTransaction::DIRECTION_DEBIT => __('Debit'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Reason'))
                            ->rows(3)
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (Wallet $record, array $data): void {
                        try {
                            app(WalletService::class)->adjust(
                                $record->user,
                                (float) $data['amount'],
                                $data['direction'],
                                $data['description'],
                            );

                            Notification::make()
                                ->title(__('Wallet transaction completed'))
                                ->success()
                                ->send();
                        } catch (DomainException|InvalidArgumentException $exception) {
                            Notification::make()
                                ->title($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('freeze')
                    ->label(__('Freeze'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Wallet $record): bool => $record->status !== Wallet::STATUS_FROZEN)
                    ->requiresConfirmation()
                    ->action(fn (Wallet $record) => $record->update(['status' => Wallet::STATUS_FROZEN])),
                Tables\Actions\Action::make('activate')
                    ->label(__('Activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Wallet $record): bool => $record->status !== Wallet::STATUS_ACTIVE)
                    ->requiresConfirmation()
                    ->action(fn (Wallet $record) => $record->update(['status' => Wallet::STATUS_ACTIVE])),
                Tables\Actions\Action::make('disable')
                    ->label(__('Disable'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Wallet $record): bool => $record->status !== Wallet::STATUS_DISABLED)
                    ->requiresConfirmation()
                    ->action(fn (Wallet $record) => $record->update(['status' => Wallet::STATUS_DISABLED])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
