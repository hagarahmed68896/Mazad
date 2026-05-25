<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;

$user = User::find(67);
auth('sanctum')->setUser($user);
auth()->setUser($user);
\Illuminate\Support\Facades\Auth::setUser($user);

$controller = new OrderController();

// 1. Get status=all
$reqAll = Request::create('/api/my-sales', 'GET', ['status' => 'all', 'per_page' => 1]);
$resAll = $controller->mySales($reqAll);
$dataAll = json_decode($resAll->getContent(), true);

// 2. Get status=sold
$reqSold = Request::create('/api/my-sales', 'GET', ['status' => 'sold', 'per_page' => 1]);
$resSold = $controller->mySales($reqSold);
$dataSold = json_decode($resSold->getContent(), true);

// 3. Get status=not_sold
$reqNotSold = Request::create('/api/my-sales', 'GET', ['status' => 'not_sold', 'per_page' => 1]);
$resNotSold = $controller->mySales($reqNotSold);
$dataNotSold = json_decode($resNotSold->getContent(), true);

// 4. Get q=لابتوب
$reqSearch = Request::create('/api/my-sales', 'GET', ['q' => 'لابتوب', 'per_page' => 1]);
$resSearch = $controller->mySales($reqSearch);
$dataSearch = json_decode($resSearch->getContent(), true);

echo "=== ALL ===\n";
echo json_encode($dataAll, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== SOLD ===\n";
echo json_encode($dataSold, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== NOT_SOLD ===\n";
echo json_encode($dataNotSold, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== SEARCH ===\n";
echo json_encode($dataSearch, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
