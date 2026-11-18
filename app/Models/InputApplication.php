<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InputApplication extends Model
{
    protected $fillable = [
        'user_id', 'input_bundle_id', 'status', 'contribution_amount', 'financed_amount', 'outstanding', 'note',
    ];

    protected function casts(): array
    {
        return [
            'contribution_amount' => 'decimal:2',
            'financed_amount' => 'decimal:2',
            'outstanding' => 'decimal:2',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(InputBundle::class, 'input_bundle_id');
    }
}
