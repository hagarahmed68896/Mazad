<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Review;
use Illuminate\Support\Str;

class IndividualUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure some categories exist
        $categories = ['السيارات', 'العقارات', 'الأجهزة'];
        foreach ($categories as $catName) {
            Category::firstOrCreate(['name' => $catName], [
                'ad_types' => ['electronic_auction', 'direct_purchase'],
                'deposit_value' => 10,
                'tax_value' => 15
            ]);
        }
        $categoryIds = Category::pluck('id')->toArray();

        // Create 20 Individual Users
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'name' => 'مستخدم فردي ' . $i,
                'email' => 'user' . $i . '_' . Str::random(4) . '@example.com',
                'password' => bcrypt('password'),
                'phone' => '05' . rand(10000000, 99999999),
                'address' => 'المملكة العربية السعودية، الرياض',
                'account_type' => 'individual',
                'status' => rand(0, 5) > 0 ? 'active' : 'banned',
            ]);

            // Add some activity stats
            $user->update([
                'ads_count' => rand(5, 20),
                'sales' => rand(2, 10),
                'purchases' => rand(3, 15),
            ]);
        }

        $allUsers = User::where('account_type', 'individual')->get();

        foreach ($allUsers as $user) {
            // For each user, create some ads
            for ($j = 0; $j < rand(1, 3); $j++) {
                $ad = Ad::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'title' => 'إعلان تجريبي رقم ' . rand(100, 999),
                    'description' => 'هذا وصف تجريبي للإعلان لعرضه في صفحة الملف الشخصي.',
                    'type' => rand(0, 1) ? 'electronic_auction' : 'direct_purchase',
                    'price' => rand(500, 10000),
                    'status' => ['active', 'upcoming', 'finished'][rand(0, 2)],
                ]);

                // Create an order for this ad
                $buyer = $allUsers->where('id', '!=', $user->id)->random();
                
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                    'ad_id' => $ad->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $user->id,
                    'final_price' => $ad->price,
                    'status' => 'paid',
                    'created_at' => now()->subDays(rand(1, 150))
                ]);

                // Transaction for the buyer
                Transaction::create([
                    'transaction_number' => 'TRX-' . strtoupper(Str::random(10)),
                    'user_id' => $buyer->id,
                    'ad_id' => $ad->id,
                    'type' => 'payment',
                    'amount' => $ad->price,
                    'payment_method' => 'apple_pay',
                    'status' => 'completed',
                    'created_at' => $order->created_at
                ]);

                // Review for the seller
                Review::create([
                    'reviewer_id' => $buyer->id,
                    'rated_user_id' => $user->id,
                    'order_id' => $order->id,
                    'rating' => rand(4, 5),
                    'comment' => 'تعامل ممتاز وسرعة في التجاوب، أنصح بالتعامل معه.',
                    'status' => 'positive',
                    'created_at' => $order->created_at->addDays(1)
                ]);
            }
        }
    }
}
