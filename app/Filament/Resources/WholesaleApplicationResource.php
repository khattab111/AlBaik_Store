<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WholesaleApplicationResource\Pages;
use App\Models\User;
use App\Models\WholesaleApplication;
use App\Notifications\WholesaleAccountApprovedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

class WholesaleApplicationResource extends Resource
{
    protected static ?string $model = WholesaleApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Customers';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Application Details'))->schema([
                Forms\Components\TextInput::make('full_name')->label(__('Full Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label(__('Email'))->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label(__('Phone'))->required()->maxLength(50),
                Forms\Components\TextInput::make('whatsapp')->label(__('WhatsApp'))->maxLength(50),
                Forms\Components\TextInput::make('business_name')->label(__('Business Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('business_type')->label(__('Business Type'))->required()->maxLength(255),
                Forms\Components\TextInput::make('city')->label(__('City'))->required()->maxLength(255),
                Forms\Components\Textarea::make('address')->label(__('Address / Location'))->required()->rows(3),
                Forms\Components\Textarea::make('notes')->label(__('Additional Notes'))->rows(3),
            ])->columns(2),
            Forms\Components\Section::make(__('Review'))->schema([
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(static::statusOptions())
                    ->required()
                    ->default(WholesaleApplication::STATUS_PENDING),
                Forms\Components\Textarea::make('admin_notes')->label(__('Admin Notes'))->rows(4),
                Forms\Components\Select::make('user_id')->label(__('Linked User'))->relationship('user', 'email')->searchable()->preload(),
                Forms\Components\Select::make('reviewed_by')->label(__('Reviewed By'))->relationship('reviewer', 'email')->disabled()->dehydrated(false),
                Forms\Components\DateTimePicker::make('reviewed_at')->label(__('Reviewed At'))->disabled()->dehydrated(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label(__('Full Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('business_name')->label(__('Business Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->toggleable(),
                Tables\Columns\TextColumn::make('city')->label(__('City'))->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name')->label(__('Reviewed By'))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label(__('Created At'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WholesaleApplication $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->action(fn (WholesaleApplication $record) => static::approve($record)),
                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WholesaleApplication $record): bool => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')->label(__('Rejection Reason'))->required()->rows(4),
                    ])
                    ->action(fn (WholesaleApplication $record, array $data) => static::reject($record, $data['admin_notes'])),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWholesaleApplications::route('/'),
            'create' => Pages\CreateWholesaleApplication::route('/create'),
            'view' => Pages\ViewWholesaleApplication::route('/{record}'),
            'edit' => Pages\EditWholesaleApplication::route('/{record}/edit'),
        ];
    }

    private static function statusOptions(): array
    {
        return [
            WholesaleApplication::STATUS_PENDING => __('Pending'),
            WholesaleApplication::STATUS_APPROVED => __('Approved'),
            WholesaleApplication::STATUS_REJECTED => __('Rejected'),
        ];
    }

    private static function approve(WholesaleApplication $application): void
    {
        $user = User::where('email', $application->email)->first();

        if ($user && (int) $application->user_id !== $user->id) {
            Notification::make()
                ->title(__('Existing user requires confirmation'))
                ->body(__('A user already exists with this email. Link the application to that user first, then approve it.'))
                ->warning()
                ->send();

            return;
        }

        if (! $user) {
            $user = User::create([
                'email' => $application->email,
                'name' => $application->full_name,
                'mobile' => $application->phone,
                'password' => Hash::make(Str::random(48)),
                'type' => 'wholesale_customer',
                'status' => true,
                'email_verified_at' => now(),
            ]);
        }

        $user->forceFill([
            'type' => 'wholesale_customer',
            'status' => true,
        ])->save();

        $role = Role::firstOrCreate(['name' => 'Wholesale Customer']);
        $user->assignRole($role);

        $application->update([
            'user_id' => $user->id,
            'status' => WholesaleApplication::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        try {
            $token = Password::broker()->createToken($user);
            $user->notify(new WholesaleAccountApprovedNotification($token));
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('Application approved, but email was not sent'))
                ->body(__('Please check mail configuration and resend a password reset link manually.'))
                ->warning()
                ->send();
        }

        Notification::make()
            ->title(__('Application approved'))
            ->body(__('Wholesale customer account is ready.'))
            ->success()
            ->send();
    }

    private static function reject(WholesaleApplication $application, string $notes): void
    {
        $application->update([
            'status' => WholesaleApplication::STATUS_REJECTED,
            'admin_notes' => $notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Notification::make()
            ->title(__('Application rejected'))
            ->danger()
            ->send();
    }
}
