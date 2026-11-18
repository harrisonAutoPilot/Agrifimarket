<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trade extends Model
{
    protected $fillable = [
        'code', 'supply_listing_id', 'demand_request_id', 'buyer_id', 'supplier_id', 'commodity_id',
        'quantity', 'price_per_kg', 'grade_factor', 'moisture', 'purity', 'grade', 'delivery_mode',
        'delivery_state', 'delivery_city', 'delivery_address', 'logistics_user_id', 'insurer_user_id',
        'logistics_cost', 'insurance_premium', 'commodity_value', 'commission', 'platform_fee',
        'buyer_total', 'supplier_net', 'status', 'previous_status', 'quality_notes', 'agreed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
            'grade_factor' => 'decimal:4',
            'agreed_at' => 'datetime',
            'commodity_value' => 'decimal:2',
            'buyer_total' => 'decimal:2',
            'supplier_net' => 'decimal:2',
            'logistics_cost' => 'decimal:2',
            'insurance_premium' => 'decimal:2',
            'commission' => 'decimal:2',
            'platform_fee' => 'decimal:2',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(SupplyListing::class, 'supply_listing_id');
    }

    public function demand(): BelongsTo
    {
        return $this->belongsTo(DemandRequest::class, 'demand_request_id');
    }

    public function haulier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistics_user_id');
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'insurer_user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TradeEvent::class)->orderBy('id');
    }

    public function haulQuotes(): HasMany
    {
        return $this->hasMany(HaulQuote::class);
    }

    public function insuranceQuotes(): HasMany
    {
        return $this->hasMany(InsuranceQuote::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function openDispute(): HasOne
    {
        return $this->hasOne(Dispute::class)->where('status', 'open');
    }

    public function involves(User $user): bool
    {
        return $user->isAdmin()
            || in_array($user->id, [
                $this->buyer_id,
                $this->supplier_id,
                $this->logistics_user_id,
                $this->insurer_user_id,
            ], true);
    }
}
