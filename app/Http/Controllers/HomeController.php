<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Ad;
use App\Models\Setting;
use App\Models\City;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->email === 'admin@mazad.com') {
            return redirect()->route('admin.dashboard');
        }
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

    public function search(Request $request)
    {
        $query = Ad::with(['category', 'user', 'city']);

        // Keywords
        if ($request->has('q') && !empty($request->q)) {
            // Smart Redirect: If keyword partially matches a category name (e.g. "الكترو" -> "الكترونيات")
            $matchedCat = Category::where('name', 'like', '%' . $request->q . '%')->first();
            if ($matchedCat && !$request->has('category') && strlen($request->q) >= 3) {
                $params = $request->all();
                $params['category'] = $matchedCat->id;
                return redirect()->route('search', $params);
            }

            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%')
                  ->orWhereHas('category', function($cq) use ($request) {
                      $cq->where('name', 'like', '%' . $request->q . '%');
                  })
                  ->orWhereHas('city', function($cq) use ($request) {
                      $cq->where('name', 'like', '%' . $request->q . '%');
                  });
            });
        }

        // Category
        $activeCategory = null;
        if ($request->has('category') && !empty($request->category)) {
            $activeCategory = Category::with('properties')->find($request->category);
            $query->where('category_id', $request->category);
        }

        // Dynamic Properties Filter (e.g. props[7]=Red)
        if ($request->has('props') && is_array($request->props)) {
            foreach ($request->props as $propId => $value) {
                if (!empty($value)) {
                    $query->whereHas('adProperties', function($pq) use ($propId, $value) {
                        $pq->where('property_id', $propId);
                        if (is_array($value)) {
                            $pq->whereIn('value', $value);
                        } else {
                            $pq->where('value', $value);
                        }
                    });
                }
            }
        }

        // City
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city_id', $request->city);
        }

        // Type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Status filter (active, expired, upcoming) - Default to 'active'
        $adStatus = $request->get('ad_status', 'active');
        $query->where('status', $adStatus);

        // Advertiser Filter (User Type)
        if ($request->has('user_type') && !empty($request->user_type)) {
            $query->whereHas('user', function($uq) use ($request) {
                $uq->where('account_type', $request->user_type);
            });
        }

        // Proximity Filter (Nearby)
        $userLat = Auth::check() ? Auth::user()->latitude : session('user_latitude');
        $userLng = Auth::check() ? Auth::user()->longitude : session('user_longitude');

        if ($userLat && $userLng) {
            $radius = 50; // 50km radius
            $query->selectRaw("ads.*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$userLat, $userLng, $userLat])
                  ->whereNotNull('lat')
                  ->whereNotNull('lng')
                  ->having('distance', '<=', $radius);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') $query->orderBy('price', 'desc');
        elseif ($sort === 'price_low') $query->orderBy('price', 'asc');
        elseif ($sort === 'ending_soon') $query->orderBy('end_date', 'asc')->whereNotNull('end_date')->where('end_date', '>', now());
        else $query->orderBy('created_at', 'desc');

        $ads = $query->paginate(12)->appends($request->all());
        
        $categories = Category::whereNull('parent_id')->with('subCategories')->get();
        $cities = City::all();
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('search', compact('ads', 'categories', 'cities', 'settings', 'activeCategory'));
    }
}
