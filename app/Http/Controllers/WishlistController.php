<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Favorite;
use App\Models\Category;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display the user's wishlist page with filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Auto-activate upcoming ads whose start date has arrived
        Ad::where('status', 'upcoming')
            ->where(function($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->update(['status' => 'active']);

        // Start query with Ads that are favorited by the current user
        $query = Ad::whereHas('favorites', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['category', 'user', 'city']);

        // Filter out blocked users' ads
        if (auth()->check()) {
            $blockedUserIds = \App\Models\BlockedUser::where('user_id', auth()->id())->pluck('blocked_user_id')
                ->merge(\App\Models\BlockedUser::where('blocked_user_id', auth()->id())->pluck('user_id'))
                ->toArray();
            $query->whereNotIn('user_id', $blockedUserIds);
        }

        // Keywords
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

        // City
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city_id', $request->city);
        }

        // Type Filter (auction, buy_now, etc.)
        if ($request->has('type') && !empty($request->type)) {
            if ($request->type === 'auction') {
                $query->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } elseif ($request->type === 'buy_now') {
                $query->where('type', 'direct_purchase');
            } else {
                $query->where('type', $request->type);
            }
        }

        // Enfaz Filter
        if ($request->has('is_enfaz')) {
            $query->where('is_enfaz', true);
        }

        // Status Filter (Default to active)
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

        // User Type (individual, company)
        if ($request->has('user_type') && !empty($request->user_type)) {
            $query->whereHas('user', function($uq) use ($request) {
                $uq->where('account_type', $request->user_type);
            });
        }

        // Geographic Distance Filter (Radius)
        $userLat = Auth::check() ? Auth::user()->latitude : session('user_latitude');
        $userLng = Auth::check() ? Auth::user()->longitude : session('user_longitude');

        if (($request->get('view') === 'map' || $request->has('nearby')) && $userLat && $userLng) {
            $radius = $request->get('radius', 100);
            $query->selectRaw("ads.*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$userLat, $userLng, $userLat])
                  ->whereNotNull('lat')
                  ->whereNotNull('lng')
                  ->having('distance', '<=', $radius);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'ending_soon') {
            $query->orderBy('end_date', 'asc')->whereNotNull('end_date')->where('end_date', '>', now());
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $ads = $query->paginate(12)->appends($request->all());

        $categories = Category::whereNull('parent_id')->with('subCategories')->get();
        $cities = City::all();
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('ads.wishlist', compact('ads', 'categories', 'cities', 'settings', 'activeCategory'));
    }
    /**
     * Toggle an ad in the user's wishlist.
     */
    public function toggle(Request $request, $adId)
    {
        $ad = Ad::findOrFail($adId);
        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
                            ->where('ad_id', $ad->id)
                            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
            $message = 'تم إزالة الإعلان من المفضلة';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'ad_id' => $ad->id
            ]);
            $status = 'added';
            $message = 'تم إضافة الإعلان إلى المفضلة';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'action' => $status,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }
}
