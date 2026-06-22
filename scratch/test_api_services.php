<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;

// 1. Mock Request for GET /api/services/options (All options)
echo "--- Test 1: GET /api/services/options (All Admin Services) ---\n";
$request1 = Request::create('/api/services/options', 'GET');
$controller = new \App\Http\Controllers\Api\HomeController();
$response1 = $controller->servicesOptions($request1);
$data1 = json_decode($response1->getContent(), true);

echo "Success Status: " . ($data1['success'] ? 'TRUE' : 'FALSE') . "\n";
echo "Message: " . $data1['message'] . "\n";
echo "Total Services found: " . count($data1['data']) . "\n";
foreach ($data1['data'] as $s) {
    echo "  - Service ID: {$s['id']} | Category ID: {$s['category_id']} | Title: {$s['title']}\n";
}

// 2. Mock Request for GET /api/services/options?category_ids=1 (Cars Category only)
echo "\n--- Test 2: GET /api/services/options?category_ids=1 (Cars Category Only) ---\n";
$request2 = Request::create('/api/services/options', 'GET', ['category_ids' => '1']);
$response2 = $controller->servicesOptions($request2);
$data2 = json_decode($response2->getContent(), true);

echo "Success Status: " . ($data2['success'] ? 'TRUE' : 'FALSE') . "\n";
echo "Total Services found for Category 1: " . count($data2['data']) . "\n";
foreach ($data2['data'] as $s) {
    echo "  - Service ID: {$s['id']} | Category ID: {$s['category_id']} | Title: {$s['title']}\n";
}

// 3. Mock Request for POST /api/profile/services
echo "\n--- Test 3: POST /api/profile/services (Save selected services for Partner) ---\n";
$partner = User::where('phone', '533333333')->first();
if (!$partner) {
    echo "ERROR: Partner user not found.\n";
    exit(1);
}

// Let's select service templates (which are admin template services for category 1)
$adminServiceIds = Service::where('user_id', 1)->pluck('id')->toArray();
if (empty($adminServiceIds)) {
    echo "ERROR: No admin services found to test with.\n";
    exit(1);
}
// Use the first two admin services
$testServiceIds = array_slice($adminServiceIds, 0, 2);

echo "Selected Admin Template Service IDs: " . implode(', ', $testServiceIds) . "\n";

$request3 = Request::create('/api/profile/services', 'POST', ['service_ids' => $testServiceIds]);
$request3->setUserResolver(fn () => $partner); // Mock authenticated user

$authController = new \App\Http\Controllers\Api\AuthController();
$response3 = $authController->updateServices($request3);
$data3 = json_decode($response3->getContent(), true);

echo "Success Status: " . ($data3['success'] ? 'TRUE' : 'FALSE') . "\n";
echo "Message: " . $data3['message'] . "\n";

// Verify database
$partnerServices = Service::where('user_id', $partner->id)->get();
echo "Partner Services saved in DB: " . $partnerServices->count() . "\n";
foreach ($partnerServices as $ps) {
    echo "  - Partner Service ID: {$ps['id']} | Category: {$ps['category_id']} | Title: {$ps['title']} | Price: {$ps['price']}\n";
}

if ($partnerServices->count() === count($testServiceIds)) {
    echo "\nTEST PASSED SUCCESSFULLY! 🎉\n";
} else {
    echo "\nTEST FAILED.\n";
}
