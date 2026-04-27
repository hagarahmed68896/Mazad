<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Notification;

$user = User::where('phone', '512345678')->first();
if ($user) {
    Notification::create([
        'user_id' => $user->id,
        'type' => 'notification',
        'title' => 'اعلان جديد',
        'content' => 'تمت إضافة اعلان جديد يناسب متطلباتك.',
        'status' => 'sent',
        'read_at' => null,
        'created_at' => now()->subHour()
    ]);
    Notification::create([
        'user_id' => $user->id,
        'type' => 'notification',
        'title' => 'اعلان جديد',
        'content' => 'تمت إضافة اعلان جديد يناسب متطلباتك.',
        'status' => 'sent',
        'read_at' => now(),
        'created_at' => now()->subHours(2)
    ]);
    echo "Real notifications created successfully for {$user->name}.\n";
} else {
    echo "User not found.\n";
}
