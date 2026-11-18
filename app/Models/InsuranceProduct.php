<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceProduct extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'rate_percent', 'description', 'active'];

    protected function casts(): array
    {
        return [
            'rate_percent' => 'decimal:3',
            'active' => 'boolean',
        ];
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
