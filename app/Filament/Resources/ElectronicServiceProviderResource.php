<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsTranslatableForms;
use App\Filament\Resources\ElectronicServiceProviderResource\Pages;
use App\Models\ElectronicServiceProvider;
use App\Services\Providers\Services\ProviderManager;
use App\Services\Providers\Services\ServiceSyncService;
use App\Traits\TranslationTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElectronicServiceProviderResource extends Resource
{
    use BuildsTranslatableForms, TranslationTrait;

    protected static ?string $model = ElectronicServiceProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationGroup = 'Electronic Services';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Basic information'))
                ->description(__('Start here. Manual providers only need this section.'))
                ->schema([
                    static::translatableTabs(fn (string $code): array => [
                        Forms\Components\TextInput::make("name.{$code}")->label(__('Name'))->required()->maxLength(255),
                    ])->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')->label(__('Slug'))->disabled()->dehydrated(false)->visible(fn ($record): bool => $record !== null),
                    Forms\Components\Select::make('provider_type')
                        ->label(__('Provider type'))
                        ->options(ElectronicServiceProvider::typeOptions())
                        ->default(ElectronicServiceProvider::TYPE_MANUAL)
                        ->helperText(__('Choose Manual for internal processing, Generic API for configurable providers, or Custom gateway for coded integrations.'))
                        ->live()
                        ->required(),
                    Forms\Components\Select::make('status')->label(__('Status'))->options(ElectronicServiceProvider::statusOptions())->default(ElectronicServiceProvider::STATUS_ACTIVE)->required(),
                    Forms\Components\TextInput::make('contact_name')->label(__('Contact name'))->maxLength(255),
                    Forms\Components\TextInput::make('contact_email')->label(__('Contact email'))->email()->maxLength(255),
                    Forms\Components\TextInput::make('contact_phone')->label(__('Contact phone'))->maxLength(255),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('API connection'))
                ->description(__('Only needed when the provider has an API. Tokens are stored encrypted.'))
                ->visible(fn (Forms\Get $get): bool => $get('provider_type') !== ElectronicServiceProvider::TYPE_MANUAL)
                ->schema([
                    Forms\Components\TextInput::make('base_url')
                        ->label(__('Base URL'))
                        ->url()
                        ->placeholder('https://provider.com/api')
                        ->helperText(__('The main API URL without the endpoint path.')),
                    Forms\Components\Select::make('auth_type')
                        ->label(__('Auth type'))
                        ->options(ElectronicServiceProvider::authTypeOptions())
                        ->default(ElectronicServiceProvider::AUTH_NO_AUTH)
                        ->live(),
                    Forms\Components\TextInput::make('auth_config.token')
                        ->label(__('Token'))
                        ->password()
                        ->revealable()
                        ->visible(fn (Forms\Get $get): bool => $get('auth_type') === ElectronicServiceProvider::AUTH_BEARER_TOKEN)
                        ->helperText(__('Paste the provider token here. It will not be shown to customers.')),
                    Forms\Components\TextInput::make('auth_config.header_name')
                        ->label(__('Header name'))
                        ->default('X-API-Key')
                        ->visible(fn (Forms\Get $get): bool => $get('auth_type') === ElectronicServiceProvider::AUTH_API_KEY_HEADER),
                    Forms\Components\TextInput::make('auth_config.api_key')
                        ->label(__('API key'))
                        ->password()
                        ->revealable()
                        ->visible(fn (Forms\Get $get): bool => $get('auth_type') === ElectronicServiceProvider::AUTH_API_KEY_HEADER),
                    Forms\Components\TextInput::make('auth_config.key_name')
                        ->label(__('Key name'))
                        ->default('api_key')
                        ->visible(fn (Forms\Get $get): bool => in_array($get('auth_type'), [ElectronicServiceProvider::AUTH_QUERY_KEY, ElectronicServiceProvider::AUTH_BODY_KEY], true)),
                    Forms\Components\TextInput::make('auth_config.key_value')
                        ->label(__('Key value'))
                        ->password()
                        ->revealable()
                        ->visible(fn (Forms\Get $get): bool => in_array($get('auth_type'), [ElectronicServiceProvider::AUTH_QUERY_KEY, ElectronicServiceProvider::AUTH_BODY_KEY], true)),
                    Forms\Components\TextInput::make('auth_config.username')
                        ->label(__('Username'))
                        ->visible(fn (Forms\Get $get): bool => $get('auth_type') === ElectronicServiceProvider::AUTH_BASIC_AUTH),
                    Forms\Components\TextInput::make('auth_config.password')
                        ->label(__('Password'))
                        ->password()
                        ->revealable()
                        ->visible(fn (Forms\Get $get): bool => $get('auth_type') === ElectronicServiceProvider::AUTH_BASIC_AUTH),
                    Forms\Components\TextInput::make('gateway_class')
                        ->label(__('Gateway class'))
                        ->placeholder('App\\Services\\Providers\\Gateways\\ThreeLwanGateway')
                        ->visible(fn (Forms\Get $get): bool => $get('provider_type') === ElectronicServiceProvider::TYPE_CUSTOM_GATEWAY)
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('auth_config')
                        ->label(__('Custom auth headers'))
                        ->helperText(__('For Bearer Token use token. For API Key Header use header_name and api_key. For Query or Body Key use key_name and key_value.'))
                        ->visible(fn (Forms\Get $get): bool => $get('auth_type') === ElectronicServiceProvider::AUTH_CUSTOM_HEADERS)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('API endpoints'))
                ->description(__('Set the provider endpoint paths. Keep it simple: URL and method for each operation.'))
                ->visible(fn (Forms\Get $get): bool => $get('provider_type') !== ElectronicServiceProvider::TYPE_MANUAL)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('endpoints_config.services.url')
                        ->label(__('Services endpoint'))
                        ->placeholder('/services')
                        ->helperText(__('Endpoint used to fetch provider services.')),
                    Forms\Components\Select::make('endpoints_config.services.method')
                        ->label(__('Method'))
                        ->options(['GET' => 'GET', 'POST' => 'POST'])
                        ->default('GET'),
                    Forms\Components\TextInput::make('endpoints_config.services.list_path')
                        ->label(__('Services list path'))
                        ->placeholder('data')
                        ->helperText(__('Leave empty if the API returns the services list directly.')),
                    Forms\Components\TextInput::make('endpoints_config.create_order.url')
                        ->label(__('Create order endpoint'))
                        ->placeholder('/orders'),
                    Forms\Components\Select::make('endpoints_config.create_order.method')
                        ->label(__('Method'))
                        ->options(['POST' => 'POST', 'GET' => 'GET', 'PUT' => 'PUT'])
                        ->default('POST'),
                    Forms\Components\TextInput::make('endpoints_config.status.url')
                        ->label(__('Order status endpoint'))
                        ->placeholder('/orders/{provider_order_id}'),
                    Forms\Components\Select::make('endpoints_config.status.method')
                        ->label(__('Method'))
                        ->options(['GET' => 'GET', 'POST' => 'POST'])
                        ->default('GET'),
                    Forms\Components\TextInput::make('endpoints_config.balance.url')
                        ->label(__('Balance endpoint'))
                        ->placeholder('/balance'),
                    Forms\Components\Select::make('endpoints_config.balance.method')
                        ->label(__('Method'))
                        ->options(['GET' => 'GET', 'POST' => 'POST'])
                        ->default('GET'),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('Field mapping'))
                ->description(__('Use this only when the provider field names differ from our system field names.'))
                ->visible(fn (Forms\Get $get): bool => $get('provider_type') !== ElectronicServiceProvider::TYPE_MANUAL)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('response_mapping.provider_service_id')
                        ->label(__('Provider service ID field'))
                        ->placeholder('id'),
                    Forms\Components\TextInput::make('response_mapping.name')
                        ->label(__('Service name field'))
                        ->placeholder('name'),
                    Forms\Components\TextInput::make('response_mapping.cost_price')
                        ->label(__('Cost price field'))
                        ->placeholder('price'),
                    Forms\Components\TextInput::make('response_mapping.category')
                        ->label(__('Category field'))
                        ->placeholder('category'),
                    Forms\Components\TextInput::make('response_mapping.available')
                        ->label(__('Availability field'))
                        ->placeholder('available'),
                    Forms\Components\TextInput::make('response_mapping.params')
                        ->label(__('Required fields path'))
                        ->placeholder('params'),
                    Forms\Components\TextInput::make('response_mapping.provider_order_id')
                        ->label(__('Provider order ID field'))
                        ->placeholder('order_id'),
                    Forms\Components\TextInput::make('response_mapping.status')
                        ->label(__('Status field'))
                        ->placeholder('status'),
                    Forms\Components\KeyValue::make('request_mapping')
                        ->label(__('Order request fields'))
                        ->helperText(__('Add only fields required by the provider. Example: service_id = {provider_service_id}, phone = {input.phone}.'))
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('status_mapping')
                        ->label(__('Status mapping'))
                        ->helperText(__('Examples: accept = completed, wait = processing, reject = failed.'))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('Pricing defaults'))
                ->description(__('These values are used when services are synced from an API provider.'))
                ->schema([
                    Forms\Components\Select::make('default_profit_type')
                        ->label(__('Retail profit type'))
                        ->options(ElectronicServiceProvider::profitTypeOptions())
                        ->default(ElectronicServiceProvider::PROFIT_PERCENTAGE),
                    Forms\Components\TextInput::make('default_profit_value')->label(__('Retail profit value'))->numeric()->default(20),
                    Forms\Components\Select::make('default_wholesale_profit_type')
                        ->label(__('Wholesale profit type'))
                        ->options(ElectronicServiceProvider::profitTypeOptions())
                        ->default(ElectronicServiceProvider::PROFIT_PERCENTAGE),
                    Forms\Components\TextInput::make('default_wholesale_profit_value')->label(__('Wholesale profit value'))->numeric()->default(10),
                    Forms\Components\Toggle::make('auto_sync_services')->label(__('Auto sync services'))->default(false),
                    Forms\Components\Toggle::make('auto_sync_prices')->label(__('Auto sync prices'))->default(true),
                ])
                ->columns(2)
                ->collapsed(),

            Forms\Components\Section::make(__('Advanced notes'))
                ->collapsed()
                ->schema([
                    Forms\Components\KeyValue::make('settings')
                        ->label(__('Provider settings'))
                        ->helperText(__('Optional provider configuration for future custom gateways.'))
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('admin_note')->label(__('Admin note'))->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('provider_type')
                    ->label(__('Provider type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ElectronicServiceProvider::typeOptions()[$state] ?? (string) $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ElectronicServiceProvider::statusOptions()[$state] ?? (string) $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('services_count')->label(__('Services'))->counts('services')->sortable(),
                Tables\Columns\TextColumn::make('last_sync_at')->label(__('Last sync'))->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('testConnection')
                    ->label(__('Test'))
                    ->icon('heroicon-o-signal')
                    ->action(function (ElectronicServiceProvider $record): void {
                        $response = app(ProviderManager::class)->gateway($record)->testConnection();
                        $notification = Notification::make()
                            ->title($response->successful ? __('Connection successful') : __('Connection failed'))
                            ->body($response->message);

                        ($response->successful ? $notification->success() : $notification->danger())->send();
                    }),
                Tables\Actions\Action::make('balance')
                    ->label(__('Balance'))
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (ElectronicServiceProvider $record): bool => $record->isApiProvider())
                    ->action(function (ElectronicServiceProvider $record): void {
                        $response = app(ProviderManager::class)->gateway($record)->getBalance();
                        $notification = Notification::make()
                            ->title(__('Provider balance'))
                            ->body($response->message ?: json_encode($response->data));

                        ($response->successful ? $notification->success() : $notification->danger())->send();
                    }),
                Tables\Actions\Action::make('syncServices')
                    ->label(__('Sync Services'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (ElectronicServiceProvider $record): bool => $record->isApiProvider())
                    ->requiresConfirmation()
                    ->action(function (ElectronicServiceProvider $record): void {
                        $result = app(ServiceSyncService::class)->sync($record);

                        Notification::make()
                            ->title(__('Services synced'))
                            ->body(__('Created: :created, Updated: :updated', $result))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElectronicServiceProviders::route('/'),
            'create' => Pages\CreateElectronicServiceProvider::route('/create'),
            'edit' => Pages\EditElectronicServiceProvider::route('/{record}/edit'),
        ];
    }

    public static function normalizeSecretAuthConfig(array $data, array $currentAuthConfig = []): array
    {
        $authConfig = $data['auth_config'] ?? [];

        if (! is_array($authConfig)) {
            return $data;
        }

        foreach (['token', 'api_key', 'key_value', 'password'] as $secretKey) {
            if (array_key_exists($secretKey, $authConfig) && blank($authConfig[$secretKey])) {
                if (filled($currentAuthConfig[$secretKey] ?? null)) {
                    $authConfig[$secretKey] = $currentAuthConfig[$secretKey];
                } else {
                    unset($authConfig[$secretKey]);
                }
            }
        }

        $data['auth_config'] = $authConfig;

        return $data;
    }
}
