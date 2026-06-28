<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;

$adsCount = Ad::count();
$adsWithLatLng = Ad::whereNotNull('lat')->whereNotNull('lng')->count();
echo "Total Ads: {$adsCount}\n";
echo "Ads with coordinates (lat/lng): {$adsWithLatLng}\n";

$sample = Ad::whereNotNull('lat')->whereNotNull('lng')->first();
if ($sample) {
    echo "Sample coordinates ad:\n";
    echo "ID: {$sample->id}\n";
    echo "Lat: {$sample->lat}, Lng: {$sample->lng}\n";
}
