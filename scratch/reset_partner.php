<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = \App\Models\User::where('phone', '533333333')->first();
if ($u) {
    $u->password = bcrypt('password123');
    $u->save();
    echo "Password for partner (533333333) has been reset to: password123\n";
} else {
    echo "Partner user with phone 533333333 not found.\n";
}
