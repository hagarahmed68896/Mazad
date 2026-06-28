<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;
use App\Models\Category;

$activeCategory = Category::with('subCategories')->find(1);
$categoryIds = $activeCategory->subCategories->pluck('id')->push($activeCategory->id)->toArray();

$ads = Ad::whereIn('category_id', $categoryIds)->get();
echo "Total Ads in Category 1 & subcategories: " . $ads->count() . "\n";
foreach ($ads as $ad) {
    echo "- ID: {$ad->id}, Title: {$ad->title}, City ID: " . ($ad->city_id ?? 'NULL') . "\n";
}
