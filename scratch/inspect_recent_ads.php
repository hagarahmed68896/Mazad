<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;

$latestAds = Ad::latest()->limit(5)->get()->toArray();
print_r($latestAds);
