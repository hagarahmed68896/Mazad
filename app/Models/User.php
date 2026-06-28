<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address', 'account_type', 'parent_id', 'commission_rate',
        'company_name', 'commercial_register', 'ads_count', 'sales',
        'purchases', 'status', 'city_id', 'avatar', 'latitude', 'longitude', 'known_devices',
        'bio', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_iban', 'bank_address', 'bank_swift_code', 'cover',
        'fcm_token', 'all_notifications', 'bid_notifications', 'bid_approaching_limit_notifications',
        'bid_approaching_limit_percentage', 'bid_reached_limit_notifications',
        'auction_update_notifications', 'favorite_auction_notifications', 'winning_bid_notifications',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'unread_messages_count',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'known_devices' => 'array',
            'all_notifications' => 'boolean',
            'bid_notifications' => 'boolean',
            'bid_approaching_limit_notifications' => 'boolean',
            'bid_approaching_limit_percentage' => 'integer',
            'bid_reached_limit_notifications' => 'boolean',
            'auction_update_notifications' => 'boolean',
            'favorite_auction_notifications' => 'boolean',
            'winning_bid_notifications' => 'boolean',
        ];
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'rated_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function interests(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function resolvedInterests()
    {
        if ($this->parent_id) {
            $companyInterests = $this->company ? $this->company->interests : collect();
            $myInterests = $this->interests;
            
            if ($companyInterests->isNotEmpty()) {
                if ($myInterests->isNotEmpty()) {
                    return $myInterests->intersect($companyInterests);
                }
                return $companyInterests;
            }
            
            return $myInterests;
        }
        
        return $this->interests;
    }

    public function getResolvedInterestsAttribute()
    {
        return $this->resolvedInterests();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function lookLaters(): HasMany
    {
        return $this->hasMany(LookLater::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function unreadMessagesCount()
    {
        return Message::whereHas('conversation', function($q) {
            $q->where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
        })
        ->where('sender_id', '!=', $this->id)
        ->where('is_read', false)
        ->count();
    }

    public function getUnreadMessagesCountAttribute()
    {
        return $this->unreadMessagesCount();
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return asset('images/default-avatar.svg');
        }
        if (\Illuminate\Support\Str::startsWith($value, 'http')) {
            return $value;
        }
        return asset($value);
    }

    /**
     * Check if this user belongs to the same team/company as another user.
     *
     * @param int $otherUserId
     * @return bool
     */
    public function isSameTeam($otherUserId): bool
    {
        if ($this->id == $otherUserId) {
            return true;
        }

        $otherUser = self::find($otherUserId);
        if (!$otherUser) {
            return false;
        }

        // If one is the parent of the other (Company and Representative)
        if (($this->parent_id && $this->parent_id == $otherUser->id) || ($otherUser->parent_id && $otherUser->parent_id == $this->id)) {
            return true;
        }

        // If both have the same parent (Representatives of the same company)
        if ($this->parent_id && $this->parent_id == $otherUser->parent_id) {
            return true;
        }

        return false;
    }
}
