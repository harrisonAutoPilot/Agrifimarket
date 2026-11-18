<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandRequest extends Model
{
    protected $fillable = [
        'code', 'user_id', 'commodity_id', 'quantity', 'quantity_filled', 'price_per_kg',
        'moisture_max', 'purity_min', 'grade', 'delivery_type', 'state', 'city', 'address',
        'payment_days', 'status', 'description', 'starts_on', 'ends_on',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'quantity_filled' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
            'moisture_max' => 'decimal:2',
            'purity_min' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function remaining(): float
    {
        return max(0, (float) $this->quantity - (float) $this->quantity_filled);
    }

    public function daysOpen(): ?int
    {
        return $this->ends_on?->diffInDays(now()->startOfDay(), false) !== null
            ? (int) now()->startOfDay()->diffInDays($this->ends_on, false)
            : null;
    }
}
