<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchSuggestion extends Model
{
    protected $fillable = [
        'supply_listing_id', 'demand_request_id', 'score', 'reasons', 'status',
    ];

    protected function casts(): array
    {
        return ['reasons' => 'array'];
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(SupplyListing::class, 'supply_listing_id');
    }

    public function demand(): BelongsTo
    {
        return $this->belongsTo(DemandRequest::class, 'demand_request_id');
    }
}
