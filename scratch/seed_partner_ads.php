<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;
use App\Models\City;
use App\Models\Category;

$user_id = 92; // زين محمد (577777777)
$city_id = City::first()->id ?? 1;
$category_id = 4; // الخدمات

// Delete any existing ads for this user to make it clean
Ad::where('user_id', $user_id)->delete();

$adsData = [
    // 1. Inspection (فحص)
    [
        'title' => 'خدمة فحص السيارات الشامل',
        'price' => 150,
        'description' => 'نقدم لكم خدمة الفحص الشامل للسيارة قبل الشراء لضمان سلامتها الفنية والتقنية بالكامل بتقرير معتمد.',
        'image' => 'images/hero_car.png'
    ],
    [
        'title' => 'خدمة فحص محرك وقير السيارات',
        'price' => 300,
        'description' => 'فحص دقيق ومفصل لمحرك السيارة وناقل الحركة (القير) باستخدام أحدث أجهزة الكمبيوتر لتحديد الأعطال.',
        'image' => 'images/hero_car.png'
    ],
    [
        'title' => 'خدمة فحص سمكرة وهيكل السيارات',
        'price' => 200,
        'description' => 'فحص دقيق لكشف العيوب في بودي وهيكل السيارة، والتأكد من عدم وجود حوادث أو تعديلات سابقة.',
        'image' => 'images/hero_car.png'
    ],
    // 2. Shipping (شحن)
    [
        'title' => 'خدمة شحن السيارات المغلقة VIP',
        'price' => 600,
        'description' => 'خدمة شحن ونقل السيارات الفارهة والرياضية في سطحات مغلقة بالكامل لضمان أقصى درجات الحماية والأمان.',
        'image' => 'images/hero_car.png'
    ],
    [
        'title' => 'خدمة شحن سريع ونقل بين المدن',
        'price' => 350,
        'description' => 'نقل سريع للسيارات بين جميع مدن المملكة العربية السعودية بأسعار تنافسية وتأمين شامل على الرحلة.',
        'image' => 'images/hero_car.png'
    ],
    [
        'title' => 'خدمة شحن سيارات هيدروليك',
        'price' => 180,
        'description' => 'نقل آمن للسيارات الرياضية والمنخفضة باستخدام سطحات هيدروليك حديثة مجهزة بالكامل.',
        'image' => 'images/hero_car.png'
    ],
    // 3. Purchase (شراء)
    [
        'title' => 'خدمة الشراء والتفاوض بالنيابة',
        'price' => 400,
        'description' => 'نقوم بالتفاوض المباشر مع البائع وتدقيق المستندات لضمان أفضل سعر وشراء آمن بالكامل دون عناء.',
        'image' => 'images/hero_car.png'
    ],
    [
        'title' => 'خدمة شراء فوري متكاملة وتوصيل',
        'price' => 500,
        'description' => 'خدمة متكاملة تشمل فحص السيارة، إنهاء إجراءات نقل الملكية والشراء الفوري، وشحنها حتى باب منزلك.',
        'image' => 'images/hero_car.png'
    ]
];

foreach ($adsData as $data) {
    Ad::create([
        'user_id' => $user_id,
        'category_id' => $category_id,
        'city_id' => $city_id,
        'title' => $data['title'],
        'price' => $data['price'],
        'description' => $data['description'],
        'image' => $data['image'],
        'type' => 'direct_purchase',
        'status' => 'active',
        'end_date' => now()->addDays(30),
    ]);
}

echo "Successfully seeded " . count($adsData) . " rich service ads for user ID: " . $user_id . "\n";
