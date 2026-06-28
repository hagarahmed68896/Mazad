<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;
use App\Models\City;
use App\Models\Category;

$citiesCount = City::count();
$adsCount = Ad::count();

echo "Total Cities: {$citiesCount}\n";
echo "Total Ads: {$adsCount}\n";

echo "\nCities list:\n";
foreach (City::all() as $c) {
    $cAds = Ad::where('city_id', $c->id)->count();
    echo "- ID: {$c->id}, Name: {$c->name}, Ads count: {$cAds}\n";
}

$adsWithoutCity = Ad::whereNull('city_id')->count();
echo "Ads without city: {$adsWithoutCity}\n";

$ad = Ad::first();
if ($ad) {
    echo "\nSample Ad Details:\n";
    echo "ID: {$ad->id}\n";
    echo "Title: {$ad->title}\n";
    echo "City ID: " . ($ad->city_id ?? 'NULL') . "\n";
    echo "Category ID: {$ad->category_id}\n";
    echo "Status: {$ad->status}\n";
}
