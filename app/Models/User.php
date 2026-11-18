<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'account_type', 'status',
        'business_name', 'rc_number', 'nin', 'bvn', 'state', 'city', 'address',
        'bank_name', 'bank_account_name', 'bank_account_number',
        'guarantor_name', 'guarantor_phone', 'bio', 'rating', 'rejection_reason',
        'approved_at', 'email_verified_at', 'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'bvn',
        'nin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'rating' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'NGN']
            );
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isApproved(): bool
    {
        return $this->isAdmin() || $this->status === 'approved';
    }

    public function deskName(): string
    {
        return $this->business_name ?: $this->name;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(SupplyListing::class);
    }

    public function demands(): HasMany
    {
        return $this->hasMany(DemandRequest::class);
    }

    public function tradesAsBuyer(): HasMany
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    public function tradesAsSupplier(): HasMany
    {
        return $this->hasMany(Trade::class, 'supplier_id');
    }

    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class);
    }

    public function rateCards(): HasMany
    {
        return $this->hasMany(RateCard::class);
    }

    public function insuranceProducts(): HasMany
    {
        return $this->hasMany(InsuranceProduct::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(InputApplication::class);
    }

    public function dealerListings(): HasMany
    {
        return $this->hasMany(DealerListing::class);
    }
}
