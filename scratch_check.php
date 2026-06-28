<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Auth;

// Find a representative or partner that has a parent_id
$member = User::whereNotNull('parent_id')
    ->whereIn('account_type', ['representative', 'partner'])
    ->first();

if (!$member) {
    echo "No representative/partner found with parent_id.\n";
    exit;
}

$company = User::find($member->parent_id);
if (!$company) {
    echo "Parent company not found for user ID {$member->id}.\n";
    exit;
}

echo "Logging in as company ID {$company->id} ({$company->name})...\n";
Auth::login($company);

echo "Calling TeamController@destroy for member ID {$member->id} ({$member->name})...\n";

try {
    $controller = new TeamController();
    $response = $controller->destroy($member->id);
    
    echo "Response received! Status code: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION caught during controller call:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
