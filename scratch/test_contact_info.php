<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$controller = new \App\Http\Controllers\Api\SupportController();
$response = $controller->contactInfo();

echo "Response Status Code: " . $response->getStatusCode() . "\n";
echo "Response Body:\n";
print_r(json_decode($response->getContent(), true));
