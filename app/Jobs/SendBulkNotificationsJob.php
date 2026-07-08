<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("SendBulkNotificationsJob started for notification ID: {$this->notification->id}");

        User::where('email', '!=', 'admin@mazad.com')->chunk(100, function ($users) {
            foreach ($users as $user) {
                try {
                    Notification::create([
                        'type' => $this->notification->type,
                        'title' => $this->notification->title,
                        'content' => $this->notification->content,
                        'user_id' => $user->id,
                        'status' => 'sent',
                    ]);
                } catch (\Exception $e) {
                    Log::error("SendBulkNotificationsJob failed to create notification for User ID {$user->id}: " . $e->getMessage());
                }
            }
        });

        Log::info("SendBulkNotificationsJob finished for notification ID: {$this->notification->id}");
    }
}
