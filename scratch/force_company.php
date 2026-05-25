<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$phone = '511111111';
$email = 'company@mazad.com';
$password = 'password';

// Clean up any conflicting records
App\Models\User::where('phone', $phone)->orWhere('email', $email)->delete();

// Create new fresh clean account
$user = App\Models\User::create([
    'name' => 'مسؤول شركة مزاد الخليج العقارية المعتمدة',
    'email' => $email,
    'phone' => $phone,
    'password' => bcrypt($password),
    'address' => 'الرياض، المملكة العربية السعودية',
    'account_type' => 'company',
    'company_name' => 'شركة مزاد الخليج العقارية المعتمدة',
    'commercial_register' => '1010020202',
    'status' => 'active'
]);

echo "تم إعادة إنشاء حساب الشركة بنجاح مع رقم الجوال الخالي من الصفر:\n";
echo "رقم الجوال: " . $user->phone . "\n";
echo "البريد الإلكتروني: " . $user->email . "\n";
echo "كلمة المرور: password\n";
