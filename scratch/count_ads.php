<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Ad;

$user = User::where('phone', 'like', '%512345678')->first();
if ($user) {
    echo "Seller: {$user->name} (ID: {$user->id})\n";
    echo "Total Ads: " . $user->ads()->count() . "\n";
    echo "Total Orders: " . \App\Models\Order::where('seller_id', $user->id)->count() . "\n";
    foreach ($user->ads as $ad) {
        $ord = $ad->orders()->first();
        echo "Ad ID: {$ad->id}, Title: {$ad->title}, Status: {$ad->status}, Order Status: " . ($ord ? $ord->status : 'No Order') . "\n";
    }
} else {
    echo "User 512345678 not found.\n";
}
