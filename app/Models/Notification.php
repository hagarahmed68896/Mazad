<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'content', 'user_id', 'status', 'scheduled_at', 'read_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
