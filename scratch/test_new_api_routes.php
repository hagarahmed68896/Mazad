<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;

$user = User::find(67);
auth('sanctum')->setUser($user);
auth()->setUser($user);
\Illuminate\Support\Facades\Auth::setUser($user);

echo "==================================================\n";
echo "Testing Brand New API Endpoints for Sales and Reports\n";
echo "==================================================\n\n";

// 1. Test GET /api/my-sales
echo "1. Testing GET /api/my-sales ...\n";
$req1 = Request::create('/api/my-sales', 'GET');
$res1 = (new OrderController())->mySales($req1);
$data1 = json_decode($res1->getContent(), true);
echo "Success: " . ($data1['success'] ? 'Yes' : 'No') . "\n";
echo "Message: " . $data1['message'] . "\n";
echo "Total Ads fetched: " . count($data1['data']['data']) . "\n\n";

// 2. Test GET /api/reports (Sales Tab, Monthly Period)
echo "2. Testing GET /api/reports (Sales Tab, Monthly Period) ...\n";
$req2 = Request::create('/api/reports', 'GET', ['tab' => 'sales', 'period' => 'monthly']);
$res2 = (new ReportController())->index($req2);
$data2 = json_decode($res2->getContent(), true);
echo "Success: " . ($data2['success'] ? 'Yes' : 'No') . "\n";
echo "Message: " . $data2['message'] . "\n";
echo "Revenue Stat: " . $data2['data']['stats']['total_revenue'] . "\n";
echo "Chart Labels count: " . count($data2['data']['chart']['labels']) . "\n";
echo "Chart Data points count: " . count($data2['data']['chart']['data']) . "\n";
echo "Items Count: " . count($data2['data']['items']['data']) . "\n\n";

// 3. Test GET /api/reports (Purchases Tab, Weekly Period)
echo "3. Testing GET /api/reports (Purchases Tab, Weekly Period) ...\n";
$req3 = Request::create('/api/reports', 'GET', ['tab' => 'purchases', 'period' => 'weekly']);
$res3 = (new ReportController())->index($req3);
$data3 = json_decode($res3->getContent(), true);
echo "Success: " . ($data3['success'] ? 'Yes' : 'No') . "\n";
echo "Purchases Count Stat: " . $data3['data']['stats']['purchases_count'] . "\n";
echo "Chart Labels: " . implode(', ', $data3['data']['chart']['labels']) . "\n";
echo "Chart Data: " . implode(', ', $data3['data']['chart']['data']) . "\n";
echo "Items Count: " . count($data3['data']['items']['data']) . "\n\n";

echo "All tests completed!\n";
