<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request, $adId)
    {
        $ad = Ad::findOrFail($adId);

        // Prevent buying own ad
        if ($ad->user_id === Auth::id()) {
            return back()->with('error', 'لا يمكنك شراء إعلانك الخاص.');
        }

        // Prevent duplicate orders for the same ad
        $existing = Order::where('ad_id', $ad->id)
            ->where('buyer_id', Auth::id())
            ->whereIn('status', ['pending_payment', 'receiving', 'received', 'paid'])
            ->first();

        if ($existing) {
            return back()->with('error', 'لقد قمت بشراء هذا الإعلان مسبقاً.');
        }

        // Prevent buying a finished ad
        if ($ad->status === 'finished') {
            return back()->with('error', 'هذا الإعلان لم يعد متاحاً للشراء.');
        }

        // Calculate fees (same logic as show.blade)
        $comm_rate = (float) (\App\Models\Setting::where('key', 'commission_rate')->first()->value ?? 2.5);
        if ($comm_rate > 50) $comm_rate = 2.5;
        $vat_rate = 15;
        $admin_fees = (float) (\App\Models\Setting::where('key', 'admin_fees')->first()->value ?? 19);
        $transfer_fees = (float) (\App\Models\Setting::where('key', 'transfer_fees')->first()->value ?? 500);

        $saai_val = $ad->price * ($comm_rate / 100);
        $vat_val = $saai_val * ($vat_rate / 100);
        
        // Total price for direct purchase matches the UI modal (Price + Admin Fees + Transfer Fees)
        $total_price = $ad->price + $admin_fees + $transfer_fees;

        // Create the order
        $order = Order::create([
            'order_number' => strtoupper(Str::random(8)),
            'ad_id'        => $ad->id,
            'buyer_id'     => Auth::id(),
            'seller_id'    => $ad->user_id,
            'final_price'  => round($total_price),
            'status'       => 'pending_payment',
        ]);

        // Log a transaction for tracking
        Transaction::create([
            'transaction_number' => 'TXN-' . strtoupper(Str::random(8)),
            'user_id'            => Auth::id(),
            'ad_id'              => $ad->id,
            'type'               => 'purchase',
            'amount'             => round($total_price),
            'payment_method'     => $request->payment_method ?? 'bank_transfer',
            'status'             => 'under_review',
        ]);

        // Mark ad as finished so no one else can buy it
        $ad->update(['status' => 'finished']);

        return back()->with('success', 'تم تقديم طلب الشراء بنجاح! رقم الطلب: #' . $order->order_number . '. سيتم التواصل معك قريباً لإتمام الدفع.');
    }

    public function mySales(Request $request)
    {
        $status = $request->query('status', 'all'); // 'all', 'sold', 'not_sold'
        $user = Auth::user();
        
        $query = Ad::where('user_id', $user->id)->with(['orders.buyer', 'category', 'city']);
        
        // 1. Tab Status Filter
        if ($status === 'sold') {
            $query->whereHas('orders');
        } elseif ($status === 'not_sold') {
            $query->whereDoesntHave('orders');
        }
        
        // 2. Search Keyword Filter
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        
        // 3. City Filter
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city_id', $request->city);
        }
        
        // 4. Category Filter
        $activeCategory = null;
        if ($request->has('category') && !empty($request->category)) {
            $activeCategory = \App\Models\Category::with('properties')->find($request->category);
            $query->where('category_id', $request->category);
        }

        // 4.1. Dynamic Category Properties Filter
        if ($activeCategory && $request->has('props') && is_array($request->props)) {
            foreach ($request->props as $propId => $val) {
                if ($val !== null && $val !== '') {
                    $query->whereHas('adProperties', function($q) use ($propId, $val) {
                        $q->where('property_id', $propId);
                        if (is_array($val)) {
                            $q->whereIn('value', $val);
                        } else {
                            $q->where('value', $val);
                        }
                    });
                }
            }
        }
        
        // 5. Ad Type Filter
        if ($request->has('type') && !empty($request->type)) {
            if ($request->type === 'auction') {
                $query->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } elseif ($request->type === 'direct_purchase') {
                $query->where('type', 'direct_purchase');
            } else {
                $query->where('type', $request->type);
            }
        }
        
        // 6. Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $ads = $query->paginate(24)->appends($request->query());
        
        $cities = \App\Models\City::orderBy('name')->get();
        $categories = \App\Models\Category::whereNull('parent_id')->with('subCategories')->get();
        
        return view('ads.my-sales', compact('ads', 'status', 'cities', 'categories', 'activeCategory'));
    }

    public function myPurchases(Request $request)
    {
        $status = $request->query('status', 'all'); // 'all', 'auctions', 'direct_purchase', 'services'
        $user = Auth::user();
        
        $query = Order::where('buyer_id', $user->id)->with(['ad.category', 'ad.city', 'seller']);
        
        // 1. Tab Status Filter based on Type / Category
        if ($status === 'auctions') {
            $query->whereHas('ad', function($q) {
                $q->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            });
        } elseif ($status === 'direct_purchase') {
            $query->whereHas('ad', function($q) {
                $q->where('type', 'direct_purchase');
            });
        } elseif ($status === 'services') {
            $query->whereHas('ad', function($q) {
                $q->where('type', 'service')
                  ->orWhereHas('category', function($cq) {
                      $cq->where('name', 'like', '%خدم%');
                  });
            });
        }
        
        // 2. Search Keyword Filter
        if ($request->has('q') && !empty($request->q)) {
            $query->whereHas('ad', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        
        // 3. City Filter
        if ($request->has('city') && !empty($request->city)) {
            $query->whereHas('ad', function($q) use ($request) {
                $q->where('city_id', $request->city);
            });
        }
        
        // 4. Category Filter
        $activeCategory = null;
        if ($request->has('category') && !empty($request->category)) {
            $activeCategory = \App\Models\Category::with('properties')->find($request->category);
            $query->whereHas('ad', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // 4.1. Dynamic Category Properties Filter
        if ($activeCategory && $request->has('props') && is_array($request->props)) {
            foreach ($request->props as $propId => $val) {
                if ($val !== null && $val !== '') {
                    $query->whereHas('ad.adProperties', function($q) use ($propId, $val) {
                        $q->where('property_id', $propId);
                        if (is_array($val)) {
                            $q->whereIn('value', $val);
                        } else {
                            $q->where('value', $val);
                        }
                    });
                }
            }
        }
        
        // 5. Ad Type Filter
        if ($request->has('type') && !empty($request->type)) {
            $query->whereHas('ad', function($q) use ($request) {
                if ($request->type === 'auction') {
                    $q->whereIn('type', ['electronic_auction', 'hybrid_auction']);
                } elseif ($request->type === 'direct_purchase') {
                    $q->where('type', 'direct_purchase');
                } else {
                    $q->where('type', $request->type);
                }
            });
        }
        
        // 6. Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('final_price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('final_price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $orders = $query->paginate(24)->appends($request->query());
        
        $cities = \App\Models\City::orderBy('name')->get();
        $categories = \App\Models\Category::whereNull('parent_id')->with('subCategories')->get();
        
        return view('ads.my-purchases', compact('orders', 'status', 'cities', 'categories', 'activeCategory'));
    }

    public function confirmDelivery(Request $request, Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'delivery_code' => 'required|string|size:6'
        ]);
        
        if ($order->delivery_code !== $request->delivery_code) {
            return back()->with('error', 'كود التسليم المدخل غير صحيح. يرجى التحقق من المشتري.');
        }
        
        $order->update(['status' => 'received']);
        
        return back()->with('success', 'تم توثيق استلام الطلب بنجاح! 🎉');
    }

    public function seedMySales()
    {
        $seller = Auth::user();
        if (!$seller) {
            return back()->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        // Find or create buyer
        $buyer = \App\Models\User::where('email', 'buyer5123@mazad.com')->first();
        if (!$buyer) {
            $buyer = \App\Models\User::create([
                'name' => 'أحمد المشتري',
                'email' => 'buyer5123@mazad.com',
                'phone' => '0599999999',
                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                'account_type' => 'individual',
                'status' => 'active',
                'city_id' => \App\Models\City::first()->id ?? null,
            ]);
        }

        $categories = \App\Models\Category::whereNotNull('parent_id')->get();
        if ($categories->isEmpty()) {
            $categories = \App\Models\Category::all();
        }
        $cities = \App\Models\City::all();
        
        // Get existing images from database
        $images = \App\Models\Ad::whereNotNull('image')->limit(12)->pluck('image')->toArray();

        // Clean up existing ads of this seller to avoid cluttering
        $seller->ads()->delete();

        $paidItems = [
            ['title' => 'لابتوب ديل XPS 15 شاشة تعمل باللمس', 'price' => 33000, 'type' => 'direct_purchase'],
            ['title' => 'كاميرا سوني Alpha 7 IV الاحترافية', 'price' => 9500, 'type' => 'direct_purchase'],
            ['title' => 'ساعة آبل الترا الإصدار الثاني رياضية', 'price' => 3200, 'type' => 'direct_purchase'],
            ['title' => 'سماعات دي جي بوس اللاسلكية عازلة للضوضاء', 'price' => 1500, 'type' => 'direct_purchase']
        ];

        $pendingPaymentItems = [
            ['title' => 'برج سكني وتجاري استثماري في الرياض', 'price' => 7000000, 'type' => 'electronic_auction'],
            ['title' => 'شقة فاخرة إطلالة بحرية كاملة في جدة', 'price' => 850000, 'type' => 'electronic_auction'],
            ['title' => 'أرض تجارية على طريق الملك فهد بالرياض', 'price' => 12000000, 'type' => 'electronic_auction'],
            ['title' => 'مجمع سكني مغلق حديث شمال العاصمة', 'price' => 25000000, 'type' => 'electronic_auction']
        ];

        $deliveringItems = [
            ['title' => 'لابتوب لينوفو ثينك باد X1 كاربون', 'price' => 10000, 'type' => 'direct_purchase'],
            ['title' => 'جهاز آيباد برو شريحة M2 شاشة 12.9', 'price' => 5400, 'type' => 'direct_purchase'],
            ['title' => 'نظام صوتي منزلي متكامل من ياماها', 'price' => 3800, 'type' => 'direct_purchase'],
            ['title' => 'آلة قهوة بريفيل باريستا إكسبريس الاحترافية', 'price' => 2900, 'type' => 'direct_purchase']
        ];

        $receivedItems = [
            ['title' => 'سيارة بي إم دبليو M4 كوبيه رياضية', 'price' => 2800000, 'type' => 'hybrid_auction'],
            ['title' => 'سيارة مرسيدس C200 موديل 2024 فل كامل', 'price' => 240000, 'type' => 'hybrid_auction'],
            ['title' => 'دراجة نارية رياضية ياماها R6 ياباني', 'price' => 45000, 'type' => 'hybrid_auction'],
            ['title' => 'سيارة تسلا موديل 3 فل كامل كهربائية', 'price' => 180000, 'type' => 'hybrid_auction']
        ];

        $expiredItems = [
            ['title' => 'فيلا سكنية مودرن مع مسبح خاص بحطين', 'price' => 5000000, 'type' => 'electronic_auction'],
            ['title' => 'شاليه فاخر حي الرمال مساحات واسعة', 'price' => 1200000, 'type' => 'electronic_auction'],
            ['title' => 'استراحة خاصة مجهزة بالكامل بالدرعية', 'price' => 3500000, 'type' => 'electronic_auction'],
            ['title' => 'مزرعة منتجة بوسط القصيم مساحة كبرى', 'price' => 8000000, 'type' => 'electronic_auction']
        ];

        $activeItems = [
            ['title' => 'هاتف آيفون 15 برو ماكس 512 جيجا', 'price' => 4500, 'type' => 'direct_purchase'],
            ['title' => 'شاشة إل جي OLED 65 بوصة ذكية 4K', 'price' => 6200, 'type' => 'direct_purchase'],
            ['title' => 'منصة ألعاب بلايستيشن 5 مع ذراعين ولعبة', 'price' => 2100, 'type' => 'direct_purchase'],
            ['title' => 'نظارة أبل فيجن برو للواقع المختلط الجديدة', 'price' => 14500, 'type' => 'direct_purchase']
        ];

        $adIndex = 0;

        // Create 4 Paid Ads & Orders
        foreach ($paidItems as $index => $item) {
            $img = $images[$adIndex % count($images)] ?? null;
            $adIndex++;
            $ad = \App\Models\Ad::create([
                'user_id' => $seller->id,
                'category_id' => $categories->random()->id ?? null,
                'city_id' => $cities->random()->id ?? null,
                'title' => $item['title'],
                'type' => $item['type'],
                'price' => $item['price'],
                'status' => 'finished',
                'image' => $img,
                'end_date' => now()->subDays($index + 1),
            ]);
            \App\Models\Order::create([
                'order_number' => 'ORD-PAID-' . ($index + 1),
                'ad_id' => $ad->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'final_price' => $item['price'] + 519,
                'status' => 'paid',
            ]);
        }

        // Create 4 Pending Payment Ads & Orders
        foreach ($pendingPaymentItems as $index => $item) {
            $img = $images[$adIndex % count($images)] ?? null;
            $adIndex++;
            $ad = \App\Models\Ad::create([
                'user_id' => $seller->id,
                'category_id' => $categories->random()->id ?? null,
                'city_id' => $cities->random()->id ?? null,
                'title' => $item['title'],
                'type' => $item['type'],
                'price' => $item['price'],
                'status' => 'finished',
                'image' => $img,
                'end_date' => now()->subDays($index + 1),
            ]);
            \App\Models\Order::create([
                'order_number' => 'ORD-PEND-' . ($index + 1),
                'ad_id' => $ad->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'final_price' => $item['price'] + 519,
                'status' => 'pending_payment',
            ]);
        }

        // Create 4 Delivering Ads & Orders (Confirm Delivery)
        foreach ($deliveringItems as $index => $item) {
            $img = $images[$adIndex % count($images)] ?? null;
            $adIndex++;
            $ad = \App\Models\Ad::create([
                'user_id' => $seller->id,
                'category_id' => $categories->random()->id ?? null,
                'city_id' => $cities->random()->id ?? null,
                'title' => $item['title'],
                'type' => $item['type'],
                'price' => $item['price'],
                'status' => 'finished',
                'image' => $img,
                'end_date' => now()->subDays($index + 1),
            ]);
            \App\Models\Order::create([
                'order_number' => 'ORD-RECV-' . ($index + 1),
                'ad_id' => $ad->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'final_price' => $item['price'] + 519,
                'status' => 'receiving',
                'delivery_code' => '123456',
            ]);
        }

        // Create 4 Received Ads & Orders
        foreach ($receivedItems as $index => $item) {
            $img = $images[$adIndex % count($images)] ?? null;
            $adIndex++;
            $ad = \App\Models\Ad::create([
                'user_id' => $seller->id,
                'category_id' => $categories->random()->id ?? null,
                'city_id' => $cities->random()->id ?? null,
                'title' => $item['title'],
                'type' => $item['type'],
                'price' => $item['price'],
                'status' => 'finished',
                'image' => $img,
                'end_date' => now()->subDays($index + 1),
            ]);
            \App\Models\Order::create([
                'order_number' => 'ORD-RCVD-' . ($index + 1),
                'ad_id' => $ad->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'final_price' => $item['price'] + 519,
                'status' => 'received',
            ]);
        }

        // Create 4 Expired Unsold Ads (منتهي - إعادة بيع / تعديل)
        foreach ($expiredItems as $index => $item) {
            $img = $images[$adIndex % count($images)] ?? null;
            $adIndex++;
            \App\Models\Ad::create([
                'user_id' => $seller->id,
                'category_id' => $categories->random()->id ?? null,
                'city_id' => $cities->random()->id ?? null,
                'title' => $item['title'],
                'type' => $item['type'],
                'price' => $item['price'],
                'status' => 'finished',
                'image' => $img,
                'end_date' => now()->subDays($index + 2),
            ]);
        }

        // Create 4 Active Unsold Ads (قائم - تعديل)
        foreach ($activeItems as $index => $item) {
            $img = $images[$adIndex % count($images)] ?? null;
            $adIndex++;
            \App\Models\Ad::create([
                'user_id' => $seller->id,
                'category_id' => $categories->random()->id ?? null,
                'city_id' => $cities->random()->id ?? null,
                'title' => $item['title'],
                'type' => $item['type'],
                'price' => $item['price'],
                'status' => 'active',
                'image' => $img,
                'end_date' => now()->addDays($index + 3),
            ]);
        }

        return back()->with('success', 'تم توليد 24 كارت مبيعات متميز بنجاح! 🎉 يمكنك الآن تجربة الفلترة والتنقل بين التبويبات ومشاهدة الأمثلة.');
    }

    public function bulkMarkSold(Request $request)
    {
        $request->validate([
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'required|integer|exists:ads,id',
        ]);

        $seller = Auth::user();
        if (!$seller) {
            return back()->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        // Find or create buyer
        $buyer = \App\Models\User::where('email', 'buyer5123@mazad.com')->first();
        if (!$buyer) {
            $buyer = \App\Models\User::create([
                'name' => 'أحمد المشتري',
                'email' => 'buyer5123@mazad.com',
                'phone' => '0599999999',
                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                'account_type' => 'individual',
                'status' => 'active',
                'city_id' => \App\Models\City::first()->id ?? null,
            ]);
        }

        $successCount = 0;
        foreach ($request->ad_ids as $adId) {
            $ad = \App\Models\Ad::where('id', $adId)->where('user_id', $seller->id)->first();
            
            // Check that the ad belongs to this user and has NO orders yet
            if ($ad && !$ad->orders()->exists()) {
                // Create a mock order with status 'paid' as requested for "تم البيع" cards
                \App\Models\Order::create([
                    'order_number' => 'ORD-BULK-' . $ad->id . '-' . rand(100, 999),
                    'ad_id' => $ad->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                    'final_price' => $ad->price,
                    'status' => 'paid',
                ]);

                // Update ad status to finished so it is sold
                $ad->update(['status' => 'finished']);
                $successCount++;
            }
        }

        if ($successCount > 0) {
            return redirect()->route('sales.my', ['status' => 'sold'])->with('success', "تم إتمام عملية البيع الجماعي بنجاح لـ {$successCount} إعلانات! 🎉");
        }

        return back()->with('error', 'لم يتم تحديد أي إعلانات صالحة لإتمام البيع الجماعي.');
    }
}
