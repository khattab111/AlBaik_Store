<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class NewsletterSubscriberResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')->label(__('Email'))->email()->required()->unique(NewsletterSubscriber::class, 'email', ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label(__('Name'))->maxLength(255),
            Forms\Components\TextInput::make('phone')->label(__('Phone'))->maxLength(50),
            Forms\Components\Select::make('locale')->label(__('Language'))->options(['ar' => __('Arabic'), 'en' => __('English')])->default('ar'),
            Forms\Components\Select::make('status')->label(__('Status'))->options(NewsletterSubscriber::statusOptions())->default(NewsletterSubscriber::STATUS_ACTIVE)->required(),
            Forms\Components\Select::make('source')->label(__('Source'))->options(NewsletterSubscriber::sourceOptions())->default(NewsletterSubscriber::SOURCE_MANUAL),
            Forms\Components\DateTimePicker::make('verified_at')->label(__('Verified at')),
            Forms\Components\DateTimePicker::make('unsubscribed_at')->label(__('Unsubscribed at'))->disabled()->dehydrated(false),
            Forms\Components\KeyValue::make('metadata')->label(__('Metadata'))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => NewsletterSubscriber::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('source')->label(__('Source'))->formatStateUsing(fn (?string $state): string => $state ? (NewsletterSubscriber::sourceOptions()[$state] ?? $state) : '-')->sortable(),
                Tables\Columns\TextColumn::make('locale')->label(__('Language'))->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('Subscribed at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(NewsletterSubscriber::statusOptions()),
                Tables\Filters\SelectFilter::make('source')->label(__('Source'))->options(NewsletterSubscriber::sourceOptions()),
                Tables\Filters\SelectFilter::make('locale')->label(__('Language'))->options(['ar' => __('Arabic'), 'en' => __('English')]),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')->label(__('Activate'))->icon('heroicon-o-check-circle')->color('success')->action(fn (NewsletterSubscriber $record) => $record->activate()),
                Tables\Actions\Action::make('unsubscribe')->label(__('Unsubscribe'))->icon('heroicon-o-no-symbol')->color('warning')->action(fn (NewsletterSubscriber $record) => $record->unsubscribe()),
                Tables\Actions\Action::make('bounce')->label(__('Mark as bounced'))->icon('heroicon-o-exclamation-triangle')->color('danger')->action(fn (NewsletterSubscriber $record) => $record->markAsBounced()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')->label(__('Export'))->icon('heroicon-o-arrow-down-tray')->action(function (Collection $records) {
                    return response()->streamDownload(function () use ($records): void {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['email', 'name', 'phone', 'locale', 'status', 'source', 'subscribed_at']);

                        foreach ($records as $record) {
                            fputcsv($handle, [
                                $record->email,
                                $record->name,
                                $record->phone,
                                $record->locale,
                                $record->status,
                                $record->source,
                                optional($record->created_at)->toDateTimeString(),
                            ]);
                        }

                        fclose($handle);
                    }, 'newsletter-subscribers.csv', ['Content-Type' => 'text/csv']);
                }),
                Tables\Actions\BulkAction::make('activateSelected')->label(__('Activate selected'))->icon('heroicon-o-check-circle')->action(fn (Collection $records) => $records->each->activate()),
                Tables\Actions\BulkAction::make('unsubscribeSelected')->label(__('Unsubscribe selected'))->icon('heroicon-o-no-symbol')->color('warning')->action(fn (Collection $records) => $records->each->unsubscribe()),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
