<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح'
            ], 401);
        }

        $tab = $request->query('tab', 'sales'); // 'sales' or 'purchases'
        $type = $request->query('type', 'all'); // 'all', 'electronic_auction', 'hybrid_auction', 'direct_purchase'
        $period = $request->query('period', 'monthly'); // 'weekly', 'monthly', 'yearly'

        // 1. Calculate General Stats
        // Sales statistics
        $salesRevenueQuery = Ad::where('user_id', $user->id)
            ->whereHas('orders')
            ->join('orders', 'ads.id', '=', 'orders.ad_id');
        if ($type !== 'all') {
            $salesRevenueQuery->where('ads.type', $type);
        }
        $totalRevenue = $salesRevenueQuery->sum('orders.final_price');

        // Purchases statistics
        $purchasesCountQuery = Order::where('buyer_id', $user->id);
        if ($type !== 'all') {
            $purchasesCountQuery->whereHas('ad', function($q) use ($type) {
                $q->where('type', $type);
            });
        }
        $purchasesCount = $purchasesCountQuery->count();

        // 2. Fetch items for the active tab with pagination
        if ($tab === 'sales') {
            $query = Ad::where('user_id', $user->id)
                ->whereHas('orders')
                ->with(['orders.buyer', 'category', 'city']);

            if ($type !== 'all') {
                $query->where('type', $type);
            }

            $items = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
        } else {
            $query = Order::where('buyer_id', $user->id)
                ->with(['ad.category', 'ad.city', 'seller']);

            if ($type !== 'all') {
                $query->whereHas('ad', function($q) use ($type) {
                    $q->where('type', $type);
                });
            }

            $items = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
        }

        // 3. Generate Chart Data
        $chartLabels = [];
        $chartData = [];

        if ($period === 'weekly') {
            $days = 7;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $chartLabels[] = $date->translatedFormat('l'); // e.g. "السبت"
                
                if ($tab === 'sales') {
                    $val = Ad::where('user_id', $user->id)
                        ->whereHas('orders', function($q) use ($date) {
                            $q->whereDate('created_at', $date->toDateString());
                        })
                        ->join('orders', 'ads.id', '=', 'orders.ad_id')
                        ->sum('orders.final_price');
                } else {
                    $val = Order::where('buyer_id', $user->id)
                        ->whereDate('created_at', $date->toDateString())
                        ->count();
                }
                $chartData[] = (float) $val;
            }
        } elseif ($period === 'yearly') {
            $months = 12;
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $chartLabels[] = $date->translatedFormat('M'); // e.g. "يناير"
                
                if ($tab === 'sales') {
                    $val = Ad::where('user_id', $user->id)
                        ->whereHas('orders', function($q) use ($date) {
                            $q->whereMonth('created_at', $date->month)
                              ->whereYear('created_at', $date->year);
                        })
                        ->join('orders', 'ads.id', '=', 'orders.ad_id')
                        ->sum('orders.final_price');
                } else {
                    $val = Order::where('buyer_id', $user->id)
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                }
                $chartData[] = (float) $val;
            }
        } else { // monthly (default) - weekly aggregation
            $weeks = ['الجمعة الماضية', 'الأسبوع الثالث', 'الأسبوع الثاني', 'الأسبوع الحالي'];
            foreach ($weeks as $index => $weekName) {
                $chartLabels[] = $weekName;
                
                $subWeeks = 3 - $index;
                $startDate = now()->subWeeks($subWeeks)->startOfWeek();
                $endDate = now()->subWeeks($subWeeks)->endOfWeek();
                
                if ($tab === 'sales') {
                    $val = Ad::where('user_id', $user->id)
                        ->whereHas('orders', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate]);
                        })
                        ->join('orders', 'ads.id', '=', 'orders.ad_id')
                        ->sum('orders.final_price');
                } else {
                    $val = Order::where('buyer_id', $user->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count();
                }
                

                
                $chartData[] = (float) $val;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'بيانات التقارير والتحليلات',
            'data' => [
                'stats' => [
                    'total_revenue' => (float) $totalRevenue,
                    'purchases_count' => (int) $purchasesCount,
                ],
                'active_tab' => $tab,
                'period' => $period,
                'type' => $type,
                'chart' => [
                    'labels' => $chartLabels,
                    'data' => $chartData,
                ],
                'items' => $items,
            ]
        ]);
    }
}
