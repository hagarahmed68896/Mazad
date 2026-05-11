<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'user_one_id', 'user_two_id', 'ad_id', 'last_message_at',
        'user_one_pinned', 'user_two_pinned', 'user_one_muted', 'user_two_muted'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'user_one_pinned' => 'boolean',
        'user_two_pinned' => 'boolean',
        'user_one_muted' => 'boolean',
        'user_two_muted' => 'boolean',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function otherUser()
    {
        return auth()->id() == $this->user_one_id ? $this->userTwo : $this->userOne;
    }
}
