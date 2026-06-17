<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Banner;

class PartnerBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing partner banners first
        Banner::where('for_user_type', 'partner')->delete();

        // Banner 1: Partner services welcome banner
        Banner::create([
            'name' => 'ترحيب الشركاء والخدمات',
            'status' => true,
            'for_user_type' => 'partner',
            'title' => 'كن شريكًا مميزاً وزد مبيعاتك',
            'image' => 'images/car_inspection.png',
            'description' => 'اعرض خدماتك مثل الفحص، الشحن أو الصيانة لتصل لآلاف العملاء النشطين يومياً.',
            'button_text' => 'أضف خدمة الآن',
            'items' => [
                [
                    'title' => 'كن شريكًا مميزاً وزد مبيعاتك',
                    'description' => 'اعرض خدماتك مثل الفحص، الشحن أو الصيانة لتصل لآلاف العملاء النشطين يومياً.',
                    'button_text' => 'أضف خدمة الآن',
                    'show_button' => '1',
                    'image' => 'images/car_inspection.png',
                ],
                [
                    'title' => 'خدمات الفحص المعتمدة',
                    'description' => 'قم بإجراء عمليات فحص السيارات للعملاء ووفر لهم تقارير معتمدة وموثوقة مباشرة عبر المنصة.',
                    'button_text' => 'عرض لوحة الفحوصات',
                    'show_button' => '1',
                    'image' => 'images/car_shipping.png',
                ]
            ]
        ]);

        // Banner 2: Partner special discount banner
        Banner::create([
            'name' => 'العروض والعمولات للشركاء',
            'status' => true,
            'for_user_type' => 'partner',
            'title' => 'عمولات مخفضة هذا الشهر للشركاء',
            'image' => 'images/car_shipping.png',
            'description' => 'احصل على نسبة عمولة مخفضة تصل إلى 5% فقط على جميع مبيعات الفحص المعتمد.',
            'button_text' => 'تفاصيل العرض',
            'items' => [
                [
                    'title' => 'عمولات مخفضة هذا الشهر للشركاء',
                    'description' => 'احصل على نسبة عمولة مخفضة تصل إلى 5% فقط على جميع مبيعات الفحص المعتمد.',
                    'button_text' => 'تفاصيل العرض',
                    'show_button' => '1',
                    'image' => 'images/car_shipping.png',
                ]
            ]
        ]);
    }
}
