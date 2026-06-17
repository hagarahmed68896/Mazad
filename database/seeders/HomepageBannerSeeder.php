<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class HomepageBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banner = Banner::find(5);

        if ($banner) {
            $items = [
                [
                    'title' => 'مزادات السيارات الفاخرة',
                    'description' => 'زايد على أفضل السيارات الكلاسيكية والرياضية والحديثة بأسعار مميزة.',
                    'image' => 'images/banner_car.png',
                    'button_text' => 'تصفح السيارات',
                    'show_button' => '1',
                    'url' => '/categories/1'
                ],
                [
                    'title' => 'عقارات وأراضي للبيع والمزاد',
                    'description' => 'اكتشف فلل فاخرة، شقق عصرية، وأراضي مميزة للاستثمار والسكن.',
                    'image' => 'images/banner_villa.png',
                    'button_text' => 'عرض العقارات',
                    'show_button' => '1',
                    'url' => '/categories/2'
                ],
                [
                    'title' => 'أحدث الإلكترونيات والأجهزة الذكية',
                    'description' => 'تصفح وزايد على الهواتف الذكية، الحواسيب المحمولة، والأجهزة المنزلية.',
                    'image' => 'images/banner_electronics.png',
                    'button_text' => 'زايد الآن',
                    'show_button' => '1',
                    'url' => '/categories/3'
                ]
            ];

            $banner->update([
                'title' => 'ابحث، بع، أو زايد',
                'description' => 'منصة موثوقة تمكّنك من تصفح آلاف الإعلانات أو إضافة إعلانك خلال دقائق.',
                'image' => 'images/banner_car.png',
                'button_text' => 'عرض التفاصيل',
                'items' => $items
            ]);
            
            $this->command->info('Homepage banners updated successfully!');
        } else {
            $this->command->error('Banner with ID 5 was not found.');
        }
    }
}
