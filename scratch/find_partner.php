<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::where('phone', 'like', '%577777777%')->first();
if ($user) {
    echo "Found user ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Phone: " . $user->phone . "\n";
    echo "Account Type: " . $user->account_type . "\n";
    print_r($user->toArray());
} else {
    echo "User not found.\n";
}
