<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Models\Banner;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HeroSlideResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'hero-slides';

    public static function getNavigationLabel(): string
    {
        return __('Hero Section');
    }

    public static function getModelLabel(): string
    {
        return __('Hero Slide');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Hero Slides');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('placement', 'home');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('placement')->default('home'),
            Forms\Components\Tabs::make('hero_slide_tabs')
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
                    Forms\Components\Tabs\Tab::make(__('CTA and image'))
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label(__('Marketing image'))
                                ->image()
                                ->directory('hero-slides')
                                ->disk('public')
                                ->visibility('public')
                                ->required(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('url')->label(__('Primary URL'))->placeholder('/products'),
                                Forms\Components\TextInput::make('secondary_url')->label(__('Secondary URL'))->placeholder('/offers'),
                            ]),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('Display'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->required(),
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
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label(__('Image')),
                Tables\Columns\TextColumn::make('title')->label(__('Title'))->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->toggleable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
