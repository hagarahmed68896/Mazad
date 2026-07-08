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

    protected $appends = ['is_favorited', 'is_look_later'];

    public function getIsFavoritedAttribute()
    {
        $user = auth('sanctum')->user() ?: (auth()->user() ?: \Illuminate\Support\Facades\Auth::user());
        if ($user) {
            return $this->favorites()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    public function getIsLookLaterAttribute()
    {
        $user = auth('sanctum')->user() ?: (auth()->user() ?: \Illuminate\Support\Facades\Auth::user());
        if ($user) {
            return $this->lookLaters()->where('user_id', $user->id)->exists();
        }
        return false;
    }

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

    public function lookLaters(): HasMany
    {
        return $this->hasMany(LookLater::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AdQuestion::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AdAttachment::class);
    }

    protected static $userOrderedAdIds = null;

    public function isRealEstate(): bool
    {
        if (!$this->category_id) {
            return false;
        }

        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }
        
        $cat = $this->category;
        while ($cat) {
            $name = mb_strtolower($cat->name);
            if (
                str_contains($name, 'عقار') || 
                str_contains($name, 'العقارات') || 
                str_contains($name, 'عقارات') || 
                str_contains($name, 'real estate') || 
                str_contains($name, 'realestate') ||
                str_contains($name, 'property') ||
                str_contains($name, 'properties')
            ) {
                return true;
            }
            $cat = $cat->parent_id ? ($cat->parent ?: $cat->load('parent')->parent) : null;
        }
        return false;
    }

    public function hasUserOrderedAd($userId): bool
    {
        if (self::$userOrderedAdIds === null) {
            self::$userOrderedAdIds = \Illuminate\Support\Facades\DB::table('orders')
                ->where('buyer_id', $userId)
                ->pluck('ad_id')
                ->toArray();
        }
        return in_array($this->id, self::$userOrderedAdIds);
    }

    private function obfuscateAddress($address): string
    {
        if (empty($address)) {
            return '';
        }
        
        $segments = preg_split('/[,-\x{060C}]/u', $address);
        if (count($segments) >= 2) {
            return trim($segments[0]) . '، ' . trim($segments[1]);
        }
        
        if (preg_match('/^(.*?\bحي\s+\S+)/ui', $address, $matches)) {
            return trim($matches[1]);
        }
        
        $words = explode(' ', $address);
        return implode(' ', array_slice($words, 0, 3));
    }

    private function getFuzzedLat($lat): float
    {
        $hash = md5($this->id);
        $latSign = (hexdec(substr($hash, 0, 1)) % 2 === 0) ? 1 : -1;
        $latOffset = $latSign * (0.0015 + (hexdec(substr($hash, 2, 4)) / 0xFFFF) * 0.0025);
        return (float)($lat + $latOffset);
    }

    private function getFuzzedLng($lng): float
    {
        $hash = md5($this->id);
        $lngSign = (hexdec(substr($hash, 1, 1)) % 2 === 0) ? 1 : -1;
        $lngOffset = $lngSign * (0.0015 + (hexdec(substr($hash, 6, 4)) / 0xFFFF) * 0.0025);
        return (float)($lng + $lngOffset);
    }

    public function getAddressAttribute($value)
    {
        if ($this->isRealEstate()) {
            return $value;
        }
        
        $user = auth('sanctum')->user() ?: (auth()->user() ?: \Illuminate\Support\Facades\Auth::user());
        if ($user) {
            if ($user->account_type === 'admin' || $user->id === $this->user_id) {
                return $value;
            }
            if ($this->hasUserOrderedAd($user->id)) {
                return $value;
            }
        }
        
        return $this->obfuscateAddress($value);
    }

    public function getLatAttribute($value)
    {
        if (!$value || $this->isRealEstate()) {
            return $value;
        }
        
        $user = auth('sanctum')->user() ?: (auth()->user() ?: \Illuminate\Support\Facades\Auth::user());
        if ($user) {
            if ($user->account_type === 'admin' || $user->id === $this->user_id) {
                return $value;
            }
            if ($this->hasUserOrderedAd($user->id)) {
                return $value;
            }
        }
        
        return $this->getFuzzedLat($value);
    }

    public function getLngAttribute($value)
    {
        if (!$value || $this->isRealEstate()) {
            return $value;
        }
        
        $user = auth('sanctum')->user() ?: (auth()->user() ?: \Illuminate\Support\Facades\Auth::user());
        if ($user) {
            if ($user->account_type === 'admin' || $user->id === $this->user_id) {
                return $value;
            }
            if ($this->hasUserOrderedAd($user->id)) {
                return $value;
            }
        }
        
        return $this->getFuzzedLng($value);
    }
}
