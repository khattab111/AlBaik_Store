<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    use TranslationTrait;

    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Reports';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('log_name')->disabled(),
            Forms\Components\TextInput::make('event')->disabled(),
            Forms\Components\Textarea::make('description')->disabled(),
            Forms\Components\KeyValue::make('properties')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('log_name')->sortable(),
            Tables\Columns\TextColumn::make('event')->sortable(),
            Tables\Columns\TextColumn::make('description')->searchable()->limit(80),
            Tables\Columns\TextColumn::make('causer.name')->label(__('Causer')),
        ])->defaultSort('created_at', 'desc')->actions([
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
