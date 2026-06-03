<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Bid;
use App\Models\User;

// Let's find an active bid of type offer
$bid = Bid::where('type', 'offer')->first();

if (!$bid) {
    echo "No bid of type offer found.\n";
    exit;
}

echo "Found bid ID: " . $bid->id . " with current amount: " . $bid->amount . " and current validity_days: " . $bid->validity_days . "\n";

// Emulate user auth
$ad = $bid->ad;
$seller = User::find($ad->user_id);
Auth::login($seller);

$request = Request::create('/api/bids-offers/' . $bid->id . '/counter', 'POST', [
    'amount' => $bid->amount + 500,
    'validity_days' => 5
]);

$controller = new \App\Http\Controllers\Api\BidController();
$response = $controller->counterBidOffer($request, $bid->id);

echo "Response Status: " . $response->getStatusCode() . "\n";
echo "Response Body:\n";
print_r(json_decode($response->getContent(), true));
