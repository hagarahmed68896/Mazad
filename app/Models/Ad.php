<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'city_id', 'address', 'lat', 'lng', 'title', 'image', 'description', 'inspection_info', 'type',
        'deposit_amount', 'price', 'bid_increment', 'bids_count', 'views_count', 'status', 'end_date', 'bid_status', 'is_featured', 'is_enfaz',
        'auction_type', 'shipping_price', 'shipping_type', 'youtube_link', 'start_date', 'award_duration', 'quantity', 'min_sale_price', 
        'allow_bidding', 'allow_negotiation', 'buy_now_price', 'inspection_date', 'inspection_end_date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function financialSettlements(): HasMany
    {
        return $this->hasMany(FinancialSettlement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function adProperties(): HasMany
    {
        return $this->hasMany(AdProperty::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AdImage::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AdQuestion::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AdAttachment::class);
    }
}
