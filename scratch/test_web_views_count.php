<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Ad;
use App\Http\Controllers\HomeController;
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

echo "Testing Web Ad views count for Ad ID: {$ad->id} (Owned by User ID: {$owner->id})\n";

// --- Case 1: Guest views the ad (Not logged in) ---
echo "\n--- Case 1: Guest views the ad (Not logged in) ---\n";
Auth::logout();
$initialViews = $ad->views_count;
echo "Initial views: {$initialViews}\n";

$controller = new HomeController();
try {
    $response = $controller->showAd($ad->id);
    $ad->refresh();
    $newViews = $ad->views_count;
    echo "Views after guest viewed: {$newViews}\n";
    if ($newViews === $initialViews + 1) {
        echo "SUCCESS: Views count increased by 1 for guest!\n";
    } else {
        echo "FAIL: Views count did not increase correctly for guest (Expected " . ($initialViews + 1) . ", got {$newViews})!\n";
    }
} catch (\Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
}

// --- Case 2: Owner views the ad ---
echo "\n--- Case 2: Owner views the ad ---\n";
Auth::login($owner);
$ad->refresh();
$initialViews2 = $ad->views_count;
echo "Initial views: {$initialViews2}\n";

try {
    $response = $controller->showAd($ad->id);
    $ad->refresh();
    $newViews2 = $ad->views_count;
    echo "Views after owner viewed: {$newViews2}\n";
    if ($newViews2 === $initialViews2) {
        echo "SUCCESS: Views count did NOT increase when owner viewed the ad!\n";
    } else {
        echo "FAIL: Views count increased when owner viewed the ad (Expected {$initialViews2}, got {$newViews2})!\n";
    }
} catch (\Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
}

// --- Case 3: Non-owner views the ad ---
echo "\n--- Case 3: Non-owner views the ad ---\n";
Auth::login($nonOwner);
$ad->refresh();
$initialViews3 = $ad->views_count;
echo "Initial views: {$initialViews3}\n";

try {
    $response = $controller->showAd($ad->id);
    $ad->refresh();
    $newViews3 = $ad->views_count;
    echo "Views after non-owner viewed: {$newViews3}\n";
    if ($newViews3 === $initialViews3 + 1) {
        echo "SUCCESS: Views count increased by 1 when non-owner viewed the ad!\n";
    } else {
        echo "FAIL: Views count did not increase correctly for non-owner (Expected " . ($initialViews3 + 1) . ", got {$newViews3})!\n";
    }
} catch (\Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
}
