<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'default_unit', 'description', 'season_note', 'primary_markets',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(SupplyListing::class);
    }

    public function demands(): HasMany
    {
        return $this->hasMany(DemandRequest::class);
    }
}
