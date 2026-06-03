<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterTemplateResource\Pages;
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

class NewsletterTemplateResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = NewsletterTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 31;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Template details'))->schema([
                Forms\Components\TextInput::make('name')->label(__('Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->label(__('Slug'))->disabled()->dehydrated(false)->visible(fn ($record): bool => $record !== null),
                Forms\Components\Select::make('category')->label(__('Category'))->options(NewsletterTemplate::categoryOptions())->default(NewsletterTemplate::CATEGORY_CUSTOM)->required(),
                Forms\Components\Select::make('status')->label(__('Status'))->options(NewsletterTemplate::statusOptions())->default(NewsletterTemplate::STATUS_ACTIVE)->required(),
                Forms\Components\Toggle::make('is_default')->label(__('Is default')),
            ])->columns(2),
            Forms\Components\Tabs::make(__('Content'))->tabs([
                Forms\Components\Tabs\Tab::make(__('Arabic'))->schema([
                    Forms\Components\TextInput::make('subject_ar')->label(__('Subject'))->required()->maxLength(255),
                    Forms\Components\TextInput::make('preheader_ar')->label(__('Preheader'))->maxLength(255),
                    Forms\Components\RichEditor::make('content_ar')->label(__('Content'))->required()->columnSpanFull(),
                ]),
                Forms\Components\Tabs\Tab::make(__('English'))->schema([
                    Forms\Components\TextInput::make('subject_en')->label(__('Subject'))->maxLength(255),
                    Forms\Components\TextInput::make('preheader_en')->label(__('Preheader'))->maxLength(255),
                    Forms\Components\RichEditor::make('content_en')->label(__('Content'))->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
            Forms\Components\KeyValue::make('design')->label(__('Design'))->helperText(__('Supported variables: :variables', ['variables' => '{{store_name}}, {{subscriber_name}}, {{email}}, {{unsubscribe_url}}, {{campaign_title}}, {{current_date}}']))->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label(__('Category'))->badge()->formatStateUsing(fn (string $state): string => NewsletterTemplate::categoryOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => NewsletterTemplate::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\IconColumn::make('is_default')->label(__('Is default'))->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->label(__('Category'))->options(NewsletterTemplate::categoryOptions()),
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(NewsletterTemplate::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')->label(__('Preview'))->icon('heroicon-o-eye')->modalSubmitAction(false)->modalCancelActionLabel(__('Close'))->modalContent(fn (NewsletterTemplate $record) => view('filament.newsletter.preview', [
                    'subject' => $record->subjectFor(app()->getLocale()),
                    'preheader' => $record->preheaderFor(app()->getLocale()),
                    'content' => $record->contentFor(app()->getLocale()),
                ])),
                Tables\Actions\Action::make('sendTest')->label(__('Send Test Email'))->icon('heroicon-o-paper-airplane')->form([
                    Forms\Components\TextInput::make('email')->label(__('Email'))->email()->required()->default(fn () => auth()->user()?->email),
                    Forms\Components\Select::make('locale')->label(__('Language'))->options(['ar' => __('Arabic'), 'en' => __('English')])->default(app()->getLocale()),
                ])->action(function (NewsletterTemplate $record, array $data): void {
                    $subscriber = new NewsletterSubscriber([
                        'email' => $data['email'],
                        'name' => auth()->user()?->name,
                        'locale' => $data['locale'],
                        'status' => NewsletterSubscriber::STATUS_ACTIVE,
                        'unsubscribe_token' => 'test-token',
                    ]);
                    $campaign = new NewsletterCampaign([
                        'title' => $record->name,
                        'subject' => $record->subjectFor($data['locale']),
                        'preheader' => $record->preheaderFor($data['locale']),
                        'content' => $record->contentFor($data['locale']),
                        'locale' => $data['locale'],
                    ]);
                    Mail::to($data['email'])->send(new NewsletterMessage($campaign, $subscriber));
                    Notification::make()->title(__('Test email sent.'))->success()->send();
                }),
                Tables\Actions\Action::make('duplicate')->label(__('Duplicate Template'))->icon('heroicon-o-document-duplicate')->action(function (NewsletterTemplate $record): void {
                    $copy = $record->replicate(['slug', 'is_default']);
                    $copy->name = $record->name.' - '.__('Copy');
                    $copy->is_default = false;
                    $copy->save();
                    Notification::make()->title(__('Template duplicated.'))->success()->send();
                }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterTemplates::route('/'),
            'create' => Pages\CreateNewsletterTemplate::route('/create'),
            'edit' => Pages\EditNewsletterTemplate::route('/{record}/edit'),
        ];
    }
}
