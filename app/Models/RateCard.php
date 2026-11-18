<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateCard extends Model
{
    protected $fillable = ['user_id', 'origin_state', 'destination_state', 'rate_per_tonne'];

    protected function casts(): array
    {
        return ['rate_per_tonne' => 'decimal:2'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
