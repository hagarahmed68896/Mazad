<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\City;
use Illuminate\Database\Seeder;

class CityRegionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'الرياض' => ['حي النخيل', 'حي الياسمين', 'حي الملز', 'حي الصحافة', 'حي الملقا'],
            'جدة' => ['حي الشاطئ', 'حي الحمراء', 'حي الصفا', 'حي الروضة', 'حي السلامة'],
            'الدمام' => ['حي الفيصلية', 'حي الشاطئ الشرقي', 'حي المنتزه', 'حي الفنار'],
            'مكة المكرمة' => ['حي الشرائع', 'حي العوالي', 'حي الشوقية', 'حي بطحاء قريش'],
            'المدينة المنورة' => ['حي القبلتين', 'حي العزيزية', 'حي الرانوناء', 'حي الجرف'],
            'الخبر' => ['حي الحزام الذهبي', 'حي الراكة', 'حي القصور'],
            'أبها' => ['حي المنسك', 'حي المحالة', 'حي السد'],
            'تبوك' => ['حي المروج', 'حي الورود', 'حي العليا'],
        ];

        foreach ($data as $regionName => $cities) {
            $region = Region::firstOrCreate(['name' => $regionName]);
            foreach ($cities as $cityName) {
                City::firstOrCreate([
                    'region_id' => $region->id,
                    'name' => $cityName
                ]);
            }
        }
    }
}
