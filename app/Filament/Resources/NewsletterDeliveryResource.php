<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterDeliveryResource\Pages;
use App\Jobs\SendNewsletterEmailJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterDeliveryResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = NewsletterDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 33;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('campaign_id')->label(__('Campaign'))->relationship('campaign', 'title')->disabled(),
            Forms\Components\TextInput::make('email')->label(__('Email'))->disabled(),
            Forms\Components\TextInput::make('subject')->label(__('Subject'))->disabled(),
            Forms\Components\Select::make('status')->label(__('Status'))->options(NewsletterDelivery::statusOptions())->disabled(),
            Forms\Components\Textarea::make('error_message')->label(__('Error message'))->disabled()->columnSpanFull(),
            Forms\Components\DateTimePicker::make('sent_at')->label(__('Sent at'))->disabled(),
            Forms\Components\DateTimePicker::make('opened_at')->label(__('Opened at'))->disabled(),
            Forms\Components\DateTimePicker::make('clicked_at')->label(__('Clicked at'))->disabled(),
            Forms\Components\KeyValue::make('metadata')->label(__('Metadata'))->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign.title')->label(__('Campaign'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('subject')->label(__('Subject'))->limit(40),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => NewsletterDelivery::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('error_message')->label(__('Error message'))->limit(50)->toggleable(),
                Tables\Columns\TextColumn::make('sent_at')->label(__('Sent at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campaign_id')->label(__('Campaign'))->options(fn () => NewsletterCampaign::query()->orderByDesc('id')->pluck('title', 'id')),
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(NewsletterDelivery::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')->label(__('Retry'))->icon('heroicon-o-arrow-path')->visible(fn (NewsletterDelivery $record): bool => $record->status === NewsletterDelivery::STATUS_FAILED)->action(function (NewsletterDelivery $record): void {
                    $record->forceFill([
                        'status' => NewsletterDelivery::STATUS_PENDING,
                        'error_message' => null,
                    ])->save();
                    SendNewsletterEmailJob::dispatch($record->id);
                    Notification::make()->title(__('Delivery queued again.'))->success()->send();
                }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterDeliveries::route('/'),
            'edit' => Pages\EditNewsletterDelivery::route('/{record}/edit'),
        ];
    }
}
