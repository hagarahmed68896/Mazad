<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\Property;
use App\Models\AdProperty;
use App\Models\Category;

class AdPreviewTimeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the "Preview Time" property for relevant categories
        $categories = Category::whereIn('name', ['العقارات', 'السيارات', 'عقارات'])->get();
        
        foreach ($categories as $category) {
            $property = Property::firstOrCreate(
                ['category_id' => $category->id, 'label' => 'وقت المعاينة'],
                [
                    'input_type' => 'text',
                    'placeholder' => 'مثال: يومياً من 4 م إلى 8 م',
                    'is_required' => false
                ]
            );

            // 2. Add random preview times to ads in this category
            $ads = Ad::where('category_id', $category->id)->get();
            
            $times = [
                'يومياً من الساعة 4 عصراً حتى 9 مساءً',
                'كل سبت واثنين من 10 صباحاً إلى 2 ظهراً',
                'بالتنسيق المسبق عبر الواتساب',
                'يوم الجمعة بعد الصلاة مباشرة',
                'متاح للمعاينة في أي وقت بالنهار'
            ];

            foreach ($ads as $ad) {
                AdProperty::updateOrCreate(
                    ['ad_id' => $ad->id, 'property_id' => $property->id],
                    ['value' => $times[array_rand($times)]]
                );
            }
        }
    }
}
