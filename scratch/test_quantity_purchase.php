<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Ad;
use App\Models\Order;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Find or prepare an ad for testing
$ad = Ad::where('type', 'direct_purchase')->where('status', 'active')->first();
if (!$ad) {
    // If no active direct purchase ad exists, let's prepare one
    $ad = Ad::first();
    if (!$ad) {
        echo "No ads found in database.\n";
        exit;
    }
    $ad->update([
        'type' => 'direct_purchase',
        'status' => 'active',
        'quantity' => 10,
        'price' => 1000
    ]);
} else {
    // Reset quantity for testing consistency
    $ad->update([
        'quantity' => 10,
        'price' => 1000
    ]);
}

$ad->refresh();

$buyer = User::where('id', '!=', $ad->user_id)->first();
if (!$buyer) {
    echo "Requires at least one other user in DB to act as buyer.\n";
    exit;
}

echo "Testing Ad ID: {$ad->id} (Initial Quantity: {$ad->quantity}, Price: {$ad->price}, Type: {$ad->type})\n";
echo "Buyer ID: {$buyer->id}\n";

// Login buyer
Auth::login($buyer);

$controller = new OrderController();

// --- Case 1: Buy 1 unit ---
echo "\n--- Case 1: Buy 1 unit ---\n";
$request1 = new Request();
$request1->merge(['quantity' => 1]);

// Call controller method
try {
    $response = $controller->store($request1, $ad->id);
    $ad->refresh();
    
    $latestOrder = Order::where('ad_id', $ad->id)->where('buyer_id', $buyer->id)->orderBy('id', 'desc')->first();
    
    echo "Ad Quantity after Case 1: {$ad->quantity}\n";
    echo "Ad Status after Case 1: {$ad->status}\n";
    echo "Latest Order Quantity: " . ($latestOrder ? $latestOrder->quantity : 'N/A') . "\n";
    echo "Latest Order Final Price: " . ($latestOrder ? $latestOrder->final_price : 'N/A') . "\n";
    
    if ($ad->quantity === 9 && $latestOrder && (int)$latestOrder->quantity === 1 && $ad->status === 'active') {
        echo "SUCCESS: Case 1 passed!\n";
    } else {
        echo "FAIL: Case 1 failed!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// --- Case 2: Buy 2 units (Multiple purchases allowed) ---
echo "\n--- Case 2: Buy 2 units ---\n";
$request2 = new Request();
$request2->merge(['quantity' => 2]);

try {
    $response = $controller->store($request2, $ad->id);
    $ad->refresh();
    
    $latestOrder = Order::where('ad_id', $ad->id)->where('buyer_id', $buyer->id)->orderBy('id', 'desc')->first();
    
    echo "Ad Quantity after Case 2: {$ad->quantity}\n";
    echo "Latest Order Quantity: " . ($latestOrder ? $latestOrder->quantity : 'N/A') . "\n";
    echo "Latest Order Final Price (expected 2000 + fees): " . ($latestOrder ? $latestOrder->final_price : 'N/A') . "\n";
    
    if ($ad->quantity === 7 && $latestOrder && (int)$latestOrder->quantity === 2) {
        echo "SUCCESS: Case 2 passed!\n";
    } else {
        echo "FAIL: Case 2 failed!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// --- Case 3: Request quantity greater than available (should fail) ---
echo "\n--- Case 3: Request too many units ---\n";
$request3 = new Request();
$request3->merge(['quantity' => 8]); // Remaining is 7

// We mock session to capture error redirect
$session = app('session.store');
$request3->setLaravelSession($session);

try {
    $response = $controller->store($request3, $ad->id);
    $ad->refresh();
    
    echo "Ad Quantity after Case 3: {$ad->quantity}\n";
    $error = session('error');
    echo "Session Error: " . ($error ?? 'None') . "\n";
    
    if ($ad->quantity === 7 && $error && strpos($error, 'غير متوفرة') !== false) {
        echo "SUCCESS: Case 3 passed!\n";
    } else {
        echo "FAIL: Case 3 failed!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// --- Case 4: Buy remaining quantity (should finish the ad) ---
echo "\n--- Case 4: Buy remaining quantity ---\n";
$request4 = new Request();
$request4->merge(['quantity' => 7]);

try {
    $response = $controller->store($request4, $ad->id);
    $ad->refresh();
    
    $latestOrder = Order::where('ad_id', $ad->id)->where('buyer_id', $buyer->id)->orderBy('id', 'desc')->first();
    
    echo "Ad Quantity after Case 4: {$ad->quantity}\n";
    echo "Ad Status after Case 4: {$ad->status}\n";
    echo "Latest Order Quantity: " . ($latestOrder ? $latestOrder->quantity : 'N/A') . "\n";
    
    if ($ad->quantity === 0 && $ad->status === 'finished' && $latestOrder && (int)$latestOrder->quantity === 7) {
        echo "SUCCESS: Case 4 passed!\n";
    } else {
        echo "FAIL: Case 4 failed!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// Reset ad status back to active for testing cleanup if needed
$ad->update(['status' => 'active', 'quantity' => 5]);
