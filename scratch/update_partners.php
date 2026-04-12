<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Service;
use App\Models\Category;

$partners = User::where('account_type', 'partner')->get();
$count = 1;
foreach ($partners as $u) {
    if (!$u->phone || !str_starts_with($u->phone, '05')) {
        $u->phone = '05' . str_pad($count . rand(100000, 999999), 8, '0', STR_PAD_LEFT);
        $u->save();
        $count++;
    }
    
    // Check if they need services
    if ($u->services()->count() < 1) {
        $numServices = rand(1, 10);
        $cat = Category::first(); // Just get any category
        for ($i = 0; $i < $numServices; $i++) {
            $service = new Service();
            $service->user_id = $u->id;
            $service->category_id = $cat ? $cat->id : null;
            $service->title = 'خدمة ' . rand(1, 100);
            $service->description = 'وصف الخدمة';
            $service->price = rand(100, 1000);
            $service->save();
        }
    }
}

echo "Added phone numbers and services to partners.\n";
