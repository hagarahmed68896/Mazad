<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('account_type', 'company')->first();
if ($user) {
    echo "اسم الشركة: " . $user->company_name . "\n";
    echo "البريد الإلكتروني: " . $user->email . "\n";
    echo "رقم الجوال: " . $user->phone . "\n";
    echo "كلمة المرور: password\n";
} else {
    // If no company user, let's create one fixed for testing!
    $user = App\Models\User::create([
        'name' => 'مسؤول شركة النخبة العقارية',
        'email' => 'company@mazad.com',
        'password' => bcrypt('password123'),
        'phone' => '0512345678',
        'address' => 'الرياض، المملكة العربية السعودية',
        'account_type' => 'company',
        'company_name' => 'شركة النخبة العقارية',
        'commercial_register' => '1010020202',
        'status' => 'active'
    ]);
    echo "تم إنشاء حساب تجريبي جديد للشركة:\n";
    echo "البريد الإلكتروني: " . $user->email . "\n";
    echo "رقم الجوال: " . $user->phone . "\n";
    echo "كلمة المرور: password123\n";
}
