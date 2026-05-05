<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['ad_id', 'user_id', 'question', 'answer', 'answered_at'];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
