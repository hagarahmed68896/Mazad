<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    protected $fillable = ['ticket_id', 'action', 'note'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
