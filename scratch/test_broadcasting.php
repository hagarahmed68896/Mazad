<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

// Find a user and a conversation to test channel conversation.4
$user = User::first();
$conversation = Conversation::find(7);
if ($user && $conversation) {
    echo "Current User ID: {$user->id}\n";
    echo "Conversation 7: user_one_id = {$conversation->user_one_id}, user_two_id = {$conversation->user_two_id}\n";
} else {
    echo "User or Conversation 7 not found.\n";
    exit;
}

// Authenticate
Auth::login($user);

// Create request matching the Flutter log request
$request = Request::create('/api/broadcasting/auth', 'POST', [
    'socket_id' => '893881.6922036',
    'channel_name' => 'private-conversation.7'
]);

// Set headers and user resolver
$request->headers->set('Accept', 'application/json');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->setUserResolver(function() use ($user) {
    return $user;
});

config(['broadcasting.default' => 'log']);
echo "Testing Local Broadcast Auth with log driver...\n";
try {
    $response = Broadcast::auth($request);
    echo "Response status: 200 (Success)\n";
    echo "Response content:\n";
    var_dump($response);
} catch (\Throwable $e) {
    echo "Exception Class: " . get_class($e) . "\n";
    echo "Exception Message: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
