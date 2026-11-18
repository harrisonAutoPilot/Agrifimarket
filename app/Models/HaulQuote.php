<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HaulQuote extends Model
{
    protected $fillable = [
        'trade_id', 'user_id', 'truck_id', 'price_per_tonne', 'total', 'note', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price_per_tonne' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
