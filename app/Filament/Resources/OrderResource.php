<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = Order::class;

   protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Sales';

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record?->order_number;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => __('Pending'),
                    'paid' => __('Paid'),
                    'processing' => __('Processing'),
                    'shipped' => __('Shipped'),
                    'delivered' => __('Delivered'),
                    'cancelled' => __('Cancelled'),
                    'refunded' => __('Refunded'),
                ])
                ->required(),
            Forms\Components\Section::make(__('Customer Shipping Information'))->schema([
                Forms\Components\TextInput::make('customer_phone')->disabled(),
                Forms\Components\TextInput::make('customer_whatsapp')->disabled(),
                Forms\Components\TextInput::make('shipping_country')->disabled(),
                Forms\Components\TextInput::make('shipping_city')->disabled(),
                Forms\Components\TextInput::make('shipping_town')->disabled(),
                Forms\Components\TextInput::make('shipping_street')->disabled(),
            ])->columns(2),
            Forms\Components\TextInput::make('tracking_number')->maxLength(255),
            Forms\Components\Textarea::make('notes')->rows(4),
            Forms\Components\Repeater::make('timeline')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('from_status')->disabled(),
                    Forms\Components\TextInput::make('to_status')->disabled(),
                    Forms\Components\Textarea::make('note')->disabled(),
                ])
                ->disabled()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label(__('Customer'))->sortable(),
                Tables\Columns\TextColumn::make('customer_phone')->label(__('Phone'))->toggleable(),
                Tables\Columns\TextColumn::make('customer_whatsapp')->label(__('WhatsApp'))->toggleable(),
                Tables\Columns\TextColumn::make('shipping_city')->label(__('City'))->toggleable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->label(__('Invoice'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Order $record): string => route('admin.orders.invoice', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('mark_paid')
                    ->label(__('Mark Paid'))
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (Order $record): bool => $record->status === 'pending')
                    ->action(function (Order $record): void {
                        $record->payments()->latest()->first()?->update([
                            'status' => 'paid',
                            'submitted_at' => now(),
                        ]);

                        static::transitionOrder($record, 'paid');
                    }),
                Tables\Actions\Action::make('process')
                    ->label(__('Process'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'paid'], true))
                    ->action(fn (Order $record) => static::transitionOrder($record, 'processing')),
                Tables\Actions\Action::make('ship')
                    ->label(__('Ship'))
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record): bool => $record->status === 'processing')
                    ->form([
                        Forms\Components\TextInput::make('tracking_number')->required()->maxLength(255),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update(['tracking_number' => $data['tracking_number']]);
                        static::transitionOrder($record, 'shipped');
                    }),
                Tables\Actions\Action::make('deliver')
                    ->label(__('Deliver'))
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Order $record): bool => $record->status === 'shipped')
                    ->action(fn (Order $record) => static::transitionOrder($record, 'delivered')),
                Tables\Actions\Action::make('cancel')
                    ->label(__('Cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'processing'], true))
                    ->action(fn (Order $record) => static::transitionOrder($record, 'cancelled')),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    private static function transitionOrder(Order $order, string $status): void
    {
        app(OrderWorkflowService::class)->transition($order, $status, auth()->user());

        Notification::make()
            ->title(__('Order updated'))
            ->body(__('Order :number moved to :status.', ['number' => $order->order_number, 'status' => __($status)]))
            ->success()
            ->send();
    }
}
