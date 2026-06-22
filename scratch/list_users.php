<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::whereIn('account_type', ['representative', 'partner'])->limit(15)->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Phone: {$u->phone} | Type: {$u->account_type} | Parent ID: {$u->parent_id}\n";
}
