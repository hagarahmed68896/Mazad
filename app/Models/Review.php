<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['reviewer_id', 'rated_user_id', 'order_id', 'ad_id', 'rating', 'comment', 'status', 'reply', 'is_visible'];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function ratedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    protected static function booted()
    {
        static::created(function ($review) {
            // Find the reviewer name
            $reviewerName = $review->reviewer ? $review->reviewer->name : 'مستخدم';
            
            // Create notification for the rated user
            \App\Models\Notification::create([
                'type' => 'notification',
                'title' => 'تقييم جديد',
                'content' => "قام المستخدم {$reviewerName} بتقييمك بـ {$review->rating} نجوم.",
                'user_id' => $review->rated_user_id,
                'status' => 'sent',
            ]);
        });
    }
}
