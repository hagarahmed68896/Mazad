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
        $user = Auth::guard('sanctum')->user();
        $banners = Banner::where('status', true)->get();
        $categories = Category::whereNull('parent_id')->get();
        
        $data = [
            'banners' => $banners,
            'categories' => $categories,
        ];

        if ($user) {
            // --- AUTH USER CONTENT ---
            $interestIds = $user->interests()->pluck('categories.id')->toArray();
            $interestIdsStr = !empty($interestIds) ? implode(',', $interestIds) : null;

            // Summary & Chart Data
            $filter = $request->get('filter', 'weekly'); // weekly, monthly, yearly
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

            $data['user_summary'] = [
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

            // Recommended (Based on interests)
            if ($interestIdsStr) {
                $data['recommended_ads'] = Ad::with(['category', 'user', 'city'])
                    ->where('status', 'active')
                    ->whereIn('category_id', $interestIds)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            } else {
                $data['recommended_ads'] = [];
            }

            // My Auctions or Current Auctions Prioritized
            $data['active_auctions'] = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->whereIn('type', ['electronic_auction', 'hybrid_auction'])
                ->when($interestIdsStr, function($q) use ($interestIdsStr) {
                    $q->orderByRaw("CASE WHEN category_id IN ($interestIdsStr) THEN 0 ELSE 1 END");
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Recently Viewed (Fallback to general recent for now)
            $data['recently_viewed'] = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->limit(10)
                ->get();

            // Instant Buy (Prioritized)
            $data['instant_buy_ads'] = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->whereNotIn('type', ['electronic_auction', 'hybrid_auction'])
                ->when($interestIdsStr, function($q) use ($interestIdsStr) {
                    $q->orderByRaw("CASE WHEN category_id IN ($interestIdsStr) THEN 0 ELSE 1 END");
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

        } else {
            // --- GUEST CONTENT ---
            
            // Featured Ads
            $data['featured_ads'] = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Ending Soon
            $data['ending_soon'] = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->whereNotNull('end_date')
                ->where('end_date', '>', now())
                ->orderBy('end_date', 'asc')
                ->limit(10)
                ->get();

            // Trending / Interactive
            $data['trending_ads'] = Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->limit(10)
                ->get();
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
