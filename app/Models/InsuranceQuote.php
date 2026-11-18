<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceQuote extends Model
{
    protected $fillable = [
        'trade_id', 'user_id', 'insurance_product_id', 'premium', 'cover_note', 'policy_number', 'status',
    ];

    protected function casts(): array
    {
        return ['premium' => 'decimal:2'];
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InsuranceProduct::class, 'insurance_product_id');
    }
}
