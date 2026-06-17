<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$categories = \App\Models\Category::whereNull('parent_id')->get();
foreach ($categories as $cat) {
    echo "ID: {$cat->id} | Name: {$cat->name} | Raw Image: " . $cat->getRawOriginal('image') . " | Resolved Image: " . $cat->image . "\n";
}
