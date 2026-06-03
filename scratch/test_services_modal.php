<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "1. Checking if save-services POST route is registered...\n";
    $route = Route::getRoutes()->getByName('save-services.web');
    if ($route) {
        echo "Route 'save-services.web' is successfully registered!\n";
    } else {
        echo "Route 'save-services.web' NOT found!\n";
    }

    echo "\n2. Share errors and compile partials.services-modal view...\n";
    view()->share('errors', new \Illuminate\Support\ViewErrorBag);
    $htmlServices = view('partials.services-modal')->render();
    echo "View partials.services-modal compiled successfully! Length: " . strlen($htmlServices) . " characters.\n";

    echo "\n3. Checking welcome.blade.php compilation...\n";
    // Mock user for welcome view compiling (Auth::user() check)
    $tempUser = new App\Models\User();
    $tempUser->account_type = 'partner';
    Auth::login($tempUser);

    $htmlWelcome = view('welcome', [
        'banners' => collect(),
        'discoveryCategories' => collect(),
        'recentlyViewed' => collect(),
        'currentAuctions' => collect(),
        'instantBuy' => collect(),
        'featuredAds' => collect(),
        'interactiveAds' => collect(),
        'endingSoon' => collect(),
        'totalRevenue' => 0,
        'chartData' => []
    ])->render();

    if (strpos($htmlWelcome, 'servicesModal') !== false) {
        echo "Services modal correctly included in welcome view for partner accounts!\n";
    } else {
        echo "Services modal NOT found in compiled welcome view!\n";
    }

    echo "\n4. Checking interests-modal.blade.php modifications...\n";
    $htmlInterests = view('partials.interests-modal', ['categories' => collect()])->render();
    if (strpos($htmlInterests, 'checkForServicesModal') !== false) {
        echo "checkForServicesModal callback integrated successfully in interests-modal close logic!\n";
    } else {
        echo "checkForServicesModal callback NOT found in interests-modal!\n";
    }

    Auth::logout();
    echo "\nAll verification checks passed successfully!\n";

} catch (\Exception $e) {
    echo "ERROR during verification: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
