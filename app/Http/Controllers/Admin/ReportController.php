<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Review;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $accountType = $request->get('account_type', 'all');
        $categoryId = $request->get('category_id', 'all');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Internal Card Filters
        $adStatusType = $request->get('ad_status_type', 'all'); // 'all', 'auction', 'direct', 'tender'
        $ratingsUserType = $request->get('ratings_user_type', 'all');

        // Base Scopes
        $applyFilters = function($builder) use ($accountType, $categoryId, $startDate, $endDate) {
            if ($accountType !== 'all') {
                $modelClass = get_class($builder->getModel());
                if ($modelClass === User::class) {
                    $builder->where('account_type', $accountType);
                } elseif ($modelClass === Ad::class) {
                    $builder->whereHas('user', function($q) use ($accountType) { $q->where('account_type', $accountType); });
                } elseif ($modelClass === Order::class) {
                    $builder->whereHas('buyer', function($q) use ($accountType) { $q->where('account_type', $accountType); });
                } elseif ($modelClass === Review::class) {
                    $builder->whereHas('reviewer', function($q) use ($accountType) { $q->where('account_type', $accountType); });
                }
            }
            if ($categoryId !== 'all' && in_array(get_class($builder->getModel()), [Ad::class, Order::class])) {
                if (get_class($builder->getModel()) === Ad::class) {
                    $builder->where('category_id', $categoryId);
                } else {
                    $builder->whereHas('ad', function($q) use ($categoryId) { $q->where('category_id', $categoryId); });
                }
            }
            $builder->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            return $builder;
        };

        // 1. User Statistics (7-Day Trend within range)
        $days = collect(range(0, 6))->map(fn($i) => Carbon::parse($endDate)->subDays($i)->translatedFormat('D'))->reverse()->values();
        $userTypes = ['individual', 'company', 'representative', 'partner'];
        $userData = [];
        foreach($userTypes as $type) {
            $userData[$type] = collect(range(0, 6))->map(function($i) use ($type, $accountType, $endDate) {
                if ($accountType !== 'all' && $accountType !== $type) return 0;
                return User::where('account_type', $type)->whereDate('created_at', Carbon::parse($endDate)->subDays($i))->count();
            })->reverse()->values();
        }

        // 2. City Distribution
        $cityDistribution = DB::table('users')
            ->join('cities', 'users.city_id', '=', 'cities.id')
            ->select('cities.name', DB::raw('count(*) as count'))
            ->whereBetween('users.created_at', [$startDate, $endDate]);
        if ($accountType !== 'all') $cityDistribution->where('users.account_type', $accountType);
        $cityDistribution = $cityDistribution->groupBy('cities.id', 'cities.name')->orderBy('count', 'desc')->take(5)->get();

        // 3. Ad Trends
        $adsTrend = [];
        $typeMapping = [
            'auction' => ['electronic_auction', 'hybrid_auction'],
            'direct' => ['direct_purchase'],
            'tender' => ['tender']
        ];
        foreach(['auction', 'direct', 'tender'] as $t) {
            $adsTrend[$t] = collect(range(0, 6))->map(function($i) use ($t, $typeMapping, $applyFilters, $endDate) {
                $q = Ad::whereIn('type', $typeMapping[$t])->whereDate('created_at', Carbon::parse($endDate)->subDays($i));
                return $applyFilters($q)->count();
            })->reverse()->values();
        }

        // 4. Categories Data
        $categoriesList = Category::all();
        $topCategories = Category::withCount(['ads' => fn($q) => $applyFilters($q)])->orderBy('ads_count', 'desc')->take(6)->get();

        // 5. Ad Statuses (Filtered by Status Type Dropdown)
        $qStatus = Ad::query();
        if ($adStatusType !== 'all') {
            $qStatus->whereIn('type', $typeMapping[$adStatusType] ?? []);
        }
        $statusMap = ['active', 'pending', 'finished', 'upcoming'];
        $adStatuses = [];
        foreach($statusMap as $st) {
            $adStatuses[$st] = $applyFilters(clone $qStatus)->where('status', $st)->count();
        }

        // 6. Ratings Breakdown (Filtered by Ratings User Type Dropdown)
        $rQ = Review::query();
        if ($ratingsUserType !== 'all') {
            $rQ->whereHas('reviewer', function($q) use ($ratingsUserType) { $q->where('account_type', $ratingsUserType); });
        }
        $rQ = $applyFilters($rQ);
        $ratingsAvg = (clone $rQ)->avg('rating') ?? 0;
        $ratingsTotal = (clone $rQ)->count();
        $ratings = [
            'avg' => round($ratingsAvg, 1),
            'percentage' => round(($ratingsAvg / 5) * 100),
            'total' => $ratingsTotal,
            'stars' => collect(range(1, 5))->mapWithKeys(fn($s) => [$s => (clone $rQ)->where('rating', $s)->count()])->toArray()
        ];

        // 7. Revenue
        $revenueData = collect(range(0, 6))->map(fn($i) => $applyFilters(Order::whereDate('created_at', Carbon::parse($endDate)->subDays($i)))->sum('final_price'))->reverse()->values();

        // 8. Purchases by Category
        $purchasesByCategory = Category::whereHas('ads.orders')
            ->withCount(['ads as orders_count' => function($q) use ($applyFilters) {
                $q->join('orders', 'ads.id', '=', 'orders.ad_id')
                  ->select(DB::raw('count(orders.id)'));
            }])->orderBy('orders_count', 'desc')->take(5)->get();

        return view('admin.reports.index', compact(
            'userData', 'days', 'topCategories', 'categoriesList', 'adStatuses', 
            'ratings', 'revenueData', 'cityDistribution', 'adsTrend', 'purchasesByCategory',
            'accountType', 'categoryId', 'startDate', 'endDate', 'adStatusType', 'ratingsUserType'
        ));
    }
}
