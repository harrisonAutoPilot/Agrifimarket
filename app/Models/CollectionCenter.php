<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionCenter extends Model
{
    protected $fillable = ['name', 'state', 'city', 'address', 'phone', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
