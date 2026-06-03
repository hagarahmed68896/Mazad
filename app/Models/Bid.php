<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = ['ad_id', 'user_id', 'amount', 'max_limit', 'is_auto', 'status', 'validity_days', 'type', 'min_limit', 'notify_near_limit'];

    protected $casts = [
        'amount' => 'decimal:2',
        'max_limit' => 'decimal:2',
        'is_auto' => 'boolean',
        'notify_near_limit' => 'boolean',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
