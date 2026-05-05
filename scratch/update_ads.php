<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ad;
use Carbon\Carbon;

$ads = Ad::limit(20)->get();
foreach ($ads as $index => $ad) {
    $ad->update([
        'status' => 'active',
        'end_date' => Carbon::now()->addDays($index + 1)->addHours(rand(1, 12))
    ]);
    echo "Updated Ad ID: {$ad->id} - New End Date: {$ad->end_date}\n";
}
echo "Done updating ads.\n";
