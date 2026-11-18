<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VaultFile extends Model
{
    protected $fillable = ['fileable_type', 'fileable_id', 'type', 'original_name', 'path'];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
