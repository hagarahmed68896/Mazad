<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\User;
use App\Models\AdQuestion;

class AdQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $ads = Ad::all();
        $users = User::where('account_type', 'individual')->limit(10)->get();

        if ($ads->isEmpty() || $users->isEmpty()) return;

        foreach ($ads as $ad) {
            // Add 1-2 questions per ad
            for ($i = 0; $i < rand(1, 2); $i++) {
                $user = $users->random();
                
                $qa = [
                    ['q' => 'هل العقار يقبل البنك؟', 'a' => 'نعم يقبل البنك وجميع الأوراق جاهزة.'],
                    ['q' => 'كم حدك للصامل؟', 'a' => 'السعر واضح في الإعلان وقابل للتفاوض البسيط.'],
                    ['q' => 'هل السيارة بها رش أو صدمات؟', 'a' => 'البدي وكالة ولله الحمد، الفحص خير برهان.'],
                    ['q' => 'هل الموقع دقيق؟', 'a' => 'نعم الموقع دقيق ويمكنك التنسيق للمعاينة.'],
                    ['q' => 'هل يوجد ضمان على المنتج؟', 'a' => null],
                ];

                $pick = $qa[array_rand($qa)];

                AdQuestion::create([
                    'ad_id' => $ad->id,
                    'user_id' => $user->id,
                    'question' => $pick['q'],
                    'answer' => $pick['a'],
                    'answered_at' => $pick['a'] ? now()->subHours(rand(1, 24)) : null,
                    'created_at' => now()->subDays(rand(1, 5))
                ]);
            }
        }
    }
}
