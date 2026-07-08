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

class CompanyUserSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::pluck('id')->toArray();
        if (empty($categoryIds)) {
            $categories = ['السيارات', 'العقارات', 'الأجهزة'];
            foreach ($categories as $catName) {
                $c = Category::create([
                    'name' => $catName,
                    'ad_types' => ['electronic_auction', 'direct_purchase'],
                    'deposit_value' => 10,
                    'tax_value' => 15
                ]);
                $categoryIds[] = $c->id;
            }
        }

        $companyNames = [
            'شركة مزاد الخليج العقارية',
            'مؤسسة التقنيات الحديثة',
            'شركة تجارة السيارات العالمية',
            'شركة النماء للاستثمار',
            'مجموعة الفوزان للتجارة',
            'شركة الجودة للخدمات اللوجستية',
            'مؤسسة القمة للمقاولات',
            'شركة التميز للاتصالات',
            'شركة الأمل للصناعة',
            'مجموعة الحلول المتطورة'
        ];

        foreach ($companyNames as $index => $name) {
            $user = User::create([
                'name' => 'مسؤول ' . $name,
                'email' => 'company' . ($index + 1) . '_' . Str::random(4) . '@example.com',
                'password' => bcrypt('password'),
                'phone' => '056' . rand(1000000, 9999999),
                'address' => 'المملكة العربية السعودية، جدة',
                'account_type' => 'company',
                'company_name' => $name,
                'commercial_register' => rand(1000000000, 9999999999),
                'status' => 'active',
                'ads_count' => rand(10, 50),
                'sales' => rand(5, 30),
                'purchases' => rand(2, 10),
            ]);

            // Create some ads for the company
            for ($j = 0; $j < rand(3, 8); $j++) {
                $ad = Ad::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'title' => 'إعلان تجاري - ' . $name . ' رقم ' . ($j + 1),
                    'description' => 'هذا وصف لإعلان تجاري مقدم من شركة ' . $name,
                    'type' => ['electronic_auction', 'hybrid_auction', 'direct_purchase', 'tender'][rand(0, 3)],
                    'price' => rand(5000, 100000),
                    'status' => ['active', 'upcoming', 'finished'][rand(0, 2)],
                ]);

                // Create some orders and transactions for completeness
                if (rand(0, 1)) {
                    $buyer = User::where('id', '!=', $user->id)->inRandomOrder()->first();
                    if ($buyer) {
                        $order = Order::create([
                            'order_number' => 'ORD-C-' . strtoupper(Str::random(8)),
                            'ad_id' => $ad->id,
                            'buyer_id' => $buyer->id,
                            'seller_id' => $user->id,
                            'final_price' => $ad->price,
                            'status' => 'paid',
                            'created_at' => now()->subDays(rand(1, 30))
                        ]);

                        Transaction::create([
                            'transaction_number' => 'TRX-C-' . strtoupper(Str::random(10)),
                            'user_id' => $buyer->id,
                            'ad_id' => $ad->id,
                            'type' => 'payment',
                            'amount' => $ad->price,
                            'payment_method' => 'bank_transfer',
                            'status' => 'completed',
                            'created_at' => $order->created_at
                        ]);
                    }
                }
            }
        }
    }
}
