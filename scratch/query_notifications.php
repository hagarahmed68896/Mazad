<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;

echo "--- Recent General Notifications (user_id IS NULL) ---\n";
$notifs = Notification::whereNull('user_id')->latest()->take(10)->get();
foreach ($notifs as $n) {
    echo "ID: {$n->id} | Title: {$n->title} | Content: {$n->content} | Status: {$n->status} | Created: {$n->created_at}\n";
}

echo "\n--- Recent User Notifications (user_id IS NOT NULL) ---\n";
$userNotifs = Notification::whereNotNull('user_id')->latest()->take(10)->get();
foreach ($userNotifs as $un) {
    echo "ID: {$un->id} | User ID: {$un->user_id} | Title: {$un->title} | Content: {$un->content} | Status: {$un->status} | Created: {$un->created_at}\n";
}
