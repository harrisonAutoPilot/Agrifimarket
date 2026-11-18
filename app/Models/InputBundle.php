<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InputBundle extends Model
{
    protected $fillable = [
        'name', 'season', 'coverage_ha', 'price', 'contribution_percent', 'description', 'items', 'active',
    ];

    protected function casts(): array
    {
        return [
            'coverage_ha' => 'decimal:2',
            'price' => 'decimal:2',
            'contribution_percent' => 'decimal:2',
            'items' => 'array',
            'active' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(InputApplication::class);
    }

    public function contributionAmount(): float
    {
        return round((float) $this->price * ((float) $this->contribution_percent / 100), 2);
    }
}
