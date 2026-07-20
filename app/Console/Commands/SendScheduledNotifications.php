<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;

#[Signature('notifications:send-scheduled')]
#[Description('Send scheduled notifications that are due')]
class SendScheduledNotifications extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifications = Notification::whereNull('user_id')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($notifications->isEmpty()) {
            $this->info("No scheduled notifications to send.");
            return;
        }

        $this->info("Found {$notifications->count()} scheduled notifications to send.");

        foreach ($notifications as $notification) {
            $notification->update(['status' => 'sent']);
            
            $this->sendToAllUsers($notification);
            
            $this->info("Sent scheduled notification #{$notification->id}: '{$notification->title}'");
        }

        $this->info("Finished sending scheduled notifications.");
    }

    private function sendToAllUsers(Notification $notification)
    {
        User::where('email', '!=', 'admin@mazad.com')->chunk(100, function ($users) use ($notification) {
            foreach ($users as $user) {
                try {
                    Notification::create([
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'content' => $notification->content,
                        'user_id' => $user->id,
                        'status' => 'sent',
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Failed to send scheduled notification to user {$user->id}: " . $e->getMessage());
                }
            }
        });
    }
}
