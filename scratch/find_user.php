<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;

$phone = '512345678';
$user = User::where('phone', 'like', '%'.$phone)->first();

if (!$user) {
    $user = User::where('phone', 'like', '%0512345678%')->first();
}

if ($user) {
    echo "USER_FOUND: ID={$user->id}, Name={$user->name}, Phone={$user->phone}\n";
} else {
    echo "USER_NOT_FOUND\n";
    // List some users
    $users = User::limit(5)->get();
    foreach ($users as $u) {
        echo "User ID={$u->id}, Name={$u->name}, Phone={$u->phone}\n";
    }
}
