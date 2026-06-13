<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletDepositRequestResource\Pages;
use App\Models\User;
use App\Models\WalletDepositRequest;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class WalletDepositRequestResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = WalletDepositRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 72;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Deposit request details'))->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->numeric()
                    ->minValue(0.01)
                    ->required(),
                Forms\Components\TextInput::make('payment_method')
                    ->label(__('Payment method'))
                    ->maxLength(100),
                Forms\Components\FileUpload::make('proof_image')
                    ->label(__('Proof image'))
                    ->image()
                    ->disk('public')
                    ->directory('wallet-deposits')
                    ->visibility('public')
                    ->openable()
                    ->downloadable(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(WalletDepositRequest::statusOptions())
                    ->default(WalletDepositRequest::STATUS_PENDING)
                    ->required(),
                Forms\Components\Textarea::make('admin_note')
                    ->label(__('Admin note'))
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label(__('User'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label(__('Email'))->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('amount')->label(__('Amount'))->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label(__('Payment method'))->limit(30),
                Tables\Columns\ImageColumn::make('proof_image')->label(__('Proof image'))->disk('public')->square(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WalletDepositRequest::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        WalletDepositRequest::STATUS_APPROVED => 'success',
                        WalletDepositRequest::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('approvedBy.name')->label(__('Approved by'))->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('Created at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(WalletDepositRequest::statusOptions()),
                Tables\Filters\SelectFilter::make('user_id')->label(__('User'))->relationship('user', 'name')->searchable()->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WalletDepositRequest $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function (WalletDepositRequest $record): void {
                        DB::transaction(function () use ($record): void {
                            $locked = WalletDepositRequest::whereKey($record->id)->lockForUpdate()->firstOrFail();

                            if (! $locked->isPending()) {
                                return;
                            }

                            app(WalletService::class)->credit(
                                $locked->user,
                                (float) $locked->amount,
                                WalletTransaction::TYPE_DEPOSIT,
                                $locked,
                                __('Wallet deposit request #:id approved', ['id' => $locked->id]),
                            );

                            $locked->update([
                                'status' => WalletDepositRequest::STATUS_APPROVED,
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);
                        });

                        Notification::make()->title(__('Wallet has been charged successfully'))->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WalletDepositRequest $record): bool => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label(__('Admin note'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(fn (WalletDepositRequest $record, array $data) => $record->update([
                        'status' => WalletDepositRequest::STATUS_REJECTED,
                        'admin_note' => $data['admin_note'],
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletDepositRequests::route('/'),
            'create' => Pages\CreateWalletDepositRequest::route('/create'),
            'edit' => Pages\EditWalletDepositRequest::route('/{record}/edit'),
        ];
    }
}
