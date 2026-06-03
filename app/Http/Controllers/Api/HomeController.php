<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Ad;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Manually resolve the Bearer Token from headers if Sanctum guard didn't boot it automatically
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            $token = $request->bearerToken();
            if ($token) {
                $model = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($model && $model->tokenable) {
                    $user = $model->tokenable;
                    Auth::setUser($user);
                }
            }
        }

        // 2. Fetch Banners (Available for both Auth & Guest)
        $banners = Banner::where('status', true)->get();
        $data = [
            'banners' => $banners,
        ];

        if ($user) {
            // --- AUTH USER CONTENT ONLY ---
            $interestIds = $user->interests()->pluck('categories.id')->toArray();
            $interestIdsStr = !empty($interestIds) ? implode(',', $interestIds) : null;

            // Helper for prioritized query
            $getPrioritized = function($query, $limit = 10) use ($user, $interestIdsStr) {
                $blockedUserIds = \App\Models\BlockedUser::where('user_id', $user->id)->pluck('blocked_user_id')
                    ->merge(\App\Models\BlockedUser::where('blocked_user_id', $user->id)->pluck('user_id'))
                    ->toArray();
                $query->whereNotIn('user_id', $blockedUserIds)
                      ->where('user_id', '!=', $user->id);

                if ($interestIdsStr) {
                    return $query->orderByRaw("CASE WHEN category_id IN ($interestIdsStr) THEN 0 ELSE 1 END")
                                 ->orderBy('created_at', 'desc')
                                 ->limit($limit)
                                 ->get();
                }
                return $query->orderBy('created_at', 'desc')->limit($limit)->get();
            };

            // 1. Reports / Summary (تقاريري)
            $filter = $request->get('filter', 'weekly');
            $chartData = [];
            $totalRevenue = 0;
            $trend = "+0.00%"; // Placeholder for now

            if ($filter === 'yearly') {
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $sum = $user->sales()->where('status', 'delivered')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('final_price');
                    $chartData[] = ['label' => $month->locale('ar')->monthName, 'value' => (float)$sum];
                    $totalRevenue += $sum;
                }
            } elseif ($filter === 'monthly') {
                for ($i = 3; $i >= 0; $i--) {
                    $weekStart = now()->subWeeks($i)->startOfWeek();
                    $weekEnd = (clone $weekStart)->endOfWeek();
                    $sum = $user->sales()->where('status', 'delivered')->whereBetween('created_at', [$weekStart, $weekEnd])->sum('final_price');
                    $chartData[] = ['label' => "الأسبوع " . (4 - $i), 'value' => (float)$sum];
                    $totalRevenue += $sum;
                }
            } else { // weekly
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $sum = $user->sales()->where('status', 'delivered')->whereDate('created_at', $day->toDateString())->sum('final_price');
                    $chartData[] = ['label' => $day->locale('ar')->dayName, 'value' => (float)$sum];
                    $totalRevenue += $sum;
                }
            }

            $userSummary = [
                'total_revenue' => (float)$user->sales()->where('status', 'delivered')->sum('final_price'),
                'active_ads_count' => $user->ads()->where('status', 'active')->count(),
                'unread_notifications' => $user->unreadNotifications()->count(),
                'report_chart' => [
                    'current_filter' => $filter,
                    'filter_revenue' => $totalRevenue,
                    'trend' => $trend,
                    'points' => $chartData
                ]
            ];

            // 2. Recommended Ads (الموصى بها)
            $recommendedAds = [];
            if ($interestIdsStr) {
                $blockedUserIds = \App\Models\BlockedUser::where('user_id', $user->id)->pluck('blocked_user_id')
                    ->merge(\App\Models\BlockedUser::where('blocked_user_id', $user->id)->pluck('user_id'))
                    ->toArray();

                $recommendedAds = Ad::with(['category', 'user', 'city'])
                    ->where('status', 'active')
                    ->whereIn('category_id', $interestIds)
                    ->whereNotIn('user_id', $blockedUserIds)
                    ->where('user_id', '!=', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            // 3. Current Auctions (المزادات الحالية)
            $currentAuctions = $getPrioritized(
                Ad::with(['category', 'user', 'city'])
                    ->where('status', 'active')
                    ->whereIn('type', ['electronic_auction', 'hybrid_auction'])
                    ->where('end_date', '>', now()),
                10
            );

            // 4. Recently Viewed / Most Viewed (الأكثر مشاهدة مؤخراً)
            $recentlyViewed = $getPrioritized(
                Ad::with(['category', 'user', 'city'])
                    ->where('status', 'active')
                    ->orderBy('views_count', 'desc'),
                10
            );

            // 5. Instant Buy (عروض الشراء الفوري)
            $instantBuy = $getPrioritized(
                Ad::with(['category', 'user', 'city'])
                    ->where('status', 'active')
                    ->whereNotIn('type', ['electronic_auction', 'hybrid_auction']),
                10
            );

            $data['my_reports'] = $userSummary;
            $data['recommended_ads'] = $recommendedAds;
            $data['current_auctions'] = $currentAuctions;
            $data['recently_viewed'] = $recentlyViewed;
            $data['instant_buy'] = $instantBuy;

        } else {
            // --- GUEST CONTENT ONLY ---
            
            // 1. Featured Ads (أبرز الإعلانات)
            $featuredAds = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // 2. Ending Soon (الإعلانات قرب الانتهاء)
            $endingSoon = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->whereNotNull('end_date')
                ->where('end_date', '>', now())
                ->orderBy('end_date', 'asc')
                ->limit(10)
                ->get();

            // 3. Trending / Interactive (الإعلانات الأكثر تفاعل)
            $trendingAds = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->limit(10)
                ->get();

            $data['featured_ads'] = $featuredAds;
            $data['ending_soon'] = $endingSoon;
            $data['trending_ads'] = $trendingAds;
        }

        return response()->json([
            'success' => true,
            'message' => $user ? 'الصفحة الرئيسية (مخصصة)' : 'الصفحة الرئيسية (عامة)',
            'is_auth' => (bool)$user,
            'data' => $data
        ]);
    }

    public function categories()
    {
        $categories = Category::whereNull('parent_id')->get();
        return response()->json([
            'success' => true,
            'message' => 'قائمة التصنيفات',
            'data' => $categories
        ]);
    }
}
