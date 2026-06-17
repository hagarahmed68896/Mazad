<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$banner = new \App\Models\Banner();

$testCases = [
    ['title' => 'سيارات للبيع والشراء', 'desc' => 'تصفح أحدث موديلات السيارات بأفضل الأسعار'],
    ['title' => 'فلل فاخرة في الرياض', 'desc' => 'عقارات ممتازة مع مسبح وحديقة'],
    ['title' => 'شاشات ذكية وجوالات', 'desc' => 'أفضل عروض الأجهزة الإلكترونية'],
    ['title' => 'كنب ومجالس فخمة', 'desc' => 'أثاث منزلي مميز بألوان رائعة'],
    ['title' => 'صيانة مكيفات وتركيب', 'desc' => 'خدمات فورية بأيدي خبراء'],
    ['title' => 'مزاد الصيف العظيم', 'desc' => 'عروض حصرية لفترة محدودة']
];

$items = [];
foreach ($testCases as $tc) {
    $items[] = [
        'title' => $tc['title'],
        'description' => $tc['desc'],
        'url' => '#'
    ];
}

$banner->items = $items;

echo "API Response Banners Items JSON:\n";
echo json_encode($banner->items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
