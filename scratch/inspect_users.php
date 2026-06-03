<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::limit(10)->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Phone: {$u->phone} | Account Type: {$u->account_type}\n";
}
