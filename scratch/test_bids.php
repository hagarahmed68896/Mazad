<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$directPurchaseAds = \App\Models\Ad::where('type', 'direct_purchase')->count();
$bids = \App\Models\Bid::with(['ad'])->get()->map(function($b) {
    return [
        'bid_id' => $b->id,
        'bidder_id' => $b->user_id,
        'ad_id' => $b->ad_id,
        'ad_owner_id' => $b->ad->user_id ?? null,
        'ad_type' => $b->ad->type ?? null,
        'bid_type' => $b->type
    ];
})->toArray();

print_r([
    'direct_purchase_ads_count' => $directPurchaseAds,
    'bids' => $bids
]);
