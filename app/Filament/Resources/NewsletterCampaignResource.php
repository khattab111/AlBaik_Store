<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterCampaignResource\Pages;
use App\Jobs\SendNewsletterCampaignJob;
use App\Mail\NewsletterMessage;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterTemplate;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class NewsletterCampaignResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = NewsletterCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 32;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Campaign details'))->schema([
                Forms\Components\TextInput::make('title')->label(__('Title'))->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->label(__('Slug'))->disabled()->dehydrated(false)->visible(fn ($record): bool => $record !== null),
                Forms\Components\Select::make('template_id')
                    ->label(__('Template'))
                    ->options(fn () => NewsletterTemplate::where('status', NewsletterTemplate::STATUS_ACTIVE)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?int $state, Forms\Set $set, Forms\Get $get): void {
                        $template = $state ? NewsletterTemplate::find($state) : null;
                        $locale = $get('locale') ?: 'ar';

                        if ($template) {
                            $set('subject', $template->subjectFor($locale));
                            $set('preheader', $template->preheaderFor($locale));
                            $set('content', $template->contentFor($locale));
                        }
                    }),
                Forms\Components\Select::make('locale')->label(__('Language'))->options(['ar' => __('Arabic'), 'en' => __('English')])->default('ar')->required()->live(),
                Forms\Components\Select::make('status')->label(__('Status'))->options(NewsletterCampaign::statusOptions())->default(NewsletterCampaign::STATUS_DRAFT)->required(),
                Forms\Components\DateTimePicker::make('scheduled_at')->label(__('Scheduled at')),
            ])->columns(2),
            Forms\Components\Section::make(__('Message'))->schema([
                Forms\Components\TextInput::make('subject')->label(__('Subject'))->required()->maxLength(255),
                Forms\Components\TextInput::make('preheader')->label(__('Preheader'))->maxLength(255),
                Forms\Components\RichEditor::make('content')->label(__('Content'))->required()->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make(__('Audience'))->schema([
                Forms\Components\Group::make([
                    Forms\Components\Select::make('preset')->label(__('Audience'))->options(NewsletterCampaign::audiencePresetOptions())->default('all_active'),
                    Forms\Components\Select::make('locale')->label(__('Language'))->options(['ar' => __('Arabic'), 'en' => __('English')]),
                    Forms\Components\Select::make('source')->label(__('Source'))->options(NewsletterSubscriber::sourceOptions()),
                    Forms\Components\Select::make('subscriber_ids')
                        ->label(__('Selected subscribers'))
                        ->multiple()
                        ->searchable()
                        ->options(fn () => NewsletterSubscriber::query()->orderBy('email')->pluck('email', 'id')),
                ])->statePath('audience')->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label(__('Title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('subject')->label(__('Subject'))->searchable()->limit(40),
                Tables\Columns\TextColumn::make('locale')->label(__('Language'))->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => NewsletterCampaign::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')->label(__('Scheduled at'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('sent_at')->label(__('Sent at'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('stats.sent')->label(__('Sent'))->default(0),
                Tables\Columns\TextColumn::make('stats.failed')->label(__('Failed'))->default(0),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(NewsletterCampaign::statusOptions()),
                Tables\Filters\SelectFilter::make('locale')->label(__('Language'))->options(['ar' => __('Arabic'), 'en' => __('English')]),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')->label(__('Preview'))->icon('heroicon-o-eye')->modalSubmitAction(false)->modalCancelActionLabel(__('Close'))->modalContent(fn (NewsletterCampaign $record) => view('filament.newsletter.preview', [
                    'subject' => $record->subject,
                    'preheader' => $record->preheader,
                    'content' => $record->content,
                ])),
                Tables\Actions\Action::make('sendTest')->label(__('Send Test Email'))->icon('heroicon-o-paper-airplane')->form([
                    Forms\Components\TextInput::make('email')->label(__('Email'))->email()->required()->default(fn () => auth()->user()?->email),
                ])->action(function (NewsletterCampaign $record, array $data): void {
                    $subscriber = new NewsletterSubscriber([
                        'email' => $data['email'],
                        'name' => auth()->user()?->name,
                        'locale' => $record->locale,
                        'status' => NewsletterSubscriber::STATUS_ACTIVE,
                        'unsubscribe_token' => 'test-token',
                    ]);
                    Mail::to($data['email'])->send(new NewsletterMessage($record, $subscriber));
                    Notification::make()->title(__('Test email sent.'))->success()->send();
                }),
                Tables\Actions\Action::make('sendNow')->label(__('Send Now'))->icon('heroicon-o-rocket-launch')->color('success')->requiresConfirmation()->visible(fn (NewsletterCampaign $record): bool => $record->status !== NewsletterCampaign::STATUS_SENT)->action(function (NewsletterCampaign $record): void {
                    if (! $record->canBeSent()) {
                        Notification::make()->title(__('Campaign cannot be sent because it is empty or already sent.'))->danger()->send();
                        return;
                    }

                    $record->forceFill(['status' => NewsletterCampaign::STATUS_QUEUED])->save();
                    SendNewsletterCampaignJob::dispatch($record->id);
                    Notification::make()->title(__('Campaign queued for sending.'))->success()->send();
                }),
                Tables\Actions\Action::make('schedule')->label(__('Schedule Campaign'))->icon('heroicon-o-clock')->form([
                    Forms\Components\DateTimePicker::make('scheduled_at')->label(__('Scheduled at'))->required()->minDate(now()),
                ])->action(function (NewsletterCampaign $record, array $data): void {
                    $record->forceFill([
                        'status' => NewsletterCampaign::STATUS_SCHEDULED,
                        'scheduled_at' => $data['scheduled_at'],
                    ])->save();
                    Notification::make()->title(__('Campaign scheduled.'))->success()->send();
                }),
                Tables\Actions\Action::make('cancel')->label(__('Cancel Campaign'))->icon('heroicon-o-x-circle')->color('warning')->visible(fn (NewsletterCampaign $record): bool => in_array($record->status, [NewsletterCampaign::STATUS_SCHEDULED, NewsletterCampaign::STATUS_QUEUED], true))->action(fn (NewsletterCampaign $record) => $record->forceFill(['status' => NewsletterCampaign::STATUS_CANCELLED])->save()),
                Tables\Actions\Action::make('duplicate')->label(__('Duplicate Campaign'))->icon('heroicon-o-document-duplicate')->action(function (NewsletterCampaign $record): void {
                    $copy = $record->replicate(['slug', 'status', 'started_at', 'sent_at', 'stats']);
                    $copy->title = $record->title.' - '.__('Copy');
                    $copy->status = NewsletterCampaign::STATUS_DRAFT;
                    $copy->scheduled_at = null;
                    $copy->save();
                    Notification::make()->title(__('Campaign duplicated.'))->success()->send();
                }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterCampaigns::route('/'),
            'create' => Pages\CreateNewsletterCampaign::route('/create'),
            'edit' => Pages\EditNewsletterCampaign::route('/{record}/edit'),
        ];
    }
}
