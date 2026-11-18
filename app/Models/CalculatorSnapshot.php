<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorSnapshot extends Model
{
    protected $fillable = [
        'user_id', 'trade_id', 'commodity_id', 'quantity', 'price_per_kg', 'grade_factor',
        'logistics_cost', 'insurance_premium', 'fees', 'commission', 'trade_value',
        'landed_cost', 'net_proceeds', 'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
            'grade_factor' => 'decimal:4',
            'trade_value' => 'decimal:2',
            'landed_cost' => 'decimal:2',
            'net_proceeds' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}
