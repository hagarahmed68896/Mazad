<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdImage extends Model
{
    use HasFactory;

    protected $fillable = ['ad_id', 'image'];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}
