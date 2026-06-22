<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::first();
if (!$user) {
    echo "No user found.\n";
    exit;
}

echo "Testing User Model Serialization...\n";
$userJson = json_encode($user);
$userData = json_decode($userJson, true);

if (array_key_exists('unread_messages_count', $userData)) {
    echo "SUCCESS: 'unread_messages_count' exists in serialized user JSON!\n";
    echo "Value: " . $userData['unread_messages_count'] . "\n";
} else {
    echo "FAIL: 'unread_messages_count' is missing from serialized user JSON!\n";
}
