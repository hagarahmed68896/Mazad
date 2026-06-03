<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ads = \App\Models\Ad::where('user_id', 92)->get()->toArray();
print_r($ads);
