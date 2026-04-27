<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Ad;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('status', true)->get();
        $discoveryCategories = Category::whereNull('parent_id')->get();
        
        // 1. Featured Ads (أبرز الإعلانات)
        $featuredAds = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();
            
        // 2. Interactive Ads (الإعلانات الأكثر تفاعل)
        $interactiveAds = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('views_count', 'desc')
            ->orderBy('bids_count', 'desc')
            ->limit(12)
            ->get();
            
        // 3. Ending Soon (الإعلانات قرب الانتهاء)
        $endingSoon = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->orderBy('end_date', 'asc') 
            ->limit(12)
            ->get();

        // 4. Recently Viewed (الأكثر مشاهدة مؤخراً)
        $recentlyViewed = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('views_count', 'desc')
            ->limit(12)
            ->get();

        // 5. Current Auctions (المزادات الحالية)
        $currentAuctions = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->where('type', 'auction')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        // 6. Instant Buy (عروض الشراء الفوري)
        $instantBuy = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->where('type', '!=', 'auction')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        $totalRevenue = 0;
        $chartData = [];
        if (auth()->check()) {
            $user = auth()->user();
            $totalRevenue = $user->sales()
                ->where('status', 'delivered') // Assuming 'delivered' means completed based on typical Mazad status
                ->sum('final_price');
            
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $chartData[] = [
                    'day' => $day->locale('ar')->dayName,
                    'total' => $user->sales()
                        ->where('status', 'delivered')
                        ->whereDate('created_at', $day->toDateString())
                        ->sum('final_price')
                ];
            }
        }

        $settings = Setting::pluck('value', 'key')->toArray();

        return view('welcome', compact(
            'banners', 'discoveryCategories', 'featuredAds', 
            'interactiveAds', 'endingSoon', 'recentlyViewed', 
            'currentAuctions', 'instantBuy', 'settings',
            'totalRevenue', 'chartData'
        ));
    }
}
