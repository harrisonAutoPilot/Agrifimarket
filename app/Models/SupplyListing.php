<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SupplyListing extends Model
{
    protected $fillable = [
        'code', 'user_id', 'commodity_id', 'quantity', 'price_per_kg', 'moisture', 'purity',
        'grade', 'state', 'city', 'address', 'status', 'description', 'expires_on',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
            'moisture' => 'decimal:2',
            'purity' => 'decimal:2',
            'expires_on' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(VaultFile::class, 'fileable');
    }

    public function value(): float
    {
        return (float) $this->quantity * 1000 * (float) $this->price_per_kg;
    }
}
