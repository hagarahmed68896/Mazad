<?php

use App\Models\Category;
use App\Models\Property;
use App\Models\Ad;
use App\Models\AdProperty;

// 3. Setup Electronics Properties (Category ID: 7)
$electroCat = Category::find(7);
if ($electroCat) {
    echo "Seeding Electronics properties...\n";
    
    $props = [
        [
            'label' => 'الماركة',
            'input_type' => 'select',
            'options' => ['Apple', 'Samsung', 'Sony', 'LG', 'HP', 'Dell'],
            'placeholder' => 'اختر الماركة'
        ],
        [
            'label' => 'الحالة',
            'input_type' => 'radio',
            'options' => ['جديد', 'مستعمل', 'مجدد']
        ],
        [
            'label' => 'الضمان',
            'input_type' => 'select',
            'options' => ['بدون ضمان', 'سنة واحدة', 'سنتين', 'ضمان ممتد'],
            'placeholder' => 'اختر الضمان'
        ]
    ];

    foreach ($props as $p) {
        $property = Property::updateOrCreate(
            ['category_id' => $electroCat->id, 'label' => $p['label']],
            $p
        );

        // Assign to ads in this category
        $ads = Ad::where('category_id', $electroCat->id)->get();
        foreach ($ads as $ad) {
            AdProperty::updateOrCreate(
                ['ad_id' => $ad->id, 'property_id' => $property->id],
                ['value' => $p['options'][array_rand($p['options'])]]
            );
        }
    }
}

echo "Done seeding Electronics properties.\n";
