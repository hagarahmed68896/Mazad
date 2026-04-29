<?php

use App\Models\Category;
use App\Models\Property;
use App\Models\Ad;
use App\Models\AdProperty;
use Illuminate\Support\Facades\DB;

// 1. Setup Cars Properties (Category ID: 1)
$carCat = Category::find(1);
if ($carCat) {
    echo "Seeding Cars properties...\n";
    
    $props = [
        [
            'label' => 'اللون',
            'input_type' => 'select',
            'options' => ['أبيض', 'أسود', 'فضي', 'أحمر', 'أزرق'],
            'placeholder' => 'اختر اللون'
        ],
        [
            'label' => 'سنة الصنع',
            'input_type' => 'select',
            'options' => ['2020', '2021', '2022', '2023', '2024'],
            'placeholder' => 'اختر السنة'
        ],
        [
            'label' => 'حالة السيارة',
            'input_type' => 'radio',
            'options' => ['جديد', 'مستعمل']
        ]
    ];

    foreach ($props as $p) {
        $property = Property::updateOrCreate(
            ['category_id' => $carCat->id, 'label' => $p['label']],
            $p
        );

        // Assign to ads in this category
        $ads = Ad::where('category_id', $carCat->id)->get();
        foreach ($ads as $ad) {
            AdProperty::updateOrCreate(
                ['ad_id' => $ad->id, 'property_id' => $property->id],
                ['value' => $p['options'][array_rand($p['options'])]]
            );
        }
    }
}

// 2. Setup Real Estate Properties (Category ID: 2 & 6)
foreach ([2, 6] as $catId) {
    $estateCat = Category::find($catId);
    if ($estateCat) {
        echo "Seeding Real Estate properties for Category $catId...\n";
        
        $props = [
            [
                'label' => 'عدد الغرف',
                'input_type' => 'radio',
                'options' => ['1', '2', '3', '4', '5+']
            ],
            [
                'label' => 'عدد الأدوار',
                'input_type' => 'select',
                'options' => ['1', '2', '3'],
                'placeholder' => 'اختر عدد الأدوار'
            ],
            [
                'label' => 'مفروش',
                'input_type' => 'radio',
                'options' => ['نعم', 'لا']
            ]
        ];

        foreach ($props as $p) {
            $property = Property::updateOrCreate(
                ['category_id' => $estateCat->id, 'label' => $p['label']],
                $p
            );

            // Assign to ads in this category
            $ads = Ad::where('category_id', $estateCat->id)->get();
            foreach ($ads as $ad) {
                AdProperty::updateOrCreate(
                    ['ad_id' => $ad->id, 'property_id' => $property->id],
                    ['value' => $p['options'][array_rand($p['options'])]]
                );
            }
        }
    }
}

echo "Done seeding dynamic properties sample data.\n";
