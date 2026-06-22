<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo 'Total users: ' . User::count() . PHP_EOL;
echo 'Users with FCM tokens: ' . User::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->count() . PHP_EOL;

foreach (User::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->get() as $u) {
    echo 'User ID: ' . $u->id . ' - Name: ' . $u->name . ' - Token Length: ' . strlen($u->fcm_token) . ' - Token: ' . substr($u->fcm_token, 0, 15) . '...' . PHP_EOL;
}
