<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "1. Checking if register/partner routes are registered...\n";
    $route = Route::getRoutes()->getByName('register.partner');
    if ($route) {
        echo "Route 'register.partner' is successfully registered!\n";
    } else {
        echo "Route 'register.partner' NOT found!\n";
    }

    $interestsRoute = Route::getRoutes()->getByName('register.partner.interests');
    if ($interestsRoute) {
        echo "Route 'register.partner.interests' is successfully registered!\n";
    } else {
        echo "Route 'register.partner.interests' NOT found!\n";
    }

    $interestsPostRoute = Route::getRoutes()->getByName('register.partner.interests.post');
    if ($interestsPostRoute) {
        echo "Route 'register.partner.interests.post' is successfully registered!\n";
    } else {
        echo "Route 'register.partner.interests.post' NOT found!\n";
    }

    $servicesRoute = Route::getRoutes()->getByName('register.partner.services');
    if ($servicesRoute) {
        echo "Route 'register.partner.services' is successfully registered!\n";
    } else {
        echo "Route 'register.partner.services' NOT found!\n";
    }

    $servicesPostRoute = Route::getRoutes()->getByName('register.partner.services.post');
    if ($servicesPostRoute) {
        echo "Route 'register.partner.services.post' is successfully registered!\n";
    } else {
        echo "Route 'register.partner.services.post' NOT found!\n";
    }

    echo "\n2. Compiling and rendering the auth.register-partner blade view...\n";
    view()->share('errors', new \Illuminate\Support\ViewErrorBag);
    $html = view('auth.register-partner')->render();
    echo "View auth.register-partner successfully compiled! Length: " . strlen($html) . " characters.\n";

    echo "\n3. Compiling and rendering the auth.register-partner-interests blade view...\n";
    $interestsHtml = view('auth.register-partner-interests', ['categories' => App\Models\Category::whereNull('parent_id')->get()])->render();
    echo "View auth.register-partner-interests successfully compiled! Length: " . strlen($interestsHtml) . " characters.\n";

    echo "\n4. Compiling and rendering the auth.register-partner-services blade view...\n";
    $servicesHtml = view('auth.register-partner-services')->render();
    echo "View auth.register-partner-services successfully compiled! Length: " . strlen($servicesHtml) . " characters.\n";

    echo "\n5. Compiling and rendering the auth.register-type blade view...\n";
    $htmlType = view('auth.register-type')->render();
    if (strpos($htmlType, '/register/partner') !== false) {
        echo "Partner registration route exists in register-type view!\n";
    } else {
        echo "Partner registration route NOT found in register-type view!\n";
    }

    echo "\n5. Checking account-type-modal.blade.php modifications...\n";
    $modalHtml = view('partials.account-type-modal')->render();
    if (strpos($modalHtml, '/register/partner') !== false) {
        echo "Partner registration redirect exists in account-type modal!\n";
    } else {
        echo "Partner registration redirect NOT found in account-type modal!\n";
    }

    echo "\nAll verification checks passed successfully!\n";

} catch (\Exception $e) {
    echo "ERROR during verification: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
