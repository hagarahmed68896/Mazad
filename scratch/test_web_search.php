<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Conversation;
use App\Http\Controllers\ChatController;
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
    $user = User::first();
}

if (!$user) {
    echo "No users found in database. Cannot run verification.\n";
    exit;
}

echo "Testing Web Search using User ID: {$user->id} ({$user->name})\n";

// Authenticate user
Auth::login($user);

// Create request with query search term
$request = Request::create('/chats', 'GET', ['search' => 'nonexistent_search_query_123456789']);

$controller = new ChatController();
$response = $controller->index($request);

// It returns a View object. We can check the data passed to the view
$viewData = $response->getData();
$conversations = $viewData['conversations'];

echo "Search results for 'nonexistent_search_query_123456789' (Should be empty):\n";
echo "Found " . count($conversations) . " conversations.\n";

// Try fetching all
$requestAll = Request::create('/chats', 'GET');
$responseAll = $controller->index($requestAll);
$conversationsAll = $responseAll->getData()['conversations'];

echo "\nTotal conversations for user (no search filter):\n";
echo count($conversationsAll) . " conversations found.\n";

if (count($conversationsAll) > 0) {
    // Try searching for the first conversation's other user's name
    $firstConv = $conversationsAll[0];
    $otherUser = $firstConv->user_one_id == $user->id ? $firstConv->userTwo : $firstConv->userOne;
    $otherUserName = $otherUser->name;
    $searchTerm = mb_substr($otherUserName, 0, 3);
    
    echo "\nSearching for: '{$searchTerm}'\n";
    $requestSearch = Request::create('/chats', 'GET', ['search' => $searchTerm]);
    $responseSearch = $controller->index($requestSearch);
    $conversationsSearch = $responseSearch->getData()['conversations'];
    
    echo "Found " . count($conversationsSearch) . " matching conversations.\n";
    foreach ($conversationsSearch as $c) {
        $ou = $c->user_one_id == $user->id ? $c->userTwo : $c->userOne;
        echo "- Conv ID: {$c->id} | Other User: {$ou->name}\n";
    }
}
