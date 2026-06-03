<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = App\Models\Order::find(73);
if ($order) {
    echo "Order 73 Details:\n";
    echo "Seller ID: " . $order->seller_id . "\n";
    echo "Buyer ID: " . $order->buyer_id . "\n";
    echo "Final Price: " . $order->final_price . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Delivery Code: " . $order->delivery_code . "\n";
} else {
    echo "Order 73 not found in the database.\n";
}
