<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Hash;

// 1. Find or create seller
$seller = User::where('phone', 'like', '%512345678')->first() 
       ?? User::where('phone', 'like', '%0512345678%')->first();
       
if (!$seller) {
    $seller = User::create([
        'name' => 'Hagar Ahmed',
        'email' => 'seller5123@mazad.com',
        'phone' => '512345678',
        'password' => Hash::make('123456'),
        'account_type' => 'individual',
        'status' => 'active',
        'city_id' => City::first()->id ?? null,
    ]);
}

// 2. Find or create buyer
$buyer = User::where('email', 'buyer5123@mazad.com')->first();
if (!$buyer) {
    $buyer = User::create([
        'name' => 'أحمد المشتري',
        'email' => 'buyer5123@mazad.com',
        'phone' => '0599999999',
        'password' => Hash::make('123456'),
        'account_type' => 'individual',
        'status' => 'active',
        'city_id' => City::first()->id ?? null,
    ]);
}

$categories = Category::whereNotNull('parent_id')->get();
if ($categories->isEmpty()) {
    $categories = Category::all();
}
$cities = City::all();

// Get existing images from database
$images = Ad::whereNotNull('image')->limit(12)->pluck('image')->toArray();

// Clean up existing ads of this seller to avoid cluttering
$seller->ads()->delete();

// Array of mock data items for each of the 6 states
$paidItems = [
    ['title' => 'لابتوب ديل XPS 15 شاشة تعمل باللمس', 'price' => 33000, 'type' => 'direct_purchase'],
    ['title' => 'كاميرا سوني Alpha 7 IV الاحترافية', 'price' => 9500, 'type' => 'direct_purchase'],
    ['title' => 'ساعة آبل الترا الإصدار الثاني رياضية', 'price' => 3200, 'type' => 'direct_purchase'],
    ['title' => 'سماعات دي جي بوس اللاسلكية عازلة للضوضاء', 'price' => 1500, 'type' => 'direct_purchase']
];

$pendingPaymentItems = [
    ['title' => 'برج سكني وتجاري استثماري في الرياض', 'price' => 7000000, 'type' => 'electronic_auction'],
    ['title' => 'شقة فاخرة إطلالة بحرية كاملة في جدة', 'price' => 850000, 'type' => 'electronic_auction'],
    ['title' => 'أرض تجارية على طريق الملك فهد بالرياض', 'price' => 12000000, 'type' => 'electronic_auction'],
    ['title' => 'مجمع سكني مغلق حديث شمال العاصمة', 'price' => 25000000, 'type' => 'electronic_auction']
];

$deliveringItems = [
    ['title' => 'لابتوب لينوفو ثينك باد X1 كاربون', 'price' => 10000, 'type' => 'direct_purchase'],
    ['title' => 'جهاز آيباد برو شريحة M2 شاشة 12.9', 'price' => 5400, 'type' => 'direct_purchase'],
    ['title' => 'نظام صوتي منزلي متكامل من ياماها', 'price' => 3800, 'type' => 'direct_purchase'],
    ['title' => 'آلة قهوة بريفيل باريستا إكسبريس الاحترافية', 'price' => 2900, 'type' => 'direct_purchase']
];

$receivedItems = [
    ['title' => 'سيارة بي إم دبليو M4 كوبيه رياضية', 'price' => 2800000, 'type' => 'hybrid_auction'],
    ['title' => 'سيارة مرسيدس C200 موديل 2024 فل كامل', 'price' => 240000, 'type' => 'hybrid_auction'],
    ['title' => 'دراجة نارية رياضية ياماها R6 ياباني', 'price' => 45000, 'type' => 'hybrid_auction'],
    ['title' => 'سيارة تسلا موديل 3 فل كامل كهربائية', 'price' => 180000, 'type' => 'hybrid_auction']
];

$expiredItems = [
    ['title' => 'فيلا سكنية مودرن مع مسبح خاص بحطين', 'price' => 5000000, 'type' => 'electronic_auction'],
    ['title' => 'شاليه فاخر حي الرمال مساحات واسعة', 'price' => 1200000, 'type' => 'electronic_auction'],
    ['title' => 'استراحة خاصة مجهزة بالكامل بالدرعية', 'price' => 3500000, 'type' => 'electronic_auction'],
    ['title' => 'مزرعة منتجة بوسط القصيم مساحة كبرى', 'price' => 8000000, 'type' => 'electronic_auction']
];

$activeItems = [
    ['title' => 'هاتف آيفون 15 برو ماكس 512 جيجا', 'price' => 4500, 'type' => 'direct_purchase'],
    ['title' => 'شاشة إل جي OLED 65 بوصة ذكية 4K', 'price' => 6200, 'type' => 'direct_purchase'],
    ['title' => 'منصة ألعاب بلايستيشن 5 مع ذراعين ولعبة', 'price' => 2100, 'type' => 'direct_purchase'],
    ['title' => 'نظارة أبل فيجن برو للواقع المختلط الجديدة', 'price' => 14500, 'type' => 'direct_purchase']
];

$adIndex = 0;

// Create 4 Paid Ads & Orders
foreach ($paidItems as $index => $item) {
    $img = $images[$adIndex % count($images)] ?? null;
    $adIndex++;
    $ad = Ad::create([
        'user_id' => $seller->id,
        'category_id' => $categories->random()->id ?? null,
        'city_id' => $cities->random()->id ?? null,
        'title' => $item['title'],
        'type' => $item['type'],
        'price' => $item['price'],
        'status' => 'finished',
        'image' => $img,
        'end_date' => now()->subDays($index + 1),
    ]);
    Order::create([
        'order_number' => 'ORD-PAID-' . ($index + 1),
        'ad_id' => $ad->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'final_price' => $item['price'] + 519,
        'status' => 'paid',
    ]);
}

// Create 4 Pending Payment Ads & Orders
foreach ($pendingPaymentItems as $index => $item) {
    $img = $images[$adIndex % count($images)] ?? null;
    $adIndex++;
    $ad = Ad::create([
        'user_id' => $seller->id,
        'category_id' => $categories->random()->id ?? null,
        'city_id' => $cities->random()->id ?? null,
        'title' => $item['title'],
        'type' => $item['type'],
        'price' => $item['price'],
        'status' => 'finished',
        'image' => $img,
        'end_date' => now()->subDays($index + 1),
    ]);
    Order::create([
        'order_number' => 'ORD-PEND-' . ($index + 1),
        'ad_id' => $ad->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'final_price' => $item['price'] + 519,
        'status' => 'pending_payment',
    ]);
}

// Create 4 Delivering Ads & Orders (Confirm Delivery)
foreach ($deliveringItems as $index => $item) {
    $img = $images[$adIndex % count($images)] ?? null;
    $adIndex++;
    $ad = Ad::create([
        'user_id' => $seller->id,
        'category_id' => $categories->random()->id ?? null,
        'city_id' => $cities->random()->id ?? null,
        'title' => $item['title'],
        'type' => $item['type'],
        'price' => $item['price'],
        'status' => 'finished',
        'image' => $img,
        'end_date' => now()->subDays($index + 1),
    ]);
    Order::create([
        'order_number' => 'ORD-RECV-' . ($index + 1),
        'ad_id' => $ad->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'final_price' => $item['price'] + 519,
        'status' => 'receiving',
        'delivery_code' => '123456',
    ]);
}

// Create 4 Received Ads & Orders
foreach ($receivedItems as $index => $item) {
    $img = $images[$adIndex % count($images)] ?? null;
    $adIndex++;
    $ad = Ad::create([
        'user_id' => $seller->id,
        'category_id' => $categories->random()->id ?? null,
        'city_id' => $cities->random()->id ?? null,
        'title' => $item['title'],
        'type' => $item['type'],
        'price' => $item['price'],
        'status' => 'finished',
        'image' => $img,
        'end_date' => now()->subDays($index + 1),
    ]);
    Order::create([
        'order_number' => 'ORD-RCVD-' . ($index + 1),
        'ad_id' => $ad->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'final_price' => $item['price'] + 519,
        'status' => 'received',
    ]);
}

// Create 4 Expired Unsold Ads (منتهي - إعادة بيع / تعديل)
foreach ($expiredItems as $index => $item) {
    $img = $images[$adIndex % count($images)] ?? null;
    $adIndex++;
    Ad::create([
        'user_id' => $seller->id,
        'category_id' => $categories->random()->id ?? null,
        'city_id' => $cities->random()->id ?? null,
        'title' => $item['title'],
        'type' => $item['type'],
        'price' => $item['price'],
        'status' => 'finished',
        'image' => $img,
        'end_date' => now()->subDays($index + 2),
    ]);
}

// Create 4 Active Unsold Ads (قائم - تعديل)
foreach ($activeItems as $index => $item) {
    $img = $images[$adIndex % count($images)] ?? null;
    $adIndex++;
    Ad::create([
        'user_id' => $seller->id,
        'category_id' => $categories->random()->id ?? null,
        'city_id' => $cities->random()->id ?? null,
        'title' => $item['title'],
        'type' => $item['type'],
        'price' => $item['price'],
        'status' => 'active',
        'image' => $img,
        'end_date' => now()->addDays($index + 3),
    ]);
}

echo "SUCCESSFULLY SEEDED 24 RICH SALES CARDS FOR USER 512345678 (ID: {$seller->id})\n";
