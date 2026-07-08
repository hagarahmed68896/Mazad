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
        $banners = Banner::where('status', true)->where('for_user_type', 'normal')->get();
        $data = [
            'banners' => $banners,
        ];

        if ($user) {
            if ($user->account_type === 'partner') {
                $query = \App\Models\Service::where('user_id', $user->id);

                // Status Filter (active, pending, rejected)
                if ($request->filled('status') && in_array($request->status, ['active', 'pending', 'rejected'])) {
                    $query->where('status', $request->status);
                }

                // Search Keyword Filter
                if ($request->has('q') && !empty($request->q)) {
                    $query->where(function($q) use ($request) {
                        $q->where('title', 'like', '%' . $request->q . '%')
                          ->orWhere('description', 'like', '%' . $request->q . '%');
                    });
                }

                // Category Filter
                if ($request->has('category') && !empty($request->category)) {
                    $query->where('category_id', $request->category);
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

                $services = $query->with('category')->get()->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'category_id' => $service->category_id,
                        'category_name' => $service->category ? $service->category->name : null,
                        'title' => $service->title,
                        'description' => $service->description,
                        'price' => (float)$service->price,
                        'image' => $service->image ? (\Illuminate\Support\Str::startsWith($service->image, 'http') ? $service->image : url('/' . ltrim($service->image, '/'))) : null,
                        'status' => $service->status,
                        'created_at' => $service->created_at,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'message' => 'الصفحة الرئيسية للشريك',
                    'is_auth' => true,
                    'data' => [
                        'services' => $services
                    ]
                ]);
            }

            // --- AUTH USER CONTENT ONLY ---
            $interestIds = $user->resolvedInterests()->pluck('id')->toArray();
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

    public function categories(Request $request)
    {
        $query = Category::whereNull('parent_id');

        $search = $request->get('q') ?: ($request->get('search') ?: ($request->get('keyword') ?: $request->get('query')));
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'قائمة التصنيفات',
            'data' => $categories
        ]);
    }

    public function servicesOptions(Request $request)
    {
        $categoryIdsInput = $request->get('category_ids');
        $categoryIds = [];

        if (!empty($categoryIdsInput)) {
            if (is_array($categoryIdsInput)) {
                $categoryIds = $categoryIdsInput;
            } else {
                $categoryIds = array_filter(explode(',', $categoryIdsInput));
            }
        }

        $query = \App\Models\Service::where('user_id', 1);

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'category_id' => $service->category_id,
                'title' => $service->title,
                'description' => $service->description,
                'price' => (float)$service->price,
                'image' => $service->image ? (\Illuminate\Support\Str::startsWith($service->image, 'http') ? $service->image : url('/' . ltrim($service->image, '/'))) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'الخدمات المتاحة للتفعيل',
            'data' => $services
        ]);
    }

    public function serviceShow($id)
    {
        $service = \App\Models\Service::with(['user', 'category'])->find($id);
        if ($service && $service->status !== 'active') {
            $user = auth('sanctum')->user();
            if (!$user || ($user->id !== $service->user_id && $user->account_type !== 'admin')) {
                $service = null;
            }
        }
        if (!$service) {
            // Fallback for partner ads stored in the ads table
            $service = \App\Models\Ad::with(['user', 'category', 'city', 'images'])->find($id);
        }

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'الخدمة غير موجودة'
            ], 404);
        }

        $additionalImages = ($service instanceof \App\Models\Ad) ? $service->images : collect();
        $allImages = collect([$service->image])
            ->merge($additionalImages->pluck('image'))
            ->filter()
            ->unique()
            ->map(fn($img) => \Illuminate\Support\Str::startsWith($img, 'http') ? $img : url('/' . ltrim($img, '/')))
            ->values();

        // 1. Fetch reviews received by this advertiser
        $reviews = \App\Models\Review::where('rated_user_id', $service->user_id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'created_at_human' => $review->created_at ? $review->created_at->diffForHumans() : null,
                    'reviewer' => $review->reviewer ? [
                        'id' => $review->reviewer->id,
                        'name' => $review->reviewer->name,
                        'avatar_url' => $review->reviewer->avatar ? url($review->reviewer->avatar) : null,
                    ] : null
                ];
            });

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0.0;

        // 2. Fetch rating distribution (5 down to 1 stars)
        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviews->where('rating', $i)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0; // Default 20% distribution as fallback
            $ratingDistribution[] = [
                'rating' => $i,
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

        return response()->json([
            'success' => true,
            'message' => 'تفاصيل الخدمة والتقييمات',
            'data' => [
                'service' => [
                    'id' => $service->id,
                    'user_id' => $service->user_id,
                    'category_id' => $service->category_id,
                    'title' => $service->title,
                    'image' => $service->image ? (\Illuminate\Support\Str::startsWith($service->image, 'http') ? $service->image : url('/' . ltrim($service->image, '/'))) : null,
                    'description' => $service->description,
                    'price' => (float)$service->price,
                    'phone' => ($service instanceof \App\Models\Service) ? ($service->phone ?? ($service->user->phone ?? null)) : ($service->user->phone ?? null),
                    'email' => ($service instanceof \App\Models\Service) ? ($service->email ?? ($service->user->email ?? null)) : ($service->user->email ?? null),
                    'all_images' => $allImages,
                    'created_at' => $service->created_at,
                    'is_ad_fallback' => $service instanceof \App\Models\Ad,
                ],
                'seller' => $service->user ? [
                    'id' => $service->user->id,
                    'name' => $service->user->name,
                    'email' => $service->user->email,
                    'phone' => $service->user->phone,
                    'avatar_url' => $service->user->avatar ? url($service->user->avatar) : null,
                    'bio' => $service->user->bio,
                ] : null,
                'category' => $service->category ? [
                    'id' => $service->category->id,
                    'name' => $service->category->name,
                ] : null,
                'reviews_info' => [
                    'average_rating' => $avgRating,
                    'total_reviews' => $totalReviews,
                    'rating_distribution' => $ratingDistribution,
                    'reviews' => $reviews,
                ],
                'related_services' => $userServices->map(function($us) {
                    return [
                        'id' => $us->id,
                        'title' => $us->title,
                        'price' => (float)$us->price,
                        'image' => $us->image ? (\Illuminate\Support\Str::startsWith($us->image, 'http') ? $us->image : url('/' . ltrim($us->image, '/'))) : null,
                    ];
                })
            ]
        ]);
    }

    public function userProfile(Request $request, $id)
    {
        // 1. Manually resolve the Bearer Token from headers if Sanctum guard didn't boot it automatically
        $currentUser = Auth::guard('sanctum')->user();
        if (!$currentUser) {
            $token = $request->bearerToken();
            if ($token) {
                $model = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($model && $model->tokenable) {
                    $currentUser = $model->tokenable;
                }
            }
        }

        // Block Check
        if ($currentUser) {
            $isBlocked = \App\Models\BlockedUser::where(function($q) use ($currentUser, $id) {
                $q->where('user_id', $currentUser->id)->where('blocked_user_id', $id)
                  ->orWhere('user_id', $id)->where('blocked_user_id', $currentUser->id);
            })->exists();

            if ($isBlocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك عرض هذا الملف الشخصي بسبب قيود الحظر'
                ], 403);
            }
        }

        $user = \App\Models\User::with(['company', 'teamMembers'])->findOrFail($id);

        // Collect IDs: user + team members (representatives)
        $teamMemberIds = $user->teamMembers->pluck('id')->toArray();
        $allUserIds = array_merge([$user->id], $teamMemberIds);

        // Stats for the profile card (include team members)
        $adsCount = \App\Models\Ad::whereIn('user_id', $allUserIds)->count();
        $soldCount = \App\Models\Order::whereIn('seller_id', $allUserIds)->where('status', 'delivered')->count();
        
        $reviews = $user->reviewsReceived();
        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0.0;

        // Fetch active ads (include team members)
        $adsQuery = \App\Models\Ad::whereIn('user_id', $allUserIds)
            ->with(['category', 'city'])
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            });

        // Optional filtering of user ads (similar to web)
        if ($request->filled('category')) {
            $adsQuery->where('category_id', $request->category);
        }
        if ($request->filled('type')) {
            if ($request->type === 'auction') {
                $adsQuery->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } else {
                $adsQuery->where('type', $request->type);
            }
        }

        $ads = $adsQuery->orderBy('created_at', 'desc')->paginate(12);

        // Fetch reviews
        $userReviews = $user->reviewsReceived()
            ->with('reviewer')
            ->where('is_visible', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'created_at_human' => $review->created_at ? $review->created_at->diffForHumans() : null,
                    'reviewer' => $review->reviewer ? [
                        'id' => $review->reviewer->id,
                        'name' => $review->reviewer->name,
                        'avatar_url' => $review->reviewer->avatar ? (\Illuminate\Support\Str::startsWith($review->reviewer->avatar, 'http') ? $review->reviewer->avatar : url('/' . ltrim($review->reviewer->avatar, '/'))) : null,
                    ] : null
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'تفاصيل ملف المعلن والإعلانات النشطة والتقييمات',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar ? (\Illuminate\Support\Str::startsWith($user->avatar, 'http') ? $user->avatar : url('/' . ltrim($user->avatar, '/'))) : null,
                    'account_type' => $user->account_type,
                    'company_name' => $user->company_name ?? ($user->company->name ?? null),
                    'bio' => $user->bio,
                    'created_at' => $user->created_at,
                    'created_at_human' => $user->created_at ? 'عضو منذ ' . $user->created_at->diffForHumans(null, true) : null,
                ],
                'stats' => [
                    'ads_count' => $adsCount,
                    'sold_count' => $soldCount,
                    'average_rating' => $avgRating,
                    'reviews_count' => $totalReviews
                ],
                'reviews' => $userReviews,
                'ads' => $ads->through(function($ad) {
                    return [
                        'id' => $ad->id,
                        'title' => $ad->title,
                        'price' => (float)$ad->price,
                        'image' => $ad->image ? (\Illuminate\Support\Str::startsWith($ad->image, 'http') ? $ad->image : url('/' . ltrim($ad->image, '/'))) : null,
                        'type' => $ad->type,
                        'views_count' => $ad->views_count,
                        'bids_count' => $ad->bids_count,
                        'city' => $ad->city ? [
                            'id' => $ad->city->id,
                            'name' => $ad->city->name,
                        ] : null,
                        'category' => $ad->category ? [
                            'id' => $ad->category->id,
                            'name' => $ad->category->name,
                        ] : null,
                        'created_at' => $ad->created_at,
                    ];
                })
            ]
        ]);
    }

    public function reportUser(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $reportedUser = \App\Models\User::findOrFail($id);
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول للإبلاغ عن مستخدم.'], 401);
        }

        if ($authUser->id == $reportedUser->id) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك الإبلاغ عن نفسك.'], 400);
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

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال البلاغ وحظر المستخدم بنجاح'
        ]);
    }
}

