<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'type',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            if (Schema::hasTable('wallets')) {
                Wallet::firstOrCreate(['user_id' => $user->id]);
            }
        });
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function legacyAddresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function walletDepositRequests(): HasMany
    {
        return $this->hasMany(WalletDepositRequest::class);
    }

    public function wholesaleApplications(): HasMany
    {
        return $this->hasMany(WholesaleApplication::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function electronicServiceOrders(): HasMany
    {
        return $this->hasMany(ElectronicServiceOrder::class);
    }

    public function reviewedWholesaleApplications(): HasMany
    {
        return $this->hasMany(WholesaleApplication::class, 'reviewed_by');
    }

    public function isWholesaleCustomer(): bool
    {
        return $this->type === 'wholesale_customer'
            || $this->hasAnyRole(['Wholesale Customer', 'wholesale_customer']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->status
            && $this->hasAnyRole(['Super Admin', 'Admin', 'Manager']);
    }
}
