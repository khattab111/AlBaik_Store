<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')->options(fn () => Product::query()->get()->pluck('name', 'id'))->required()->searchable(),
            Forms\Components\Select::make('user_id')->options(fn () => User::query()->pluck('name', 'id'))->required()->searchable(),
            Forms\Components\TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required(),
            static::translatableTabs(fn (string $code): array => [
                Forms\Components\TextInput::make("title.{$code}")->label(__('Title')),
                Forms\Components\Textarea::make("comment.{$code}")->label(__('Comment'))->rows(4),
            ]),
            Forms\Components\Toggle::make('is_published')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('user.name')->sortable(),
            Tables\Columns\TextColumn::make('rating')->sortable(),
            Tables\Columns\IconColumn::make('is_published')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
