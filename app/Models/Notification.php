<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'content', 'user_id', 'status', 'scheduled_at', 'read_at'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public $custom_payload = [];

    protected static function booted()
    {
        static::created(function ($notification) {
            \Log::info("Notification static::created triggered for ID: {$notification->id}, status: {$notification->status}, user_id: " . ($notification->user_id ?? 'null'));
            if ($notification->status === 'sent') {
                $notification->sendFcm($notification->custom_payload ?? []);
            }
        });

        static::updated(function ($notification) {
            \Log::info("Notification static::updated triggered for ID: {$notification->id}, status: {$notification->status}, wasChanged status: " . ($notification->wasChanged('status') ? 'true' : 'false'));
            if ($notification->wasChanged('status') && $notification->status === 'sent') {
                $notification->sendFcm($notification->custom_payload ?? []);
            }
        });
    }

    public function sendFcm($extraData = [])
    {
        $user = $this->user;
        if (!$user) {
            \Log::warning("FCM Send skipped: User relation not found for Notification ID: {$this->id}");
            return;
        }
        if (!$user->fcm_token) {
            \Log::warning("FCM Send skipped: User ID {$user->id} has no FCM token. Notification ID: {$this->id}");
            return;
        }
        
        \Log::info("FCM Send: User ID {$user->id} has FCM token. Status of 'all_notifications': " . ($user->all_notifications ? 'true' : 'false') . ", notification type: {$this->type}");
        
        if ($user->all_notifications || $this->type === 'alert') {
            try {
                $routingType = isset($extraData['click_action']) ? $extraData['click_action'] : $this->type;
                
                $customData = array_merge([
                    'notification_id' => (string) $this->id,
                    'type' => (string) $routingType,
                ], $extraData);
                
                unset($customData['click_action']);
                
                if ($this->type === 'bid_outbid' || $routingType === 'bid_outbid') {
                    $customData['actions'] = json_encode([
                        ['action' => 'bid', 'label' => 'إعطاء عرض', 'color' => 'blue']
                    ], JSON_UNESCAPED_UNICODE);
                } elseif (str_contains($this->type, 'offer') || str_contains($routingType, 'offer')) {
                    $customData['actions'] = json_encode([
                        ['action' => 'accept', 'label' => 'قبول العرض', 'color' => 'blue'],
                        ['action' => 'reject', 'label' => 'رفض العرض', 'color' => 'grey']
                    ], JSON_UNESCAPED_UNICODE);
                } elseif ($this->type === 'winning_bid' || $routingType === 'winning_bid') {
                    $customData['actions'] = json_encode([
                        ['action' => 'pay', 'label' => 'إتمام الدفع', 'color' => 'blue']
                    ], JSON_UNESCAPED_UNICODE);
                }

                // Ensure all values are converted to strings for FCM v1
                foreach ($customData as $key => $val) {
                    $customData[$key] = (string) $val;
                }

                \Log::info("Calling FcmService::sendPush for token: " . substr($user->fcm_token, 0, 15) . "...");
                \App\Services\FcmService::sendPush(
                    $user->fcm_token,
                    $this->title,
                    $this->content,
                    $customData
                );
            } catch (\Exception $e) {
                \Log::warning('FCM Send Failed: ' . $e->getMessage());
            }
        } else {
            \Log::warning("FCM Send skipped: User ID {$user->id} has all_notifications disabled and type is not 'alert'.");
        }
    }
}
