<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdImage;

$data = [
    37 => ['images/building.png', 'images/hero_car.png', 'images/laptop.png'],
    38 => ['images/laptop.png', 'images/hero_car.png'],
    40 => ['images/building.png', 'images/laptop.png', 'images/hero_car.png', 'images/building.png'],
];

foreach ($data as $adId => $images) {
    foreach ($images as $img) {
        AdImage::create([
            'ad_id' => $adId,
            'image' => $img
        ]);
    }
}

echo "Done adding images to ads.";
