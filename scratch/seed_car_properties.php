<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CategoryPage;
use App\Models\Property;

$categoryId = 1; // السيارات

// 1. أرقام تعريفية
$p1 = CategoryPage::updateOrCreate(['category_id' => $categoryId, 'name' => 'أرقام تعريفية']);
Property::updateOrCreate(['category_page_id' => $p1->id, 'label' => 'رقم اللوحة'], ['category_id' => $categoryId, 'input_type' => 'text', 'placeholder' => 'أ ب ج 1 2 3']);
Property::updateOrCreate(['category_page_id' => $p1->id, 'label' => 'رقم الهيكل (VIN)'], ['category_id' => $categoryId, 'input_type' => 'text']);
Property::updateOrCreate(['category_page_id' => $p1->id, 'label' => 'الرقم التسلسلي'], ['category_id' => $categoryId, 'input_type' => 'text']);

// 2. تفاصيل السيارة
$p2 = CategoryPage::updateOrCreate(['category_id' => $categoryId, 'name' => 'تفاصيل السيارة']);
Property::updateOrCreate(['category_page_id' => $p2->id, 'label' => 'نوع السيارة'], ['category_id' => $categoryId, 'input_type' => 'select', 'options' => ['تويوتا', 'هيونداي', 'كيا', 'فورد', 'شيفروليه', 'نيسان']]);
Property::updateOrCreate(['category_page_id' => $p2->id, 'label' => 'سنة الصنع'], ['category_id' => $categoryId, 'input_type' => 'number', 'placeholder' => '2023']);
Property::updateOrCreate(['category_page_id' => $p2->id, 'label' => 'اللون'], ['category_id' => $categoryId, 'input_type' => 'text', 'placeholder' => 'أبيض']);
Property::updateOrCreate(['category_page_id' => $p2->id, 'label' => 'الكيلومترات (الممشى)'], ['category_id' => $categoryId, 'input_type' => 'number', 'placeholder' => '0']);

// 3. مواصفات السيارة
$p3 = CategoryPage::updateOrCreate(['category_id' => $categoryId, 'name' => 'مواصفات السيارة']);
Property::updateOrCreate(['category_page_id' => $p3->id, 'label' => 'نوع الوقود'], ['category_id' => $categoryId, 'input_type' => 'select', 'options' => ['بنزين', 'ديزل', 'هايبرد', 'كهرباء']]);
Property::updateOrCreate(['category_page_id' => $p3->id, 'label' => 'ناقل الحركة'], ['category_id' => $categoryId, 'input_type' => 'select', 'options' => ['أوتوماتيك', 'يدوي']]);
Property::updateOrCreate(['category_page_id' => $p3->id, 'label' => 'عدد السلندرات'], ['category_id' => $categoryId, 'input_type' => 'number', 'placeholder' => '4']);

// 4. حالة السيارة الميكانيكية
$p4 = CategoryPage::updateOrCreate(['category_id' => $categoryId, 'name' => 'حالة السيارة الميكانيكية']);
Property::updateOrCreate(['category_page_id' => $p4->id, 'label' => 'حالة المحرك'], ['category_id' => $categoryId, 'input_type' => 'radio', 'options' => ['ممتاز', 'جيد', 'يحتاج صيانة']]);
Property::updateOrCreate(['category_page_id' => $p4->id, 'label' => 'حالة القير'], ['category_id' => $categoryId, 'input_type' => 'radio', 'options' => ['ممتاز', 'جيد', 'يحتاج صيانة']]);
Property::updateOrCreate(['category_page_id' => $p4->id, 'label' => 'حالة المكيف'], ['category_id' => $categoryId, 'input_type' => 'radio', 'options' => ['بارد جداً', 'جيد', 'يحتاج فريون']]);

echo "Done populating car properties!";
