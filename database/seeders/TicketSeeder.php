<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketLog;

class TicketSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        // Delete existing data to start fresh
        TicketLog::query()->delete();
        Ticket::query()->delete();

        for ($i = 1; $i <= 15; $i++) {
            $type = rand(0, 1) ? 'report' : 'inquiry';
            
            // Logic: Inquiries are either resolved or under_review. Reports can also be rejected.
            if ($type == 'inquiry') {
                $status = rand(0, 1) ? 'resolved' : 'under_review';
            } else {
                $status = ['resolved', 'under_review', 'rejected'][rand(0, 2)];
            }

            $ticket = Ticket::create([
                'ticket_number' => 'TICK-' . rand(1000, 9999),
                'type' => $type,
                'user_id' => $users->random()->id,
                'phone' => '96601234567' . rand(0,9),
                'reported_about' => ($type == 'report') ? 'مستخدم ' . rand(1, 50) : null,
                'description' => 'لوريم ايبسوم دولار سيت آميت، كونيسيكتيتور أديبيسيسينغ إيليت. فبغيرا كونديمنتووم إيجيت بوروس إن. كونيسيكتيتور إيجيت إيد موري آميت آميت، إن. إيسوم فيغيرا بريتيوم تيلوس نيكوي. أولامكوربر سوسبينديسي آتيان ليو فاريتا إن سيت سمبر إت.',
                'status' => $status,
                'created_at' => now()->subDays(rand(0, 15)),
            ]);

            // Add some logs for resolved tickets to show the history UI
            if ($status == 'resolved') {
                if ($type == 'inquiry') {
                    TicketLog::create([
                        'ticket_id' => $ticket->id,
                        'action' => 'reply',
                        'note' => 'تم الرد على استفساركم: نحن بصدد مراجعة طلبكم وسيتم التواصل معكم قريباً.',
                        'created_at' => $ticket->created_at->addHours(2)
                    ]);
                } else {
                    $action = rand(0, 1) ? 'warning' : 'suspension';
                    TicketLog::create([
                        'ticket_id' => $ticket->id,
                        'action' => $action,
                        'note' => $action == 'warning' ? 'تم إرسال تنبيه رسمي للمستخدم بمخالفة السياسات.' : 'تم إيقاف الحساب لمدة 3 أيام للمراجعة.',
                        'created_at' => $ticket->created_at->addHours(2)
                    ]);
                }
            }
        }
    }
}
