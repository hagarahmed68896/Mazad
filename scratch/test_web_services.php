<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "--- Test 1: WEB Partner Onboarding Form & Services Rendering ---\n";

$partner = User::where('phone', '533333333')->first();
if (!$partner) {
    echo "ERROR: Partner user not found.\n";
    exit(1);
}

// Ensure the partner has category 1 (Automobiles) as interest
$partner->interests()->sync([1]);
echo "Partner interests synced to Category 1 (Automobiles).\n";

// Authenticate partner on Web guard
Auth::login($partner);

$controller = new \App\Http\Controllers\AuthController();
$response1 = $controller->showPartnerServicesForm();

// Get the variables passed to the view
$viewData = $response1->getData();
$services = $viewData['services'] ?? collect();

echo "Show Services page returned view: " . $response1->getName() . "\n";
echo "Dynamic Services passed to view: " . $services->count() . "\n";
foreach ($services as $s) {
    echo "  - Service Template ID: {$s->id} | Title: {$s->title} | Category: {$s->category_id}\n";
}

// 2. Mock saving services via onboarding form
echo "\n--- Test 2: WEB savePartnerServices Onboarding Form ---\n";
// Choose admin template service ID 84 and 86 (which are category 1)
$adminServiceIds = Service::where('user_id', 1)->where('category_id', 1)->pluck('id')->toArray();
if (empty($adminServiceIds)) {
    echo "ERROR: No admin services under category 1 to test with.\n";
    exit(1);
}

echo "Selected services for saving: " . implode(', ', $adminServiceIds) . "\n";

$request2 = Request::create('register/partner/services', 'POST', ['services' => $adminServiceIds]);
$response2 = $controller->savePartnerServices($request2);

// Check DB
$savedServicesCount = Service::where('user_id', $partner->id)->count();
echo "Redirect URL: " . $response2->getTargetUrl() . "\n";
echo "Services stored in DB for Partner: " . $savedServicesCount . "\n";
foreach (Service::where('user_id', $partner->id)->get() as $s) {
    echo "  - Stored Service ID: {$s->id} | Title: {$s->title} | Category: {$s->category_id}\n";
}

if ($savedServicesCount === count($adminServiceIds)) {
    echo "\nWEB TEST PASSED SUCCESSFULLY! 🎉\n";
} else {
    echo "\nWEB TEST FAILED.\n";
}
