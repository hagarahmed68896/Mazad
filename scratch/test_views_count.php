<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Ad;
use App\Http\Controllers\Api\AdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Find an ad in the database
$ad = Ad::first();
if (!$ad) {
    echo "No ads found in database. Cannot run verification.\n";
    exit;
}

$owner = User::find($ad->user_id);
// Find a non-owner user
$nonOwner = User::where('id', '!=', $ad->user_id)->first();

if (!$owner || !$nonOwner) {
    echo "Requires at least two users in the database to verify owner vs non-owner behavior.\n";
    exit;
}

echo "Testing Ad ID: {$ad->id} (Owned by User ID: {$owner->id})\n";

// --- Case 1: Owner views the ad ---
echo "\n--- Case 1: Owner views the ad ---\n";
Auth::login($owner);
$initialViews = $ad->views_count;
echo "Initial views: {$initialViews}\n";

$request = Request::create("/api/ads/{$ad->id}", 'GET');
$request->setUserResolver(fn() => $owner);

// Force request on request context
app()->instance('request', $request);
echo "Resolved Sanctum user: " . (auth('sanctum')->user() ? auth('sanctum')->user()->id : 'null') . "\n";

$controller = new AdController();
$response = $controller->show($request, $ad->id);
echo "Response content (truncated): " . substr($response->getContent(), 0, 150) . "\n";

$ad->refresh();
$newViews = $ad->views_count;
echo "Views after owner viewed: {$newViews}\n";
if ($newViews === $initialViews) {
    echo "SUCCESS: Views count did NOT increase when owner viewed the ad!\n";
} else {
    echo "FAIL: Views count increased when owner viewed the ad!\n";
}

// --- Case 2: Non-owner views the ad ---
echo "\n--- Case 2: Non-owner views the ad ---\n";
Auth::login($nonOwner);
$initialViews2 = $ad->views_count;
echo "Initial views: {$initialViews2}\n";

$request2 = Request::create("/api/ads/{$ad->id}", 'GET');
$request2->setUserResolver(fn() => $nonOwner);

// Force request on request context
app()->instance('request', $request2);
auth('sanctum')->forgetUser();
echo "Resolved Sanctum user: " . (auth('sanctum')->user() ? auth('sanctum')->user()->id : 'null') . "\n";

$response2 = $controller->show($request2, $ad->id);
echo "Response content (truncated): " . substr($response2->getContent(), 0, 150) . "\n";

$ad->refresh();
$newViews2 = $ad->views_count;
echo "Views after non-owner viewed: {$newViews2}\n";
if ($newViews2 === $initialViews2 + 1) {
    echo "SUCCESS: Views count increased by 1 when non-owner viewed the ad!\n";
} else {
    echo "FAIL: Views count did not increase correctly!\n";
}
