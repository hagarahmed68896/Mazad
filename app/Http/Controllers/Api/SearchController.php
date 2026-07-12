<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use App\Models\Region;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Ad::with(['category', 'user', 'city']);

        // 1. Keywords (Title, Description, Category Name, City Name)
        $q = $request->get('q') ?: ($request->get('search') ?: ($request->get('keyword') ?: $request->get('query')));
        if (!empty($q)) {
            // تسجيل الكلمة في الكاش ديناميكياً بدون قاعدة بيانات
            $cleanQ = trim($q);
            if (mb_strlen($cleanQ) >= 2) {
                // Global search trends (all users)
                $searchTrends = \Illuminate\Support\Facades\Cache::get('search_trends', []);
                $searchTrends[$cleanQ] = ($searchTrends[$cleanQ] ?? 0) + 1;
                arsort($searchTrends);
                $searchTrends = array_slice($searchTrends, 0, 30, true);
                \Illuminate\Support\Facades\Cache::put('search_trends', $searchTrends, now()->addDays(30));

                // Personal search history (authenticated user)
                $user = auth('sanctum')->user();
                if ($user) {
                    $userId = $user->id;
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

            $query->where(function($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                      ->orWhere('description', 'like', '%' . $q . '%')
                      ->orWhereHas('category', function($cq) use ($q) {
                          $cq->where('name', 'like', '%' . $q . '%');
                      })
                      ->orWhereHas('city', function($cq) use ($q) {
                          $cq->where('name', 'like', '%' . $q . '%');
                      });
            });
        }

        // 2. Category Filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $category = Category::with('subCategories')->find($request->category_id);
            if ($category) {
                $categoryIds = $category->subCategories->pluck('id')->push($category->id)->toArray();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // 3. City Filter (District-level)
        if ($request->has('city_id') && !empty($request->city_id)) {
            $query->where('city_id', $request->city_id);
        }

        // 3a. Region Filter (City-level)
        if ($request->has('region_id') && !empty($request->region_id)) {
            $query->whereHas('city', function($cq) use ($request) {
                $cq->where('region_id', $request->region_id);
            });
        }

        // 3b. Dynamic Category Properties Filter (e.g., props[1]=2024)
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

        // 4. Type Filter (auction, buy_now)
        if ($request->has('type') && !empty($request->type)) {
            if ($request->type === 'auction') {
                $query->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } elseif ($request->type === 'buy_now') {
                $query->where('type', 'direct_purchase');
            } else {
                $query->where('type', $request->type);
            }
        }

        // 4b. Status Filter (Default to active)
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

        // 4c. Enfaz Filter
        if ($request->has('is_enfaz')) {
            $query->where('is_enfaz', $request->is_enfaz);
        }

        // 5. Price Range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // 6. Account Type Filter (individual, company)
        if ($request->has('user_type') && !empty($request->user_type)) {
            $query->whereHas('user', function($uq) use ($request) {
                $uq->where('account_type', $request->user_type);
            });
        }

        // 7. Geographic Distance Filter (Radius)
        // Check both auth user location and requested coordinates
        $lat = $request->lat ?: (Auth::guard('sanctum')->check() ? Auth::guard('sanctum')->user()->latitude : null);
        $lng = $request->lng ?: (Auth::guard('sanctum')->check() ? Auth::guard('sanctum')->user()->longitude : null);
        $radius = $request->get('radius', 50); // Default 50km

        if ($lat && $lng) {
            $query->selectRaw("ads.*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
                  ->whereNotNull('lat')
                  ->whereNotNull('lng')
                  ->having('distance', '<=', $radius);
        }

        // 8. Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'most_viewed':
                $query->orderBy('views_count', 'desc');
                break;
            case 'ending_soon':
                $query->whereNotNull('end_date')
                      ->where('end_date', '>', now())
                      ->orderBy('end_date', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $ads = $query->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'message' => 'نتائج البحث',
            'data' => $this->formatPagination($ads)
        ]);
    }

    public function properties($id)
    {
        $category = Category::with('properties')->find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'الفئة غير موجودة'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category->properties
        ]);
    }

    public function filters()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $regions = Region::with('cities')->get();
        $cities = City::all();
        
        $minPrice = Ad::where('status', 'active')->min('price') ?? 0;
        $maxPrice = Ad::where('status', 'active')->max('price') ?? 100000;

        $types = [
            ['key' => 'direct_purchase', 'label' => 'شراء مباشر'],
            ['key' => 'electronic_auction', 'label' => 'مزاد إلكتروني'],
            ['key' => 'hybrid_auction', 'label' => 'مزاد هجين'],
        ];

        $userTypes = [
            ['key' => 'individual', 'label' => 'فرد'],
            ['key' => 'company', 'label' => 'شركة'],
            ['key' => 'representative', 'label' => 'مندوب'],
            ['key' => 'partner', 'label' => 'شريك'],
        ];

        $cachedTrends = \Illuminate\Support\Facades\Cache::get('search_trends', []);
        $mostSearched = !empty($cachedTrends) ? array_keys(array_slice($cachedTrends, 0, 6)) : [];

        $recentSearches = [];
        $user = auth('sanctum')->user();
        if ($user) {
            $recentSearches = \Illuminate\Support\Facades\Cache::get('user_search_history_' . $user->id, []);
        }

        return response()->json([
            'success' => true,
            'message' => 'فلاتر البحث المتاحة',
            'data' => [
                'categories' => $categories,
                'regions' => $regions,
                'cities' => $cities,
                'types' => $types,
                'user_types' => $userTypes,
                'most_searched' => $mostSearched,
                'recent_searches' => $recentSearches,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ]
        ]);
    }

    public function clearHistory()
    {
        $user = auth('sanctum')->user();
        if ($user) {
            \Illuminate\Support\Facades\Cache::forget('user_search_history_' . $user->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم مسح سجل البحث بنجاح'
        ]);
    }
}
