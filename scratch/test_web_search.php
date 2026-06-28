<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Models\Ad;

// Let's print out what ads have category_id = 1 (Cars) and their city_id
$carsAds = Ad::where('category_id', 1)->get();
echo "Total Cars ads: " . $carsAds->count() . "\n";
foreach ($carsAds as $ad) {
    echo "- ID: {$ad->id}, Title: {$ad->title}, City ID: " . ($ad->city_id ?? 'NULL') . "\n";
}

// Now let's simulate calling HomeController@search with category = 1 and city = 1
$request = Request::create('/search', 'GET', [
    'category' => 1,
    'city' => 1
]);

$controller = new HomeController();
// We might need to mock or just inspect what the query returns.
// Let's execute the controller method
$response = $controller->search($request);
// Get view data
$data = $response->getData();
$ads = $data['ads'];
echo "\nSimulated Search for category = 1, city = 1:\n";
echo "Returned ads count: " . $ads->count() . "\n";
foreach ($ads as $ad) {
    echo "- ID: {$ad->id}, Title: {$ad->title}, City ID: " . ($ad->city_id ?? 'NULL') . "\n";
}
