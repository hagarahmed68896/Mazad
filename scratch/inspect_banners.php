<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- Banners --- \n";
$banners = \App\Models\Banner::all();
echo "Total banners: " . count($banners) . "\n";
foreach ($banners as $b) {
    echo "ID: {$b->id} | Title: {$b->title} | Image: {$b->image} | Status: " . ($b->status ? 'Active' : 'Inactive') . "\n";
    echo "   Description: {$b->description}\n";
    echo "   Button text: {$b->button_text}\n";
    echo "---------------------------------\n";
}
