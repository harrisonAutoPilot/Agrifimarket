<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $fillable = [
        'commodity_id', 'market_id', 'price_per_kg', 'price_per_bag', 'price_per_tonne',
        'bag_weight_kg', 'recorded_on', 'source', 'notes', 'published_by',
    ];

    protected function casts(): array
    {
        return [
            'recorded_on' => 'date',
            'price_per_kg' => 'decimal:2',
            'price_per_bag' => 'decimal:2',
            'price_per_tonne' => 'decimal:2',
            'bag_weight_kg' => 'decimal:2',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function display(string $unit = 'kg'): float
    {
        return match ($unit) {
            'bag' => (float) $this->price_per_bag,
            'tonne' => (float) $this->price_per_tonne,
            default => (float) $this->price_per_kg,
        };
    }
}
