<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::where('email', 'admin@mazad.com')->first();
        if (!$user) return;

        $notifications = [
            [
                'type' => 'notification',
                'title' => 'تذكرة بلاغ جديدة',
                'content' => 'قام أحمد محمد بإضافة بلاغ جديد بخصوص سوء استخدام في المزاد رقم #543',
                'status' => 'sent',
                'read_at' => null,
            ],
            [
                'type' => 'alert',
                'title' => 'تم قبول انضمام شريك',
                'content' => 'تمت الموافقة على انضمام "شركة النور" كشريك استراتيجي للمنصة',
                'status' => 'sent',
                'read_at' => now()->subHours(2),
            ],
            [
                'type' => 'notification',
                'title' => 'استفسار بحاجة لرد',
                'content' => 'هناك استفسار جديد من "سارة أحمد" بخصوص شروط المزايدات الدولية',
                'status' => 'sent',
                'read_at' => null,
            ],
            [
                'type' => 'alert',
                'title' => 'تنبيه أمان',
                'content' => 'تم رصد محاولة دخول من جهاز غير معروف في منطقة الرياض',
                'status' => 'sent',
                'read_at' => null,
            ],
            [
                'type' => 'notification',
                'title' => 'تحديث مالي',
                'content' => 'تم تحويل عمولة المزاد رقم #4432 بنجاح إلى حسابك البنكي',
                'status' => 'sent',
                'read_at' => now()->subDays(1),
            ],
            [
                'type' => 'notification',
                'title' => 'طلب سحب رصيد',
                'content' => 'المستخدم "خالد علي" قدم طلباً لسحب مبلغ 500 ريال من محفظته',
                'status' => 'sent',
                'read_at' => null,
            ],
            [
                'type' => 'alert',
                'title' => 'تحديث في السياسات',
                'content' => 'تم إجراء تعديلات على شروط الاستخدام لمزادات العقارات',
                'status' => 'sent',
                'read_at' => now()->subDays(2),
            ],
            [
                'type' => 'notification',
                'title' => 'رسالة إدارية',
                'content' => 'تذكير: موعد اجتماع مراجعة الأداء الأسبوعي غداً في تمام الـ 10 صباحاً',
                'status' => 'sent',
                'read_at' => null,
            ],
        ];

        foreach ($notifications as $notif) {
            \App\Models\Notification::create(array_merge($notif, ['user_id' => $user->id]));
        }
    }
}
