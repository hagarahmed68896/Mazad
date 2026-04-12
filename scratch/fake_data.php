<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;

$reps = User::where('account_type', 'representative')->get();
foreach ($reps as $u) {
    // The columns in DB are ads_count, sales, purchases.
    $u->ads_count = rand(0, 15);
    $u->sales = rand(0, 5000);
    $u->purchases = rand(0, 3000);
    $u->save();
}

echo "Added data to " . $reps->count() . " representatives.\n";
