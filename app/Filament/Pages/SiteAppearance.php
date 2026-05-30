<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SiteSettingService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class SiteAppearance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.site-appearance';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Site Appearance');
    }

    public function getTitle(): string
    {
        return __('Site Appearance');
    }

    public function mount(SiteSettingService $settings): void
    {
        $this->form->fill([
            'store_name_ar' => $settings->localized('store.name', null, 'ar'),
            'store_name_en' => $settings->localized('store.name', null, 'en'),
            'store_tagline_ar' => $settings->localized('store.tagline', null, 'ar'),
            'store_tagline_en' => $settings->localized('store.tagline', null, 'en'),
            'store_short_description_ar' => $settings->localized('store.short_description', null, 'ar'),
            'store_short_description_en' => $settings->localized('store.short_description', null, 'en'),
            'store_logo' => $this->fileState($settings->get('store.logo')),
            'store_favicon' => $this->fileState($settings->get('store.favicon')),
            'store_default_product_image' => $this->fileState($settings->get('store.default_product_image')),
            'store_primary_color' => $settings->get('store.primary_color', '#111111'),
            'store_primary_hover_color' => $settings->get('store.primary_hover_color', '#2a2a2a'),
            'store_accent_color' => $settings->get('store.accent_color', '#d99a16'),
            'store_topbar_color' => $settings->get('store.topbar_color', '#111111'),
            'store_header_bg_color' => $settings->get('store.header_bg_color', '#ffffff'),
            'store_nav_bg_color' => $settings->get('store.nav_bg_color', '#ffffff'),
            'store_body_bg_color' => $settings->get('store.body_bg_color', '#fafafa'),
            'store_surface_color' => $settings->get('store.surface_color', '#ffffff'),
            'store_surface_tint_color' => $settings->get('store.surface_tint_color', '#f5f6f8'),
            'store_text_color' => $settings->get('store.text_color', '#111111'),
            'store_muted_text_color' => $settings->get('store.muted_text_color', '#6b7280'),
            'store_border_color' => $settings->get('store.border_color', '#e5e7eb'),
            'store_hero_overlay_from' => $settings->get('store.hero_overlay_from', 'rgba(255,255,255,.06)'),
            'store_hero_overlay_to' => $settings->get('store.hero_overlay_to', 'rgba(255,255,255,.42)'),
            'contact_email' => $settings->get('contact.email'),
            'contact_phone' => $settings->get('contact.phone'),
            'contact_whatsapp' => $settings->get('contact.whatsapp'),
            'contact_address_ar' => $settings->localized('contact.address', null, 'ar'),
            'contact_address_en' => $settings->localized('contact.address', null, 'en'),
            'contact_working_hours_ar' => $settings->localized('contact.working_hours', null, 'ar'),
            'contact_working_hours_en' => $settings->localized('contact.working_hours', null, 'en'),
            'contact_map_url' => $settings->get('contact.map_url'),
            'social_facebook' => $settings->get('social.facebook'),
            'social_instagram' => $settings->get('social.instagram'),
            'social_youtube' => $settings->get('social.youtube'),
            'social_tiktok' => $settings->get('social.tiktok'),
            'social_x' => $settings->get('social.x'),
            'social_linkedin' => $settings->get('social.linkedin'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Tabs::make('site_settings_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Identity'))
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('store_name_ar')->label(__('Store name Arabic'))->required(),
                                    Forms\Components\TextInput::make('store_name_en')->label(__('Store name English'))->required(),
                                    Forms\Components\TextInput::make('store_tagline_ar')->label(__('Tagline Arabic')),
                                    Forms\Components\TextInput::make('store_tagline_en')->label(__('Tagline English')),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Textarea::make('store_short_description_ar')->label(__('Short description Arabic'))->rows(3),
                                    Forms\Components\Textarea::make('store_short_description_en')->label(__('Short description English'))->rows(3),
                                ]),
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\FileUpload::make('store_logo')->label(__('Logo'))->image()->directory('site')->disk('public')->visibility('public'),
                                    Forms\Components\FileUpload::make('store_favicon')->label(__('Favicon'))->image()->directory('site')->disk('public')->visibility('public'),
                                    Forms\Components\FileUpload::make('store_default_product_image')->label(__('Default product image'))->image()->directory('site')->disk('public')->visibility('public'),
                                ]),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Brand Colors'))
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\ColorPicker::make('store_primary_color')->label(__('Primary color'))->required(),
                                    Forms\Components\ColorPicker::make('store_primary_hover_color')->label(__('Primary hover color'))->required(),
                                    Forms\Components\ColorPicker::make('store_accent_color')->label(__('Accent color'))->required(),
                                    Forms\Components\ColorPicker::make('store_topbar_color')->label(__('Topbar color'))->required(),
                                    Forms\Components\ColorPicker::make('store_header_bg_color')->label(__('Header background'))->required(),
                                    Forms\Components\ColorPicker::make('store_nav_bg_color')->label(__('Navbar background'))->required(),
                                    Forms\Components\ColorPicker::make('store_body_bg_color')->label(__('Page background'))->required(),
                                    Forms\Components\ColorPicker::make('store_surface_color')->label(__('Surface color'))->required(),
                                    Forms\Components\ColorPicker::make('store_surface_tint_color')->label(__('Surface tint color'))->required(),
                                    Forms\Components\ColorPicker::make('store_text_color')->label(__('Text color'))->required(),
                                    Forms\Components\ColorPicker::make('store_muted_text_color')->label(__('Muted text color'))->required(),
                                    Forms\Components\ColorPicker::make('store_border_color')->label(__('Border color'))->required(),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('store_hero_overlay_from')->label(__('Hero overlay start'))->helperText('Example: rgba(2,6,23,.96)'),
                                    Forms\Components\TextInput::make('store_hero_overlay_to')->label(__('Hero overlay end'))->helperText('Example: rgba(185,28,28,.52)'),
                                ]),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Contact'))
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('contact_email')->label(__('Email'))->email(),
                                    Forms\Components\TextInput::make('contact_phone')->label(__('Phone')),
                                    Forms\Components\TextInput::make('contact_whatsapp')->label(__('WhatsApp')),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('contact_address_ar')->label(__('Address Arabic')),
                                    Forms\Components\TextInput::make('contact_address_en')->label(__('Address English')),
                                    Forms\Components\TextInput::make('contact_working_hours_ar')->label(__('Working hours Arabic')),
                                    Forms\Components\TextInput::make('contact_working_hours_en')->label(__('Working hours English')),
                                ]),
                                Forms\Components\TextInput::make('contact_map_url')->label(__('Map URL'))->url(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Social links'))
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('social_facebook')->label('Facebook')->url(),
                                    Forms\Components\TextInput::make('social_instagram')->label('Instagram')->url(),
                                    Forms\Components\TextInput::make('social_youtube')->label('YouTube')->url(),
                                    Forms\Components\TextInput::make('social_tiktok')->label('TikTok')->url(),
                                    Forms\Components\TextInput::make('social_x')->label('X')->url(),
                                    Forms\Components\TextInput::make('social_linkedin')->label('LinkedIn')->url(),
                                ]),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply_template')
                ->label(__('Apply visual template'))
                ->icon('heroicon-o-sparkles')
                ->form([
                    Forms\Components\Select::make('template')
                        ->label(__('Template'))
                        ->options($this->templateOptions())
                        ->searchable()
                        ->required(),
                ])
                ->action(fn (array $data) => $this->applyTemplate((string) $data['template'])),
            Action::make('save_template')
                ->label(__('Save current as template'))
                ->icon('heroicon-o-bookmark-square')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Template name'))
                        ->required()
                        ->maxLength(80),
                ])
                ->action(fn (array $data) => $this->saveCurrentTemplate((string) $data['name'])),
            Action::make('save')
                ->label(__('Save changes'))
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(SiteSettingService::class);

        $this->saveLocalized('store.name', 'identity', $data['store_name_ar'] ?? $settings->localized('store.name', null, 'ar'), $data['store_name_en'] ?? $settings->localized('store.name', null, 'en'));
        $this->saveLocalized('store.tagline', 'identity', $data['store_tagline_ar'] ?? $settings->localized('store.tagline', null, 'ar'), $data['store_tagline_en'] ?? $settings->localized('store.tagline', null, 'en'));
        $this->saveLocalized('store.short_description', 'identity', $data['store_short_description_ar'] ?? $settings->localized('store.short_description', null, 'ar'), $data['store_short_description_en'] ?? $settings->localized('store.short_description', null, 'en'));
        $this->saveValue('store.logo', 'identity', array_key_exists('store_logo', $data) ? $this->singleFile($data['store_logo']) : $settings->get('store.logo'));
        $this->saveValue('store.favicon', 'identity', array_key_exists('store_favicon', $data) ? $this->singleFile($data['store_favicon']) : $settings->get('store.favicon'));
        $this->saveValue('store.default_product_image', 'identity', array_key_exists('store_default_product_image', $data) ? $this->singleFile($data['store_default_product_image']) : $settings->get('store.default_product_image'));
        $this->saveValue('store.primary_color', 'identity', $data['store_primary_color'] ?? $settings->get('store.primary_color', '#111111'));
        $this->saveValue('store.primary_hover_color', 'identity', $data['store_primary_hover_color'] ?? $settings->get('store.primary_hover_color', '#2a2a2a'));
        $this->saveValue('store.accent_color', 'identity', $data['store_accent_color'] ?? $settings->get('store.accent_color', '#d99a16'));
        $this->saveValue('store.topbar_color', 'identity', $data['store_topbar_color'] ?? $settings->get('store.topbar_color', '#111111'));
        $this->saveValue('store.header_bg_color', 'identity', $data['store_header_bg_color'] ?? $settings->get('store.header_bg_color', '#ffffff'));
        $this->saveValue('store.nav_bg_color', 'identity', $data['store_nav_bg_color'] ?? $settings->get('store.nav_bg_color', '#ffffff'));
        $this->saveValue('store.body_bg_color', 'identity', $data['store_body_bg_color'] ?? $settings->get('store.body_bg_color', '#fafafa'));
        $this->saveValue('store.surface_color', 'identity', $data['store_surface_color'] ?? $settings->get('store.surface_color', '#ffffff'));
        $this->saveValue('store.surface_tint_color', 'identity', $data['store_surface_tint_color'] ?? $settings->get('store.surface_tint_color', '#f5f6f8'));
        $this->saveValue('store.text_color', 'identity', $data['store_text_color'] ?? $settings->get('store.text_color', '#111111'));
        $this->saveValue('store.muted_text_color', 'identity', $data['store_muted_text_color'] ?? $settings->get('store.muted_text_color', '#6b7280'));
        $this->saveValue('store.border_color', 'identity', $data['store_border_color'] ?? $settings->get('store.border_color', '#e5e7eb'));
        $this->saveValue('store.hero_overlay_from', 'identity', $data['store_hero_overlay_from'] ?? $settings->get('store.hero_overlay_from', 'rgba(255,255,255,.06)'));
        $this->saveValue('store.hero_overlay_to', 'identity', $data['store_hero_overlay_to'] ?? $settings->get('store.hero_overlay_to', 'rgba(255,255,255,.42)'));

        $this->saveValue('contact.email', 'contact', $data['contact_email'] ?? $settings->get('contact.email'));
        $this->saveValue('contact.phone', 'contact', $data['contact_phone'] ?? $settings->get('contact.phone'));
        $this->saveValue('contact.whatsapp', 'contact', $data['contact_whatsapp'] ?? $settings->get('contact.whatsapp'));
        $this->saveLocalized('contact.address', 'contact', $data['contact_address_ar'] ?? $settings->localized('contact.address', null, 'ar'), $data['contact_address_en'] ?? $settings->localized('contact.address', null, 'en'));
        $this->saveLocalized('contact.working_hours', 'contact', $data['contact_working_hours_ar'] ?? $settings->localized('contact.working_hours', null, 'ar'), $data['contact_working_hours_en'] ?? $settings->localized('contact.working_hours', null, 'en'));
        $this->saveValue('contact.map_url', 'contact', $data['contact_map_url'] ?? $settings->get('contact.map_url'));

        foreach (['facebook', 'instagram', 'youtube', 'tiktok', 'x', 'linkedin'] as $network) {
            $this->saveValue('social.'.$network, 'social', $data['social_'.$network] ?? $settings->get('social.'.$network));
        }

        SiteSettingService::forgetCache();

        Notification::make()
            ->title(__('Site settings saved successfully.'))
            ->success()
            ->send();
    }

    public function applyTemplate(string $templateKey): void
    {
        $template = $this->allTemplates()[$templateKey] ?? null;

        if (! $template) {
            Notification::make()
                ->title(__('Template not found.'))
                ->danger()
                ->send();

            return;
        }

        $state = $this->form->getState();
        $this->form->fill(array_merge($state, $template['colors']));
        $this->save();

        Notification::make()
            ->title(__('Visual template applied successfully.'))
            ->success()
            ->send();
    }

    public function saveCurrentTemplate(string $name): void
    {
        $name = trim($name);
        $slug = Str::slug($name) ?: 'template-'.now()->format('YmdHis');
        $templates = $this->customTemplates();

        $templates[$slug] = [
            'name' => $name,
            'colors' => $this->currentColorState(),
            'created_at' => now()->toISOString(),
        ];

        Setting::updateOrCreate(
            ['key' => 'appearance.presets'],
            ['group' => 'appearance', 'value' => $templates, 'type' => 'json', 'is_public' => false],
        );

        Notification::make()
            ->title(__('Visual template saved successfully.'))
            ->success()
            ->send();
    }

    private function templateOptions(): array
    {
        return collect($this->allTemplates())
            ->mapWithKeys(fn (array $template, string $key): array => [$key => $template['name']])
            ->all();
    }

    /**
     * @return array<string, array{name: string, colors: array<string, string>}>
     */
    private function allTemplates(): array
    {
        return array_merge($this->defaultTemplates(), $this->customTemplates());
    }

    /**
     * @return array<string, array{name: string, colors: array<string, string>}>
     */
    private function defaultTemplates(): array
    {
        return [
            'black-gold' => [
                'name' => __('Black & Gold Store'),
                'colors' => [
                    'store_primary_color' => '#111111',
                    'store_primary_hover_color' => '#2a2a2a',
                    'store_accent_color' => '#d99a16',
                    'store_topbar_color' => '#111111',
                    'store_header_bg_color' => '#ffffff',
                    'store_nav_bg_color' => '#ffffff',
                    'store_body_bg_color' => '#fafafa',
                    'store_surface_color' => '#ffffff',
                    'store_surface_tint_color' => '#f5f6f8',
                    'store_text_color' => '#111111',
                    'store_muted_text_color' => '#6b7280',
                    'store_border_color' => '#e5e7eb',
                    'store_hero_overlay_from' => 'rgba(255,255,255,.06)',
                    'store_hero_overlay_to' => 'rgba(255,255,255,.42)',
                ],
            ],
            'classic-red' => [
                'name' => __('Classic Red Commerce'),
                'colors' => [
                    'store_primary_color' => '#b91c1c',
                    'store_primary_hover_color' => '#991b1b',
                    'store_accent_color' => '#f59e0b',
                    'store_topbar_color' => '#020617',
                    'store_header_bg_color' => '#ffffff',
                    'store_nav_bg_color' => '#ffffff',
                    'store_body_bg_color' => '#f8fafc',
                    'store_surface_color' => '#ffffff',
                    'store_surface_tint_color' => '#fff5f5',
                    'store_text_color' => '#0f172a',
                    'store_muted_text_color' => '#64748b',
                    'store_border_color' => '#e2e8f0',
                    'store_hero_overlay_from' => 'rgba(2,6,23,.96)',
                    'store_hero_overlay_to' => 'rgba(185,28,28,.52)',
                ],
            ],
            'calm-blue' => [
                'name' => __('Calm Blue Retail'),
                'colors' => [
                    'store_primary_color' => '#1d4ed8',
                    'store_primary_hover_color' => '#1e40af',
                    'store_accent_color' => '#14b8a6',
                    'store_topbar_color' => '#0f172a',
                    'store_header_bg_color' => '#ffffff',
                    'store_nav_bg_color' => '#ffffff',
                    'store_body_bg_color' => '#f8fafc',
                    'store_surface_color' => '#ffffff',
                    'store_surface_tint_color' => '#eff6ff',
                    'store_text_color' => '#0f172a',
                    'store_muted_text_color' => '#64748b',
                    'store_border_color' => '#dbeafe',
                    'store_hero_overlay_from' => 'rgba(15,23,42,.88)',
                    'store_hero_overlay_to' => 'rgba(29,78,216,.42)',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{name: string, colors: array<string, string>}>
     */
    private function customTemplates(): array
    {
        $value = Setting::where('key', 'appearance.presets')->first()?->value;

        return is_array($value) ? $value : [];
    }

    /**
     * @return array<string, string>
     */
    private function currentColorState(): array
    {
        $state = $this->form->getState();

        return collect($this->colorFields())
            ->mapWithKeys(fn (string $field): array => [$field => (string) ($state[$field] ?? '')])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function colorFields(): array
    {
        return [
            'store_primary_color',
            'store_primary_hover_color',
            'store_accent_color',
            'store_topbar_color',
            'store_header_bg_color',
            'store_nav_bg_color',
            'store_body_bg_color',
            'store_surface_color',
            'store_surface_tint_color',
            'store_text_color',
            'store_muted_text_color',
            'store_border_color',
            'store_hero_overlay_from',
            'store_hero_overlay_to',
        ];
    }

    private function saveLocalized(string $key, string $group, ?string $ar, ?string $en): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => ['ar' => $ar, 'en' => $en], 'type' => 'json', 'is_public' => true],
        );
    }

    private function saveValue(string $key, string $group, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => ['value' => $value], 'type' => 'string', 'is_public' => true],
        );
    }

    private function fileState(mixed $path): mixed
    {
        return $path ?: null;
    }

    private function singleFile(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_values($value)[0] ?? null;
        }

        return $value;
    }
}
