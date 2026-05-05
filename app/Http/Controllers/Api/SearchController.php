<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Ad::with(['category', 'user', 'city']);

        // 1. Keywords (Title, Description, Category Name, City Name)
        if ($request->has('q') && !empty($request->q)) {
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

        // 2. Category Filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // 3. City Filter
        if ($request->has('city_id') && !empty($request->city_id)) {
            $query->where('city_id', $request->city_id);
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
            'data' => $ads
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
}
