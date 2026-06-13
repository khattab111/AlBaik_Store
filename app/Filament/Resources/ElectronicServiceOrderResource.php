<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElectronicServiceOrderResource\Pages;
use App\Models\ElectronicServiceOrder;
use App\Services\WalletService;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElectronicServiceOrderResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = ElectronicServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Electronic Services';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Order details'))->schema([
                Forms\Components\TextInput::make('order_number')->label(__('Order number'))->disabled(),
                Forms\Components\Select::make('user_id')->label(__('User'))->relationship('user', 'name')->disabled(),
                Forms\Components\Select::make('electronic_service_id')->label(__('Service'))->relationship('service', 'name')->disabled(),
                Forms\Components\TextInput::make('amount')->label(__('Amount'))->numeric()->disabled(),
                Forms\Components\Select::make('status')->label(__('Status'))->options(ElectronicServiceOrder::statusOptions())->required(),
                Forms\Components\Select::make('payment_status')->label(__('Payment status'))->options(ElectronicServiceOrder::paymentStatusOptions())->required(),
                Forms\Components\TextInput::make('provider_reference')->label(__('Provider reference')),
                Forms\Components\Textarea::make('admin_note')->label(__('Admin note'))->rows(3)->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make(__('Customer inputs'))->schema([
                Forms\Components\KeyValue::make('customer_inputs')->label(__('Customer inputs'))->disabled(),
                Forms\Components\KeyValue::make('service_snapshot')->label(__('Service snapshot'))->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label(__('Order number'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label(__('User'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.name')->label(__('Service'))->searchable(),
                Tables\Columns\TextColumn::make('amount')->label(__('Amount'))->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => ElectronicServiceOrder::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('Payment status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ElectronicServiceOrder::paymentStatusOptions()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('Created at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(ElectronicServiceOrder::statusOptions()),
                Tables\Filters\SelectFilter::make('electronic_service_id')->label(__('Service'))->relationship('service', 'name')->searchable()->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('processing')
                    ->label(__('Process'))
                    ->icon('heroicon-o-play-circle')
                    ->visible(fn (ElectronicServiceOrder $record): bool => $record->status === ElectronicServiceOrder::STATUS_PENDING)
                    ->action(fn (ElectronicServiceOrder $record) => $record->update(['status' => ElectronicServiceOrder::STATUS_PROCESSING, 'processed_at' => now()])),
                Tables\Actions\Action::make('complete')
                    ->label(__('Complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ElectronicServiceOrder $record): bool => in_array($record->status, [ElectronicServiceOrder::STATUS_PENDING, ElectronicServiceOrder::STATUS_PROCESSING], true))
                    ->action(fn (ElectronicServiceOrder $record) => $record->update(['status' => ElectronicServiceOrder::STATUS_COMPLETED, 'completed_at' => now()])),
                Tables\Actions\Action::make('refund')
                    ->label(__('Refund'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ElectronicServiceOrder $record): bool => $record->payment_status === ElectronicServiceOrder::PAYMENT_PAID)
                    ->action(function (ElectronicServiceOrder $record): void {
                        app(WalletService::class)->refund(
                            $record->user,
                            (float) $record->amount,
                            $record,
                            __('Refund for electronic service order #:number', ['number' => $record->order_number]),
                        );

                        $record->update([
                            'status' => ElectronicServiceOrder::STATUS_REFUNDED,
                            'payment_status' => ElectronicServiceOrder::PAYMENT_REFUNDED,
                            'cancelled_at' => now(),
                        ]);

                        Notification::make()->title(__('Amount refunded to customer wallet.'))->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElectronicServiceOrders::route('/'),
            'edit' => Pages\EditElectronicServiceOrder::route('/{record}/edit'),
        ];
    }
}
