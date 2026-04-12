<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;

$reps = User::where('account_type', 'representative')->get();
$count = 1;
foreach ($reps as $u) {
    if (!$u->phone || !str_starts_with($u->phone, '05')) {
        // Generate a random Saudi-like phone number
        $u->phone = '05' . str_pad($count . rand(100000, 999999), 8, '0', STR_PAD_LEFT);
        $u->save();
        $count++;
    }
}

echo "Added phone numbers to representatives.\n";
