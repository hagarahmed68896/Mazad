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

        // Auto-activate upcoming ads whose start date has arrived
        Ad::where('status', 'upcoming')
            ->where(function($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->update(['status' => 'active']);

        $banners = Banner::where('status', true)->get();
        $discoveryCategories = Category::whereNull('parent_id')->get();
        
        // Handle User Interests for personalization (Web)
        $interestIds = Auth::check() ? Auth::user()->interests()->pluck('categories.id')->toArray() : [];
        $interestIdsStr = !empty($interestIds) ? implode(',', $interestIds) : null;

        // Helper for prioritized query
        $getPrioritized = function($query, $limit = 12) use ($interestIdsStr) {
            if (auth()->check()) {
                $blockedUserIds = \App\Models\BlockedUser::where('user_id', auth()->id())->pluck('blocked_user_id')
                    ->merge(\App\Models\BlockedUser::where('blocked_user_id', auth()->id())->pluck('user_id'))
                    ->toArray();
                $query->whereNotIn('user_id', $blockedUserIds);
            }

            if ($interestIdsStr) {
                return $query->orderByRaw("CASE WHEN category_id IN ($interestIdsStr) THEN 0 ELSE 1 END")
                             ->orderBy('created_at', 'desc')
                             ->limit($limit)
                             ->get();
            }
            return $query->orderBy('created_at', 'desc')->limit($limit)->get();
        };

        // 1. Featured Ads (أبرز الإعلانات)
        $featuredAds = $getPrioritized(
            Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
                ->where('is_featured', true)
        );
            
        // 2. Interactive Ads (الإعلانات الأكثر تفاعل)
        $interactiveAds = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            })
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
        $recentlyViewed = $getPrioritized(
            Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
        );

        // 5. Current Auctions (المزادات الحالية)
        $currentAuctions = $getPrioritized(
            Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->whereIn('type', ['electronic_auction', 'hybrid_auction'])
                ->where('end_date', '>', now())
        );

        // 6. Instant Buy (عروض الشراء الفوري)
        $instantBuy = $getPrioritized(
            Ad::with(['category', 'user', 'city'])
                ->where('status', 'active')
                ->whereNotIn('type', ['electronic_auction', 'hybrid_auction'])
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
        );

        $totalRevenue = 0;
        $chartData = [];
        $currentFilter = request('filter', 'weekly');
        
        if (auth()->check()) {
            $user = auth()->user();
            $totalRevenue = $user->sales()->where('status', 'delivered')->sum('final_price');
            
            if ($currentFilter === 'yearly') {
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $chartData[] = [
                        'day' => $month->locale('ar')->monthName,
                        'total' => $user->sales()->where('status', 'delivered')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('final_price')
                    ];
                }
            } elseif ($currentFilter === 'monthly') {
                for ($i = 3; $i >= 0; $i--) {
                    $weekStart = now()->subWeeks($i)->startOfWeek();
                    $weekEnd = (clone $weekStart)->endOfWeek();
                    $chartData[] = [
                        'day' => "الأسبوع " . (4 - $i),
                        'total' => $user->sales()->where('status', 'delivered')->whereBetween('created_at', [$weekStart, $weekEnd])->sum('final_price')
                    ];
                }
            } else {
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $chartData[] = [
                        'day' => $day->locale('ar')->dayName,
                        'total' => $user->sales()->where('status', 'delivered')->whereDate('created_at', $day->toDateString())->sum('final_price')
                    ];
                }
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
        // Auto-activate upcoming ads whose start date has arrived
        Ad::where('status', 'upcoming')
            ->where(function($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->update(['status' => 'active']);

        $query = Ad::with(['category', 'user', 'city']);

        // Filter out blocked users' ads
        if (auth()->check()) {
            $blockedUserIds = \App\Models\BlockedUser::where('user_id', auth()->id())->pluck('blocked_user_id')
                ->merge(\App\Models\BlockedUser::where('blocked_user_id', auth()->id())->pluck('user_id'))
                ->toArray();
            $query->whereNotIn('user_id', $blockedUserIds);
        }

        // Keywords
        if ($request->has('q') && !empty($request->q)) {
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

        // Dynamic Properties Filter
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

        // Status Filter (Default to active)
        $adStatus = $request->get('ad_status', 'active');
        $query->where('status', $adStatus)->where(function($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>', now());
        });

        // City
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city_id', $request->city);
        }

        // 4. Type Filter (auction, buy_now, etc.)
        if ($request->has('type') && !empty($request->type)) {
            if ($request->type === 'auction') {
                $query->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } elseif ($request->type === 'buy_now') {
                $query->where('type', 'direct_purchase');
            } else {
                $query->where('type', $request->type);
            }
        }

        // 4b. Enfaz Filter
        if ($request->has('is_enfaz')) {
            $query->where('is_enfaz', true);
        }

        // Status Filter
        $adStatus = $request->get('ad_status', 'active');
        if ($adStatus === 'is_featured') {
            $query->where('is_featured', true)->where('status', 'active')->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            });
        } elseif ($adStatus === 'ends_today') {
            $query->whereDate('end_date', now()->toDateString())->where('status', 'active');
        } elseif ($adStatus === 'active') {
            $query->where('status', 'active')->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            });
        } else {
            $query->where('status', $adStatus);
        }

        if ($request->has('user_type') && !empty($request->user_type)) {
            $query->whereHas('user', function($uq) use ($request) {
                $uq->where('account_type', $request->user_type);
            });
        }

        $userLat = Auth::check() ? Auth::user()->latitude : session('user_latitude');
        $userLng = Auth::check() ? Auth::user()->longitude : session('user_longitude');

        if (($request->get('view') === 'map' || $request->has('nearby')) && $userLat && $userLng) {
            $radius = $request->get('radius', 100); 
            $query->selectRaw("ads.*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$userLat, $userLng, $userLat])
                  ->whereNotNull('lat')
                  ->whereNotNull('lng')
                  ->having('distance', '<=', $radius);
        }

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

    public function showAd($id)
    {
        $ad = Ad::with(['category', 'user', 'city', 'adProperties.property', 'images'])->findOrFail($id);
        
        // Draft privacy check: If the ad is incomplete, only the owner can view it
        if ($ad->status === 'incomplete' && (!Auth::check() || Auth::id() !== $ad->user_id)) {
            abort(404);
        }
        
        // Increment views
        $ad->increment('views_count');

        // Related Ads (Same category, only active and not expired)
        $relatedAds = Ad::with(['category', 'user', 'city'])
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            })
            ->limit(4)
            ->get();

        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('ads.show', compact('ad', 'relatedAds', 'settings', 'categories'));
    }

    public function showGallery($id, $index = 0)
    {
        $ad = Ad::with(['category', 'user', 'images'])->findOrFail($id);
        $seller = $ad->user;
        
        // 1. Get real images (Main + Additional)
        $realImages = collect([$ad->image])
            ->merge($ad->images->pluck('image'))
            ->filter()
            ->unique()
            ->map(fn($img) => \Illuminate\Support\Str::startsWith($img, 'http') ? $img : '/' . ltrim($img, '/'))
            ->values();

        // 3. Final collection (Real images only)
        $allImages = $realImages;

        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        $startIndex = (int)$index;

        return view('ads.gallery', compact('ad', 'allImages', 'seller', 'categories', 'settings', 'startIndex'));
    }
    public function showProfile(Request $request, $id)
    {
        // Block Check
        if (auth()->check()) {
            $isBlocked = \App\Models\BlockedUser::where(function($q) use ($id) {
                $q->where('user_id', auth()->id())->where('blocked_user_id', $id)
                  ->orWhere('user_id', $id)->where('blocked_user_id', auth()->id());
            })->exists();

            if ($isBlocked) {
                return redirect()->back()->with('error', 'لا يمكنك عرض هذا الملف الشخصي بسبب قيود الحظر');
            }
        }

        $user = \App\Models\User::with(['ads.category', 'ads.city'])->findOrFail($id);
        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        $cities = City::all();
        
        // Stats for the profile card
        $adsCount = $user->ads()->count();
        $soldCount = $user->sales()->where('status', 'delivered')->count();
        $rating = $user->reviewsReceived()->avg('rating') ?: 5.0;
        $reviewsCount = $user->reviewsReceived()->count();

        // Filtering ads for the Products tab
        $query = $user->ads()->with(['category', 'user', 'city']);

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('type')) {
            if ($request->type === 'auction') {
                $query->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } else {
                $query->where('type', $request->type);
            }
        }
        
        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = Category::with('properties')->find($request->category);
            $query->where('category_id', $request->category);
        }

        // Dynamic Properties Filter
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

        if ($request->filled('city')) {
            $query->where('city_id', $request->city);
        }
        if ($request->filled('ad_status')) {
            if ($request->ad_status === 'active') {
                $query->where('status', 'active')->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                });
            } else {
                $query->where('status', $request->ad_status);
            }
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') $query->orderBy('price', 'desc');
        elseif ($sort === 'price_low') $query->orderBy('price', 'asc');
        else $query->orderBy('created_at', 'desc');

        $ads = $query->paginate(12)->appends($request->all());
        
        return view('users.profile', compact('user', 'categories', 'settings', 'adsCount', 'soldCount', 'rating', 'reviewsCount', 'cities', 'ads', 'activeCategory'));
    }
    public function reportUser(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم إرسال البلاغ بنجاح']);
        }

        return back()->with('success', 'تم إرسال البلاغ بنجاح');
    }

    public function adServices($id)
    {
        $ad = Ad::findOrFail($id);
        $services = \App\Models\Service::where('category_id', $ad->category_id)->get();
        $categories = Category::whereNull('parent_id')->get();
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('services.index', compact('ad', 'services', 'categories', 'settings'));
    }

    public function serviceShow($id)
    {
        $service = \App\Models\Service::findOrFail($id);
        $categories = Category::whereNull('parent_id')->get();
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('services.show', compact('service', 'categories', 'settings'));
    }
}
