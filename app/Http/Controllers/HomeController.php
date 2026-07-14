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
        // Temporary DB update trigger via query param to bypass route caching
        if (request()->has('run_updates')) {
            try {
                // Run migrations
                \Illuminate\Support\Facades\Artisan::call('migrate');
                $migOutput = \Illuminate\Support\Facades\Artisan::output();
                
                // Require and run seeder directly to bypass composer autoload caching
                $seederPath = base_path('database/seeders/PartnerBannerSeeder.php');
                if (file_exists($seederPath)) {
                    require_once $seederPath;
                    $seeder = new \Database\Seeders\PartnerBannerSeeder();
                    $seeder->run();
                    $seedOutput = "PartnerBannerSeeder executed successfully via direct file require!";
                } else {
                    $seedOutput = "Seeder file not found at: " . $seederPath;
                }

                // Clean up any mistakenly added Cairo / القاهرة records
                \App\Models\Region::where('name', 'like', '%قاهر%')->orWhere('name', 'like', '%cair%')->delete();
                \App\Models\City::where('name', 'like', '%قاهر%')->orWhere('name', 'like', '%cair%')->delete();
                
                $regionsList = \App\Models\Region::pluck('name')->toArray();
                
                return "Migration output: <pre>$migOutput</pre><br>Seeder status: <b>$seedOutput</b><br>Remaining regions: " . implode(', ', $regionsList) . "<br>Done! 🎉";
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage() . "<br>Line: " . $e->getLine() . "<br>File: " . $e->getFile();
            }
        }
        // Temporary Banner diagnostics
        if (request()->has('check_banners')) {
            try {
                $banners = \App\Models\Banner::where('for_user_type', 'partner')->get();
                $info = [];
                foreach ($banners as $b) {
                    $info[] = [
                        'id' => $b->id,
                        'name' => $b->name,
                        'status' => $b->status,
                        'items_count' => is_array($b->items) ? count($b->items) : 'not array/empty',
                        'items' => $b->items
                    ];
                }
                return response()->json($info);
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        }
        if (Auth::check()) {
            if (Auth::user()->email === 'admin@mazad.com') {
                return redirect()->route('admin.dashboard');
            }
            if (Auth::user()->account_type === 'partner') {
                return redirect()->route('ads.my');
            }
        }

        // Auto-activate upcoming ads whose start date has arrived
        Ad::where('status', 'upcoming')
            ->where(function($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->update(['status' => 'active']);

        $banners = Banner::where('status', true)->where('for_user_type', 'normal')->get();
        $discoveryCategories = Category::whereNull('parent_id')->get();
        
        // Handle User Interests for personalization (Web)
        $interestIds = Auth::check() ? Auth::user()->resolvedInterests()->pluck('id')->toArray() : [];
        $interestIdsStr = !empty($interestIds) ? implode(',', $interestIds) : null;

        // Helper for prioritized query
        $getPrioritized = function($query, $limit = 12) use ($interestIdsStr) {
            if (auth()->check()) {
                $blockedUserIds = \App\Models\BlockedUser::where('user_id', auth()->id())->pluck('blocked_user_id')
                    ->merge(\App\Models\BlockedUser::where('blocked_user_id', auth()->id())->pluck('user_id'))
                    ->toArray();
                $query->whereNotIn('user_id', $blockedUserIds)
                      ->where('user_id', '!=', auth()->id());
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
        $interactiveQuery = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            });
        if (auth()->check()) {
            $blockedUserIds = \App\Models\BlockedUser::where('user_id', auth()->id())->pluck('blocked_user_id')
                ->merge(\App\Models\BlockedUser::where('blocked_user_id', auth()->id())->pluck('user_id'))
                ->toArray();
            $interactiveQuery->whereNotIn('user_id', $blockedUserIds)
                             ->where('user_id', '!=', auth()->id());
        }
        $interactiveAds = $interactiveQuery->orderBy('views_count', 'desc')
            ->orderBy('bids_count', 'desc')
            ->limit(12)
            ->get();
            
        // 3. Ending Soon (الإعلانات قرب الانتهاء)
        $endingSoonQuery = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '>', now());
        if (auth()->check()) {
            $blockedUserIds = \App\Models\BlockedUser::where('user_id', auth()->id())->pluck('blocked_user_id')
                ->merge(\App\Models\BlockedUser::where('blocked_user_id', auth()->id())->pluck('user_id'))
                ->toArray();
            $endingSoonQuery->whereNotIn('user_id', $blockedUserIds)
                            ->where('user_id', '!=', auth()->id());
        }
        $endingSoon = $endingSoonQuery->orderBy('end_date', 'asc') 
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
        $revenueTrend = 0;
        $trendDirection = 'flat';

        $settings = Setting::pluck('value', 'key')->toArray();

        return view('welcome', compact(
            'banners', 'discoveryCategories', 'featuredAds', 
            'interactiveAds', 'endingSoon', 'recentlyViewed', 
            'currentAuctions', 'instantBuy', 'settings',
            'totalRevenue', 'chartData', 'revenueTrend', 'trendDirection'
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
            $cleanQ = trim($request->q);
            if (mb_strlen($cleanQ) >= 2) {
                // Global search trends (all users)
                $searchTrends = \Illuminate\Support\Facades\Cache::get('search_trends', []);
                $searchTrends[$cleanQ] = ($searchTrends[$cleanQ] ?? 0) + 1;
                arsort($searchTrends);
                $searchTrends = array_slice($searchTrends, 0, 30, true);
                \Illuminate\Support\Facades\Cache::put('search_trends', $searchTrends, now()->addDays(30));

                // Personal search history (authenticated web user)
                if (auth()->check()) {
                    $userId = auth()->id();
                    $historyKey = 'user_search_history_' . $userId;
                    $userHistory = \Illuminate\Support\Facades\Cache::get($historyKey, []);
                    if (($key = array_search($cleanQ, $userHistory)) !== false) {
                        unset($userHistory[$key]);
                    }
                    array_unshift($userHistory, $cleanQ);
                    $userHistory = array_slice($userHistory, 0, 10);
                    \Illuminate\Support\Facades\Cache::put($historyKey, $userHistory, now()->addDays(30));
                }
            }

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
            $activeCategory = Category::with(['properties', 'subCategories'])->find($request->category);
            if ($activeCategory) {
                $categoryIds = $activeCategory->subCategories->pluck('id')->push($activeCategory->id)->toArray();
                $query->whereIn('category_id', $categoryIds);
            }
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

        // City / District Filter
        $districtFilter = $request->get('district') ?: $request->get('district_id');
        $cityFilter = $request->get('city') ?: $request->get('city_id');
        
        if (empty($cityFilter) && empty($districtFilter)) {
            $cityFilter = Auth::check() ? Auth::user()->city_id : session('user_city_id');
        }

        if (!empty($districtFilter)) {
            $query->where('city_id', $districtFilter);
            if (!empty($cityFilter)) {
                $query->whereHas('city', function($cq) use ($cityFilter) {
                    $cq->where('region_id', $cityFilter);
                });
            }
        } elseif (!empty($cityFilter)) {
            // Check if it's a Region ID (City level)
            $isRegion = \App\Models\Region::where('id', $cityFilter)->exists();
            if ($isRegion) {
                $query->whereHas('city', function($cq) use ($cityFilter) {
                    $cq->where('region_id', $cityFilter);
                });
            } else {
                $query->where('city_id', $cityFilter);
            }
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
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'ending_soon') {
            $query->orderBy('end_date', 'asc')->whereNotNull('end_date')->where('end_date', '>', now());
        } else {
            // Default sort (newest or relevant)
            if ($userLat && $userLng) {
                // Prioritize by distance (closest first), keeping ads without coordinates at the end
                $query->orderByRaw("CASE WHEN lat IS NULL OR lng IS NULL THEN 1 ELSE 0 END")
                      ->orderByRaw("(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) ASC", [$userLat, $userLng, $userLat]);
            }
            
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            } else {
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
            }
        }

        $ads = $query->paginate(12)->appends($request->all());
        
        $categories = Category::whereNull('parent_id')->with('subCategories')->get();
        $regions = \App\Models\Region::orderBy('name')->get();
        $cities = \App\Models\City::orderBy('name')->get();
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('search', compact('ads', 'categories', 'regions', 'cities', 'settings', 'activeCategory'));
    }
    public function showAd($id)
    {
        $ad = Ad::with(['category', 'user', 'city', 'adProperties.property', 'images'])->findOrFail($id);

        // If the ad is owned by a partner, redirect to services.show
        if ($ad->user && $ad->user->account_type === 'partner') {
            return redirect()->route('services.show', $ad->id);
        }
        
        // Draft privacy check: If the ad is incomplete, only the owner can view it
        if ($ad->status === 'incomplete' && (!Auth::check() || Auth::id() !== $ad->user_id)) {
            abort(404);
        }
        
        // Increment views
        if (!Auth::check() || Auth::id() !== $ad->user_id) {
            $ad->increment('views_count');
        }

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

        $services = \App\Models\Service::where('category_id', $ad->category_id)->where('status', 'active')->get();

        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('ads.show', compact('ad', 'relatedAds', 'settings', 'categories', 'services'));
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

        if ($allImages->isEmpty()) {
            return redirect()->route('ads.show', $id)->with('error', 'لا توجد صور متوفرة لهذا الإعلان.');
        }

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

        $user = \App\Models\User::with(['ads.category', 'ads.city', 'company', 'reviewsReceived.reviewer', 'teamMembers'])->findOrFail($id);
        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        $cities = City::all();

        // Collect IDs: the user themselves + any team members (representatives) if this is a company
        $teamMemberIds = $user->teamMembers->pluck('id')->toArray();
        $allUserIds = array_merge([$user->id], $teamMemberIds);

        // Stats for the profile card (include team members' ads & sales)
        $adsCount = \App\Models\Ad::whereIn('user_id', $allUserIds)->count();
        $soldCount = \App\Models\Order::whereIn('seller_id', $allUserIds)->where('status', 'delivered')->count();
        $rating = $user->reviewsReceived()->avg('rating') ?: 0.0;
        $reviewsCount = $user->reviewsReceived()->count();

        // Filtering ads for the Products tab (include team members' ads)
        $query = \App\Models\Ad::whereIn('user_id', $allUserIds)->with(['category', 'user', 'city']);

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
            $activeCategory = Category::with(['properties', 'subCategories'])->find($request->category);
            if ($activeCategory) {
                $categoryIds = $activeCategory->subCategories->pluck('id')->push($activeCategory->id)->toArray();
                $query->whereIn('category_id', $categoryIds);
            }
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

        $reportedUser = \App\Models\User::findOrFail($id);
        $authUser = Auth::user();

        if (!$authUser) {
            return back()->with('error', 'يجب تسجيل الدخول للإبلاغ عن مستخدم.');
        }

        if ($authUser->id == $reportedUser->id) {
            return back()->with('error', 'لا يمكنك الإبلاغ عن نفسك.');
        }

        // 1. Create a Ticket of type 'report'
        $ticket = \App\Models\Ticket::create([
            'ticket_number' => 'TCK-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'type' => 'report',
            'user_id' => $authUser->id,
            'phone' => $authUser->phone,
            'reported_about' => 'بلاغ عن مستخدم: ' . $reportedUser->name . ' (ID: ' . $reportedUser->id . ')',
            'description' => $request->reason,
            'status' => 'under_review',
        ]);

        // Create log for the ticket
        $ticket->logs()->create([
            'action' => 'ticket_created',
            'note' => 'تم إنشاء بلاغ ضد المستخدم ' . $reportedUser->name . ' بنجاح.'
        ]);

        // 2. Automatically Block the reported user for the auth user
        \App\Models\BlockedUser::firstOrCreate([
            'user_id' => $authUser->id,
            'blocked_user_id' => $reportedUser->id
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم إرسال البلاغ وحظر المستخدم بنجاح']);
        }

        return back()->with('success', 'تم إرسال البلاغ وحظر المستخدم بنجاح');
    }

    public function adServices(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);
        
        $query = \App\Models\Service::where('category_id', $ad->category_id)->where('status', 'active');
        
        // Search Filter
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        
        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $services = $query->paginate(12)->appends($request->all());
        $categories = Category::whereNull('parent_id')->get();
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('services.index', compact('ad', 'services', 'categories', 'settings'));
    }

    public function serviceShow($id)
    {
        $service = \App\Models\Service::find($id);
        if ($service && $service->status !== 'active') {
            if (!Auth::check() || (Auth::id() !== $service->user_id && Auth::user()->account_type !== 'admin')) {
                $service = null;
            }
        }
        if (!$service) {
            // Fallback for partner ads stored in the ads table
            $service = \App\Models\Ad::find($id);
        }

        if (!$service) {
            abort(404);
        }

        $categories = Category::whereNull('parent_id')->get();
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        // Check if the current authenticated user has already requested this service
        $hasOrdered = false;
        if (Auth::check()) {
            $hasOrdered = \App\Models\ServiceOrder::where('buyer_id', Auth::id())
                ->where('status', 'paid')
                ->where(function($q) use ($service) {
                    if ($service instanceof \App\Models\Service) {
                        $q->where('service_id', $service->id);
                    } else {
                        $q->where('ad_id', $service->id);
                    }
                })
                ->exists();
        }

        // 1. Fetch reviews received by this advertiser
        $reviews = \App\Models\Review::where('rated_user_id', $service->user_id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0.0;

        // 2. Fetch rating distribution (5 down to 1 stars)
        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviews->where('rating', $i)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0; // Default 20% distribution as visually pictured in placeholder
            $ratingDistribution[$i] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        // 3. Fetch related services from this seller
        if ($service instanceof \App\Models\Ad) {
            $userServices = \App\Models\Ad::where('user_id', $service->user_id)
                ->where('id', '!=', $service->id)
                ->limit(4)
                ->get();
        } else {
            $userServices = \App\Models\Service::where('user_id', $service->user_id)
                ->where('id', '!=', $service->id)
                ->where('status', 'active')
                ->limit(4)
                ->get();
        }

        return view('services.show', compact(
            'service', 
            'categories', 
            'settings',
            'reviews',
            'totalReviews',
            'avgRating',
            'ratingDistribution',
            'userServices',
            'hasOrdered'
        ));
    }

    public function serviceGallery($id, $index = 0)
    {
        $service = \App\Models\Service::find($id);
        if (!$service) {
            $service = \App\Models\Ad::find($id);
        }

        if (!$service) {
            abort(404);
        }

        $seller = $service->user;

        $additionalImages = ($service instanceof \App\Models\Ad) ? $service->images : collect();
        $allImages = collect([$service->image])
            ->merge($additionalImages->pluck('image'))
            ->filter()
            ->unique()
            ->map(fn($img) => \Illuminate\Support\Str::startsWith($img, 'http') ? $img : '/' . ltrim($img, '/'))
            ->values();

        if ($allImages->isEmpty()) {
            if ($service instanceof \App\Models\Ad) {
                return redirect()->route('ads.show', $id)->with('error', 'لا توجد صور متوفرة لهذا الإعلان.');
            }
            return redirect()->route('services.show', $id)->with('error', 'لا توجد صور متوفرة لهذه الخدمة.');
        }

        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        $startIndex = (int)$index;

        // Map $service to $ad so the ads.gallery view is reused seamlessly
        $ad = $service;

        return view('ads.gallery', compact('ad', 'allImages', 'seller', 'categories', 'settings', 'startIndex'));
    }

    public function showAllCategories()
    {
        $categories = Category::whereNull('parent_id')->with('subCategories')->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('categories', compact('categories', 'settings'));
    }

    public function showCategoryAds(Request $request, $id)
    {
        if ($request->has('category')) {
            $catId = $request->category;
            if (empty($catId)) {
                return redirect()->route('search', $request->except('category'));
            }
            if ($catId != $id) {
                return redirect()->route('categories.show', array_merge(['id' => $catId], $request->except('category')));
            }
        }

        $request->merge(['category' => $id]);
        return $this->search($request);
    }
}
