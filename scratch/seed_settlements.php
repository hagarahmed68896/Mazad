<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\FinancialSettlement;

$partners = User::where('account_type', 'partner')->get();

foreach ($partners as $u) {
    if ($u->services()->count() > 0) {
        $services = $u->services()->get();
        // Create 2 settlements randomly
        for($i=0; $i<2; $i++) {
            $service = $services->random();
            $settlement = new FinancialSettlement();
            $settlement->settlement_number = rand(1000, 9999);
            $settlement->user_id = $u->id;
            $settlement->ad_id = $service->id; // storing service id here
            $settlement->total_amount = $service->price;
            $settlement->payment_method = 'تحويل بنكي';
            $settlement->status = rand(0, 1) ? 'transferred' : 'closed';
            $settlement->save();
        }
    }
}

echo "Added financial settlements for partners.\n";
