<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 35;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Review details'))->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('Product'))
                    ->options(fn () => Product::query()->orderBy('id')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->label(__('Rating'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(ProductReview::statusOptions())
                    ->default(ProductReview::STATUS_PENDING)
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('comment')
                    ->label(__('Comment'))
                    ->rows(5),
                Forms\Components\Textarea::make('admin_note')
                    ->label(__('Admin note'))
                    ->rows(3),
            ])->columns(2),
            Forms\Components\Section::make(__('Images'))->schema([
                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label(__('Image'))
                            ->image()
                            ->disk('public')
                            ->directory('product-reviews')
                            ->visibility('public')
                            ->openable()
                            ->downloadable()
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('Sort order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label(__('Product'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label(__('User'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rating')->label(__('Rating'))->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn (string $state): string => ProductReview::statusOptions()[$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('comment')->label(__('Comment'))->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('admin_note')->label(__('Admin note'))->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('Submitted at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(ProductReview::statusOptions()),
                Tables\Filters\SelectFilter::make('rating')->label(__('Rating'))->options([
                    5 => '5',
                    4 => '4',
                    3 => '3',
                    2 => '2',
                    1 => '1',
                ]),
                Tables\Filters\SelectFilter::make('product_id')->label(__('Product'))->relationship('product', 'name')->searchable()->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductReview $record): bool => $record->status !== ProductReview::STATUS_APPROVED)
                    ->action(fn (ProductReview $record) => $record->update([
                        'status' => ProductReview::STATUS_APPROVED,
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ])),
                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ProductReview $record): bool => $record->status !== ProductReview::STATUS_REJECTED)
                    ->action(fn (ProductReview $record) => $record->update([
                        'status' => ProductReview::STATUS_REJECTED,
                        'approved_at' => null,
                        'approved_by' => null,
                    ])),
                Tables\Actions\Action::make('hide')
                    ->label(__('Hide'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (ProductReview $record): bool => $record->status !== ProductReview::STATUS_HIDDEN)
                    ->action(fn (ProductReview $record) => $record->update([
                        'status' => ProductReview::STATUS_HIDDEN,
                    ])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
