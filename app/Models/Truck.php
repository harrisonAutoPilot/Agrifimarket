<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Truck extends Model
{
    protected $fillable = [
        'user_id', 'registration_number', 'driver_name', 'driver_phone',
        'capacity_tonnes', 'route_zone', 'status', 'availability',
    ];

    protected function casts(): array
    {
        return ['capacity_tonnes' => 'decimal:2'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
