<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Notification;

$user = User::where('phone', '512345678')->first();
if ($user) {
    // Delete existing notifications first to have a clean slate
    Notification::where('user_id', $user->id)->delete();

    $notifs = [
        [
            'type' => 'alert',
            'title' => 'تنبيه أمان',
            'content' => 'تم رصد محاولة دخول لحسابك من جهاز جديد في منطقة الرياض. إذا لم تكن أنت، يرجى تغيير كلمة المرور فوراً.',
            'status' => 'sent',
            'read_at' => null,
            'created_at' => now()
        ],
        [
            'type' => 'notification',
            'title' => 'مزايدة جديدة على إعلانك',
            'content' => 'قام المزايد أحمد محمد بتقديم عرض قيمته 5,200 ريال على إعلانك "سيارة تويوتا كامري 2023".',
            'status' => 'sent',
            'read_at' => null,
            'created_at' => now()->subMinutes(15)
        ],
        [
            'type' => 'notification',
            'title' => 'تم تخطي مزايدتك',
            'content' => 'لقد قام مزايد آخر بتقديم عرض أعلى منك بقيمة 12,000 ريال على "ساعة رولكس ديت جست". يمكنك الدخول للمزايدة مرة أخرى.',
            'status' => 'sent',
            'read_at' => null,
            'created_at' => now()->subHour()
        ],
        [
            'type' => 'notification',
            'title' => 'طلب شراء مباشر جديد',
            'content' => 'مبروك! لقد قام المشتري خالد علي بشراء "جهاز آيفون 15 برو ماكس" الخاص بك عن طريق الشراء المباشر بنجاح.',
            'status' => 'sent',
            'read_at' => null,
            'created_at' => now()->subHours(3)
        ],
        [
            'type' => 'notification',
            'title' => 'إعلان جديد يهمك',
            'content' => 'تمت إضافة إعلان جديد في قسم العقارات يناسب اهتماماتك المفضلة: "فيلا سكنية للبيع حي الملقا".',
            'status' => 'sent',
            'read_at' => now(),
            'created_at' => now()->subHours(6)
        ],
        [
            'type' => 'alert',
            'title' => 'توثيق الحساب بنجاح',
            'content' => 'تهانينا! تمت الموافقة على طلب توثيق حسابك وأصبحت الآن معلناً معتمداً بشارة التوثيق الذهبية.',
            'status' => 'sent',
            'read_at' => now(),
            'created_at' => now()->subDays(1)
        ],
        [
            'type' => 'notification',
            'title' => 'تحويل مالي وارد',
            'content' => 'تم تحويل مبلغ عمولة المزاد رقم #4432 بنجاح إلى حسابك البنكي المرتبط بالمنصة.',
            'status' => 'sent',
            'read_at' => now(),
            'created_at' => now()->subDays(2)
        ]
    ];

    foreach ($notifs as $notif) {
        Notification::create(array_merge($notif, ['user_id' => $user->id]));
    }

    echo "Diverse notifications created successfully for user {$user->name} (Phone: 512345678).\n";
} else {
    echo "User with phone 512345678 not found.\n";
}
