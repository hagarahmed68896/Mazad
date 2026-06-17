<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Banner;

$banner = Banner::find(5);

if ($banner) {
    echo "Updating Banner ID 5...\n";
    
    $items = [
        [
            'title' => 'مزادات السيارات الفاخرة',
            'description' => 'زايد على أفضل السيارات الكلاسيكية والرياضية والحديثة بأسعار مميزة.',
            'image' => 'images/banner_car.png',
            'button_text' => 'تصفح السيارات',
            'show_button' => '1'
        ],
        [
            'title' => 'عقارات وأراضي للبيع والمزاد',
            'description' => 'اكتشف فلل فاخرة، شقق عصرية، وأراضي مميزة للاستثمار والسكن.',
            'image' => 'images/banner_villa.png',
            'button_text' => 'عرض العقارات',
            'show_button' => '1'
        ],
        [
            'title' => 'أحدث الإلكترونيات والأجهزة الذكية',
            'description' => 'تصفح وزايد على الهواتف الذكية، الحواسيب المحمولة، والأجهزة المنزلية.',
            'image' => 'images/banner_electronics.png',
            'button_text' => 'زايد الآن',
            'show_button' => '1'
        ]
    ];

    $banner->update([
        'title' => 'ابحث، بع، أو زايد',
        'description' => 'منصة موثوقة تمكّنك من تصفح آلاف الإعلانات أو إضافة إعلانك خلال دقائق.',
        'image' => 'images/banner_car.png',
        'button_text' => 'عرض التفاصيل',
        'items' => $items
    ]);

    echo "Banner updated successfully!\n";
} else {
    echo "Banner ID 5 not found!\n";
}
