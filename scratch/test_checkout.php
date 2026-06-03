<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Let's find first user and create a cart item if none exists
$user = \App\Models\User::first();
if (!$user) {
    echo "No users found!\n";
    exit;
}

$ad = \App\Models\Ad::first();
if (!$ad) {
    echo "No ads found!\n";
    exit;
}

// Create or get cart item
$cartItem = \App\Models\Cart::firstOrCreate([
    'user_id' => $user->id,
    'ad_id' => $ad->id,
], [
    'quantity' => 1,
    'price' => $ad->price
]);

echo "Cart Item created/found: ID = {$cartItem->id}, User ID = {$cartItem->user_id}, Quantity = {$cartItem->quantity}\n";

// Authenticate as this user
Auth::login($user);

// Simulate checkout request
$request = \Illuminate\Http\Request::create("/api/cart/checkout", 'POST', [
    'buyer_name' => 'Test Buyer',
    'buyer_phone' => '0512345678',
    'buyer_address' => 'Riyadh, Saudi Arabia',
    'payment_method' => 'cod'
]);

$controller = new \App\Http\Controllers\Api\CartController();
try {
    $response = $controller->checkout($request);
    echo "Response status: " . $response->status() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Exception thrown: " . $e->getMessage() . "\n";
    echo "Stack trace: \n" . $e->getTraceAsString() . "\n";
}
