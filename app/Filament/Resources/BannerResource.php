<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Models\Banner;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('banner_tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('Content'))
                        ->schema([
                            static::translatableTabs(fn (string $code): array => [
                                Forms\Components\TextInput::make("title.{$code}")->label(__('Title'))->required(),
                                Forms\Components\TextInput::make("eyebrow.{$code}")->label(__('Eyebrow')),
                                Forms\Components\Textarea::make("subtitle.{$code}")->label(__('Subtitle'))->rows(3),
                                Forms\Components\TextInput::make("primary_button_text.{$code}")->label(__('Primary button')),
                                Forms\Components\TextInput::make("secondary_button_text.{$code}")->label(__('Secondary button')),
                            ]),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('Actions and media'))
                        ->schema([
                            Forms\Components\FileUpload::make('image')->image()->directory('banners')->disk('public')->visibility('public'),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('url')->label(__('Primary URL')),
                                Forms\Components\TextInput::make('secondary_url')->label(__('Secondary URL')),
                            ]),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('Display'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->helperText(__('Generated automatically when the banner is created and kept stable after that.'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record): bool => $record !== null),
                                Forms\Components\Select::make('placement')
                                    ->label(__('Display placement'))
                                    ->options(Banner::placementOptions())
                                    ->native(false)
                                    ->required()
                                    ->default(Banner::PLACEMENT_HOME_AFTER_HERO)
                                    ->helperText(__('Choose where this banner appears in the storefront template.')),
                                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                                Forms\Components\ColorPicker::make('background_color')->label(__('Background color')),
                                Forms\Components\ColorPicker::make('text_color')->label(__('Text color')),
                                Forms\Components\DateTimePicker::make('starts_at')->label(__('Starts at')),
                                Forms\Components\DateTimePicker::make('ends_at')->label(__('Ends at')),
                            ]),
                            Forms\Components\Toggle::make('is_active')->default(true),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->disk('public'),
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('placement')
                ->label(__('Placement'))
                ->formatStateUsing(fn (?string $state): string => Banner::placementOptions()[$state] ?? (string) $state)
                ->badge()
                ->sortable(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
