<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    protected $fillable = [
        'dealer_listing_id', 'from_user_id', 'name', 'phone', 'email', 'message',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(DealerListing::class, 'dealer_listing_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
