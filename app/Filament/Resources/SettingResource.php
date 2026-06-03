<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    use TranslationTrait;
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

 public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Admin') ?? false;
}
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('group')->required()->default('general'),
            Forms\Components\TextInput::make('key')->required()->unique(Setting::class, 'key', ignoreRecord: true),
            Forms\Components\Select::make('type')->options(['string' => __('String'), 'boolean' => __('Boolean'), 'number' => __('Number'), 'json' => 'JSON'])->required(),
            Forms\Components\KeyValue::make('value'),
            Forms\Components\Toggle::make('is_public')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('group')->sortable(),
            Tables\Columns\TextColumn::make('key')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type'),
            Tables\Columns\IconColumn::make('is_public')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
