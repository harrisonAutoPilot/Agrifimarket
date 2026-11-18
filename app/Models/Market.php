<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    protected $fillable = ['name', 'city', 'state', 'code'];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function label(): string
    {
        return $this->name.', '.$this->state;
    }
}
