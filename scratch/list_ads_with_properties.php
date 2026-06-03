<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- Ads with Properties saved ---\n";
$adIds = \App\Models\AdProperty::distinct()->pluck('ad_id');
$ads = \App\Models\Ad::whereIn('id', $adIds)->get();

foreach ($ads as $ad) {
    echo "Ad ID: {$ad->id} | Title: {$ad->title} | Category: " . optional($ad->category)->name . " (ID: {$ad->category_id})\n";
    $props = $ad->adProperties;
    foreach ($props as $ap) {
        $propName = optional(\App\Models\Property::find($ap->property_id))->name;
        // fallback if name is empty
        if (empty($propName)) $propName = "Property #{$ap->property_id}";
        echo "   -> {$propName} = {$ap->value}\n";
    }
    echo "--------------------------------------------------------\n";
}
