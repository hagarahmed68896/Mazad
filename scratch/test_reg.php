<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::create([
    'name' => 'Test Registration',
    'phone' => '500000001',
    'email' => 'testreg@example.com',
    'password' => bcrypt('password'),
    'type' => 'company',
    'company_name' => 'Test Company',
    'cr_number' => '1234567890',
]);

echo "Created User ID: {$user->id}\n";
echo "Account Type: {$user->account_type}\n";
echo "Company Name: {$user->company_name}\n";

$user->delete();
