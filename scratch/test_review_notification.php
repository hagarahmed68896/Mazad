<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Review;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

echo "=== Review Notification Event Test ===" . PHP_EOL;

// Ensure we have at least two users
$reviewer = User::first();
$ratedUser = User::skip(1)->first();

if (!$reviewer || !$ratedUser) {
    echo "Requires at least 2 users in database to test review notification." . PHP_EOL;
    exit(1);
}

echo "Reviewer: ID {$reviewer->id}, Name: {$reviewer->name}" . PHP_EOL;
echo "Rated User: ID {$ratedUser->id}, Name: {$ratedUser->name}" . PHP_EOL;

DB::beginTransaction();

try {
    $beforeCount = Notification::where('user_id', $ratedUser->id)->count();

    // Create a mock review
    echo "Creating review..." . PHP_EOL;
    $review = Review::create([
        'reviewer_id' => $reviewer->id,
        'rated_user_id' => $ratedUser->id,
        'rating' => 5,
        'comment' => 'هذا تقييم تجريبي للتحقق من نظام الإشعارات الجديد.',
        'status' => 'positive',
        'is_visible' => true,
    ]);

    $afterCount = Notification::where('user_id', $ratedUser->id)->count();
    
    echo "Notifications count before: {$beforeCount}, after review: {$afterCount}" . PHP_EOL;
    
    if ($afterCount > $beforeCount) {
        $notification = Notification::where('user_id', $ratedUser->id)->latest()->first();
        echo "✅ SUCCESS: Notification was automatically created!" . PHP_EOL;
        echo "Notification Details:" . PHP_EOL;
        echo "  - ID: {$notification->id}" . PHP_EOL;
        echo "  - Title: {$notification->title}" . PHP_EOL;
        echo "  - Content: {$notification->content}" . PHP_EOL;
        echo "  - Status: {$notification->status}" . PHP_EOL;
    } else {
        echo "❌ FAILURE: Notification was not created for the rated user." . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
} finally {
    DB::rollBack();
    echo "Database changes rolled back successfully." . PHP_EOL;
}
