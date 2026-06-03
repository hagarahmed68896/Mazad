<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Total ads: " . \App\Models\Ad::count() . "\n";
echo "Total records in ad_properties: " . \App\Models\AdProperty::count() . "\n";

echo "\n--- Last 5 Ad Properties records ---\n";
$records = \App\Models\AdProperty::orderBy('id', 'desc')->limit(10)->get();
foreach ($records as $rec) {
    echo "ID: {$rec->id} | Ad ID: {$rec->ad_id} | Property ID: {$rec->property_id} | Value: {$rec->value}\n";
}

echo "\n--- Last Ad with its properties ---\n";
$lastAd = \App\Models\Ad::orderBy('id', 'desc')->first();
if ($lastAd) {
    echo "Ad ID: {$lastAd->id} | Title: {$lastAd->title} | Category ID: {$lastAd->category_id}\n";
    $adProps = $lastAd->adProperties;
    echo "Loaded adProperties count: " . $adProps->count() . "\n";
    foreach ($adProps as $ap) {
        echo "  -> Property ID: {$ap->property_id} | Value: {$ap->value}\n";
    }
}
