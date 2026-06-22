<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Admin Notification System Test ===" . PHP_EOL;

// Ensure we have at least one test user
$testUser = User::first();
if (!$testUser) {
    echo "No users found in database to test with!" . PHP_EOL;
    exit(1);
}

echo "Testing with User ID: {$testUser->id}, Name: {$testUser->name}, FCM Token: " . ($testUser->fcm_token ?: 'NONE') . PHP_EOL;

DB::beginTransaction();

try {
    // 1. Create a notification with status 'unsent' (equivalent to store form with 'unsent')
    echo PHP_EOL . "1. Creating unsent general notification..." . PHP_EOL;
    $initialNotifsCount = Notification::where('user_id', $testUser->id)->count();

    $notification = Notification::create([
        'title' => 'إشعار غير مرسل تجريبي',
        'content' => 'محتوى تجريبي للإشعار غير المرسل',
        'type' => 'notification',
        'status' => 'unsent',
        'user_id' => null,
    ]);

    $afterUnsentCount = Notification::where('user_id', $testUser->id)->count();
    echo "General Notification ID: {$notification->id}, Status: {$notification->status}" . PHP_EOL;
    echo "User notifications count before: {$initialNotifsCount}, after unsent creation: {$afterUnsentCount}" . PHP_EOL;
    if ($afterUnsentCount === $initialNotifsCount) {
        echo "✅ Success: No copies created for users when notification is 'unsent'." . PHP_EOL;
    } else {
        echo "❌ Error: Copies were created for users when status is 'unsent'." . PHP_EOL;
    }

    // 2. Simulate sending the unsent notification (equivalent to send action)
    echo PHP_EOL . "2. Sending the unsent notification (updating status to 'sent')..." . PHP_EOL;
    
    // We simulate what NotificationController@send does:
    if ($notification->status !== 'sent') {
        $notification->update(['status' => 'sent']);
        
        if (is_null($notification->user_id)) {
            // Duplicate to all users (we only simulate for the test chunk or just run the query)
            User::chunk(100, function ($users) use ($notification) {
                foreach ($users as $user) {
                    Notification::create([
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'content' => $notification->content,
                        'user_id' => $user->id,
                        'status' => 'sent',
                    ]);
                }
            });
        }
    }

    $afterSentCount = Notification::where('user_id', $testUser->id)->count();
    echo "User notifications count after update to sent: {$afterSentCount}" . PHP_EOL;
    if ($afterSentCount > $initialNotifsCount) {
        echo "✅ Success: Copies were successfully broadcasted to users when notification was marked as sent." . PHP_EOL;
        
        // Find the copy
        $copy = Notification::where('user_id', $testUser->id)->where('title', 'إشعار غير مرسل تجريبي')->latest()->first();
        if ($copy) {
            echo "Created copy ID: {$copy->id}, User ID: {$copy->user_id}, Status: {$copy->status}" . PHP_EOL;
        }
    } else {
        echo "❌ Error: Copies were not created for users when status was updated to 'sent'." . PHP_EOL;
    }

    // 3. Create a notification with status 'sent' directly (equivalent to store form with 'sent')
    echo PHP_EOL . "3. Creating sent general notification directly..." . PHP_EOL;
    
    $notification2 = Notification::create([
        'title' => 'إشعار مرسل مباشرة تجريبي',
        'content' => 'محتوى تجريبي للإشعار المرسل مباشرة',
        'type' => 'notification',
        'status' => 'sent',
        'user_id' => null,
    ]);
    
    // Simulate NotificationController@store behavior:
    if ($notification2->status === 'sent') {
        User::chunk(100, function ($users) use ($notification2) {
            foreach ($users as $user) {
                Notification::create([
                    'type' => $notification2->type,
                    'title' => $notification2->title,
                    'content' => $notification2->content,
                    'user_id' => $user->id,
                    'status' => 'sent',
                ]);
            }
        });
    }

    $afterDirectSentCount = Notification::where('user_id', $testUser->id)->count();
    echo "User notifications count after direct sent: {$afterDirectSentCount}" . PHP_EOL;
    if ($afterDirectSentCount > $afterSentCount) {
        echo "✅ Success: Copies were successfully broadcasted to users when notification was created directly as 'sent'." . PHP_EOL;
    } else {
        echo "❌ Error: Copies were not created for users when notification was created directly as 'sent'." . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
} finally {
    DB::rollBack();
    echo PHP_EOL . "DB changes rolled back successfully." . PHP_EOL;
}
