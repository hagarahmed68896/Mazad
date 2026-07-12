<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_number', 'ad_id', 'service_order_id', 'user_id', 'total_amount', 
        'payment_method', 'status'
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        // reusing ad_id column for now to simulate relation for partners without making DB changes
        return $this->belongsTo(Service::class, 'ad_id');
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }
}
