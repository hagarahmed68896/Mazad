<?php

use App\Models\Ad;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$locations = [
    ['lat' => 24.7136, 'lng' => 46.6753, 'address' => 'حي العليا، الرياض'],
    ['lat' => 24.7236, 'lng' => 46.6853, 'address' => 'حي الملقا، الرياض'],
    ['lat' => 24.7036, 'lng' => 46.6653, 'address' => 'حي الصحافة، الرياض'],
    ['lat' => 21.4858, 'lng' => 39.1925, 'address' => 'حي الشاطئ، جدة'],
    ['lat' => 26.4207, 'lng' => 50.0888, 'address' => 'حي الزهور، الدمام'],
    ['lat' => 24.4672, 'lng' => 39.6108, 'address' => 'حي العزيزية، المدينة'],
    ['lat' => 18.2164, 'lng' => 42.5053, 'address' => 'حي المنهل، أبها'],
];

$ads = Ad::all();
foreach ($ads as $index => $ad) {
    $loc = $locations[$index % count($locations)];
    $ad->update([
        'lat' => $loc['lat'] + (rand(-500, 500) / 10000),
        'lng' => $loc['lng'] + (rand(-500, 500) / 10000),
        'address' => $loc['address']
    ]);
}

echo "Successfully updated " . count($ads) . " ads with coordinates and addresses.\n";
