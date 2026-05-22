<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('order_id')->relationship('order', 'order_number')->required()->searchable(),
            Forms\Components\Select::make('payment_method_id')->relationship('method', 'name')->searchable(),
            Forms\Components\TextInput::make('driver')->required(),
            Forms\Components\Select::make('status')->options([
                'pending' => __('Pending'),
                'awaiting_transfer' => __('Awaiting Transfer'),
                'manual_review' => __('Manual Review'),
                'paid' => __('Paid'),
                'failed' => __('Failed'),
                'refunded' => __('Refunded'),
            ])->required(),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\TextInput::make('transaction_reference'),
            Forms\Components\FileUpload::make('receipt_image')
                ->label(__('Payment Receipt'))
                ->image()
                ->directory('payment-receipts')
                ->visibility('public'),
            Forms\Components\DateTimePicker::make('submitted_at'),
            Forms\Components\KeyValue::make('payload'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('order.order_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('method.name')->sortable(),
            Tables\Columns\TextColumn::make('status')->sortable(),
            Tables\Columns\ImageColumn::make('receipt_image')->label(__('Receipt')),
            Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\Action::make('approve')
                ->label(__('Approve Payment'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Payment $record): bool => $record->status !== 'paid')
                ->requiresConfirmation()
                ->action(function (Payment $record): void {
                    $record->update(['status' => 'paid', 'submitted_at' => $record->submitted_at ?? now()]);

                    if ($record->order && $record->order->status === 'pending') {
                        app(\App\Services\OrderWorkflowService::class)->transition($record->order, 'paid', auth()->user(), __('Payment approved by admin.'));
                    }
                }),
            Tables\Actions\Action::make('reject')
                ->label(__('Reject Payment'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Payment $record): bool => $record->status !== 'failed')
                ->requiresConfirmation()
                ->action(fn (Payment $record): bool => $record->update(['status' => 'failed'])),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
