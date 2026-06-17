<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;
use App\Models\Service;

$activeAds = Ad::where('status', 'active')
    ->where(function($q) {
        $q->whereNull('end_date')->orWhere('end_date', '>', now());
    })
    ->get();

echo "Active Ads in DB:\n";
foreach ($activeAds as $ad) {
    // Check if it has suggested services under same category
    $hasServices = Service::where('category_id', $ad->category_id)->exists();
    if ($hasServices) {
        $services = Service::where('category_id', $ad->category_id)->get();
        echo "Ad ID: {$ad->id} | Title: {$ad->title} (Category: {$ad->category->name} - ID: {$ad->category_id}) -> HAS SUGGESTED SERVICES!\n";
        foreach ($services as $service) {
            echo "   - Service ID: {$service->id} | Title: {$service->title}\n";
        }
    } else {
        echo "Ad ID: {$ad->id} | Title: {$ad->title} (Category: {$ad->category->name} - ID: {$ad->category_id})\n";
    }
}
