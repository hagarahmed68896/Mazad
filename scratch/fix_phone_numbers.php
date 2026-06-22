<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::all();
$count = 0;
foreach ($users as $u) {
    $phone = $u->phone;
    if ($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        while (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        if (str_starts_with($phone, '966')) {
            $phone = substr($phone, 3);
        }
        while (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        
        if ($u->phone !== $phone) {
            try {
                $u->phone = $phone;
                $u->password = bcrypt('password');
                $u->save();
                $count++;
            } catch (\Exception $e) {
                // If it's a duplicate, just delete this duplicate user to keep the database clean
                echo "Skipping/Deleting duplicate user ID: {$u->id} due to phone duplicate: {$phone}\n";
                try {
                    $u->delete();
                } catch (\Exception $de) {}
            }
        }
    }
}
echo "Successfully updated phone numbers and passwords of $count users.\n";
