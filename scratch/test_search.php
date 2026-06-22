<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Find a user who has conversations
$user = null;
foreach (User::all() as $u) {
    $hasConvs = Conversation::where('user_one_id', $u->id)->orWhere('user_two_id', $u->id)->exists();
    if ($hasConvs) {
        $user = $u;
        break;
    }
}

if (!$user) {
    // If no user with conversations exists, let's just find any user
    $user = User::first();
}

if (!$user) {
    echo "No users found in database. Cannot run verification.\n";
    exit;
}

echo "Testing using User ID: {$user->id} ({$user->name})\n";

// Authenticate user
Auth::login($user);

// Create request with query search term
$request = Request::create('/api/chats', 'GET', ['search' => 'nonexistent_search_query_123456789']);

$controller = new ChatController();
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

echo "Search results for 'nonexistent_search_query_123456789' (Should be empty):\n";
print_r($data['data']);

// Try fetching all
$requestAll = Request::create('/api/chats', 'GET');
$responseAll = $controller->index($requestAll);
$dataAll = json_decode($responseAll->getContent(), true);

echo "\nTotal conversations for user (no search filter):\n";
echo count($dataAll['data']) . " conversations found.\n";
echo "Unread messages count in response: " . $dataAll['unread_messages_count'] . "\n";

if (count($dataAll['data']) > 0) {
    // Try searching for the first conversation's other user's name
    $firstConv = $dataAll['data'][0];
    $otherUserName = $firstConv['other_user']['name'];
    $searchTerm = mb_substr($otherUserName, 0, 3); // first 3 chars
    
    echo "\nSearching for: '{$searchTerm}'\n";
    $requestSearch = Request::create('/api/chats', 'GET', ['search' => $searchTerm]);
    $responseSearch = $controller->index($requestSearch);
    $dataSearch = json_decode($responseSearch->getContent(), true);
    
    echo "Found " . count($dataSearch['data']) . " matching conversations.\n";
    foreach ($dataSearch['data'] as $c) {
        echo "- Conv ID: {$c['id']} | Other User: {$c['other_user']['name']}\n";
    }
}
