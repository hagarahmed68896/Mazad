<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'city_id', 'address', 'lat', 'lng', 'title', 'image', 'description', 'inspection_info', 'type',
        'deposit_amount', 'price', 'bid_increment', 'bids_count', 'views_count', 'status', 'end_date', 'bid_status', 'is_featured', 'is_enfaz',
        'auction_type', 'shipping_price', 'shipping_type', 'youtube_link', 'start_date', 'award_duration', 'quantity', 'min_sale_price', 
        'allow_bidding', 'allow_negotiation', 'buy_now_price', 'inspection_date', 'inspection_end_date'
    ];

    protected $appends = ['is_favorited', 'is_look_later', 'is_real_estate', 'is_cars'];

    protected static function booted()
    {
        static::retrieved(function ($ad) {
            // Auto finish active ads whose end date is past
            if ($ad->status === 'active' && $ad->end_date && \Carbon\Carbon::parse($ad->end_date)->isPast()) {
                $ad->status = 'finished';
                $ad->save();
            }
            // Auto activate upcoming ads whose start date is past
            elseif ($ad->status === 'upcoming' && $ad->start_date && \Carbon\Carbon::parse($ad->start_date)->isPast()) {
                $ad->status = 'active';
                $ad->save();
            }
        });
    }

    public function getIsRealEstateAttribute()
    {
        return $this->isRealEstate();
    }

    public function getIsCarsAttribute()
    {
        return $this->isCars();
    }

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

    public function isCars(): bool
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
                str_contains($name, 'سيارات') || 
                str_contains($name, 'سيارة') || 
                str_contains($name, 'car') || 
                str_contains($name, 'cars') || 
                str_contains($name, 'vehicle') || 
                str_contains($name, 'vehicles')
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
        
        $cityName = $this->city ? $this->city->name : '';
        
        // Split address by commas or dashes
        $segments = preg_split('/[,-\x{060C}]/u', $address);
        $segments = array_map(function($s) {
            return trim(preg_replace('/^[\s،\-]+|[\s،\-]+$/u', '', $s));
        }, $segments);
        $segments = array_filter($segments);
        
        $neighborhood = '';
        
        if ($cityName) {
            // Find the city index in segments
            $cityIndex = -1;
            foreach ($segments as $idx => $seg) {
                if (mb_strpos($seg, $cityName) !== false) {
                    $cityIndex = $idx;
                    break;
                }
            }
            
            if ($cityIndex > 0) {
                // The neighborhood is typically the segment right before the city
                $neighborhood = $segments[$cityIndex - 1];
            } elseif ($cityIndex === 0 && count($segments) > 1) {
                // If city is the first segment, neighborhood might be the second
                $neighborhood = $segments[1];
            }
        }
        
        // Fallback: If we couldn't find the city in the segments, look for a segment with "حي"
        if (empty($neighborhood)) {
            foreach ($segments as $seg) {
                if (mb_strpos($seg, 'حي') !== false) {
                    $neighborhood = $seg;
                    break;
                }
            }
        }
        
        // Fallback 2: If still empty, take the first segment that is not a road/building keyword
        if (empty($neighborhood)) {
            $detailedKeywords = [
                'شارع', 'طريق', 'مول', 'عمارة', 'فيلا', 'برج', 'شقة', 'مكتب', 
                'رقم', 'الدور', 'دور', 'السعودية', 'saudi', 'فلل', 'شقق', 'مكاتب', 'معرض', 'بناية'
            ];
            foreach ($segments as $seg) {
                $hasKeyword = false;
                foreach ($detailedKeywords as $kw) {
                    if (mb_strpos(mb_strtolower($seg), $kw) !== false) {
                        $hasKeyword = true;
                        break;
                    }
                }
                if (!$hasKeyword && $seg !== $cityName) {
                    $neighborhood = $seg;
                    break;
                }
            }
        }
        
        // Clean up neighborhood (ensure it starts with "حي" if not empty)
        if (!empty($neighborhood)) {
            $neighborhoodClean = trim(preg_replace('/^حي\s+/u', '', $neighborhood));
            $neighborhood = 'حي ' . $neighborhoodClean;
        }
        
        if ($cityName && $neighborhood) {
            return $cityName . '، ' . $neighborhood;
        }
        
        return $cityName ?: ($neighborhood ?: 'السعودية');
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

    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'http')) {
            return $value;
        }

        $host = app()->runningInConsole() ? asset('') : request()->getSchemeAndHttpHost() . request()->getBaseUrl();
        $host = rtrim($host, '/');

        return $host . '/' . ltrim($value, '/');
    }

    public function toArray()
    {
        $array = parent::toArray();
        
        if ($this->relationLoaded('city') && $this->city) {
            $array['district'] = [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ];
            
            $region = $this->city->region;
            if ($region) {
                $array['city'] = [
                    'id' => $region->id,
                    'name' => $region->name,
                ];
            } else {
                $array['city'] = null;
            }
        } else {
            if ($this->city_id) {
                // To prevent infinite loop, we fetch via Query Builder
                $cityObj = DB::table('cities')->find($this->city_id);
                if ($cityObj) {
                    $array['district'] = [
                        'id' => $cityObj->id,
                        'name' => $cityObj->name,
                    ];
                    $regionObj = DB::table('regions')->find($cityObj->region_id);
                    $array['city'] = $regionObj ? [
                        'id' => $regionObj->id,
                        'name' => $regionObj->name,
                    ] : null;
                }
            }
        }
        
        return $array;
    }
}
