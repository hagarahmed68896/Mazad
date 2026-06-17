<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'content', 'user_id', 'status', 'scheduled_at', 'read_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    protected static function booted()
    {
        static::created(function ($notification) {
            // Load user relation if not already loaded
            $user = $notification->user;
            if ($user && $user->fcm_token) {
                // Check if user has enabled notifications OR if it is a critical alert (security warning)
                if ($user->all_notifications || $notification->type === 'alert') {
                    try {
                        // Prepare custom data depending on notification type to enable interactive buttons in the mobile UI
                        $customData = [
                            'notification_id' => (string) $notification->id,
                            'type' => (string) $notification->type,
                        ];
                        
                        // We can add actions / extra fields to map exactly what buttons the UI should render
                        // e.g. for "bid_outbid" -> Show "إعطاء عرض"
                        // for "offer" -> Show "قبول العرض"
                        // for "winning_bid" -> Show "إتمام الدفع"
                        if ($notification->type === 'bid_outbid') {
                            $customData['actions'] = json_encode([
                                ['action' => 'bid', 'label' => 'إعطاء عرض', 'color' => 'blue']
                            ], JSON_UNESCAPED_UNICODE);
                        } elseif (str_contains($notification->type, 'offer')) {
                            $customData['actions'] = json_encode([
                                ['action' => 'accept', 'label' => 'قبول العرض', 'color' => 'blue'],
                                ['action' => 'reject', 'label' => 'رفض العرض', 'color' => 'grey']
                            ], JSON_UNESCAPED_UNICODE);
                        } elseif ($notification->type === 'winning_bid') {
                            $customData['actions'] = json_encode([
                                ['action' => 'pay', 'label' => 'إتمام الدفع', 'color' => 'blue']
                            ], JSON_UNESCAPED_UNICODE);
                        }

                        \App\Services\FcmService::sendPush(
                            $user->fcm_token,
                            $notification->title,
                            $notification->content,
                            $customData
                        );
                    } catch (\Exception $e) {
                        \Log::warning('FCM Auto-Send Failed: ' . $e->getMessage());
                    }
                }
            }
        });
    }
}
