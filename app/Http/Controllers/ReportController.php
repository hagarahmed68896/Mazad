<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Order;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $tab = $request->query('tab', 'sales'); // 'sales' or 'purchases'
        $type = $request->query('type', 'all'); // 'all', 'electronic_auction', 'hybrid_auction', 'direct_purchase'
        $period = $request->query('period', 'monthly'); // 'weekly', 'monthly', 'yearly'
        
        $isPartner = ($user->account_type === 'partner');

        // 1. Calculate General Stats
        if ($isPartner) {
            // Partner Sales (Service Orders completed/under_review)
            $totalRevenue = ServiceOrder::where('seller_id', $user->id)
                ->whereIn('status', ['completed', 'under_review'])
                ->sum('price');

            // Partner Purchases (if any, normally hidden)
            $purchasesCount = ServiceOrder::where('buyer_id', $user->id)
                ->count();
        } else {
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
        }

        // 2. Fetch items for the active tab with pagination
        if ($tab === 'sales') {
            if ($isPartner) {
                $items = ServiceOrder::where('seller_id', $user->id)
                    ->whereIn('status', ['completed', 'under_review'])
                    ->with(['buyer', 'service', 'ad'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->appends($request->query());
            } else {
                $query = Ad::where('user_id', $user->id)
                    ->whereHas('orders')
                    ->with(['orders.buyer', 'category', 'city']);

                if ($type !== 'all') {
                    $query->where('type', $type);
                }

                $items = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
            }
        } else {
            if ($isPartner) {
                $items = ServiceOrder::where('buyer_id', $user->id)
                    ->with(['seller', 'service', 'ad'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->appends($request->query());
            } else {
                $query = Order::where('buyer_id', $user->id)
                    ->with(['ad.category', 'ad.city', 'seller']);

                if ($type !== 'all') {
                    $query->whereHas('ad', function($q) use ($type) {
                        $q->where('type', $type);
                    });
                }

                $items = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
            }
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
                    if ($isPartner) {
                        $val = ServiceOrder::where('seller_id', $user->id)
                            ->whereIn('status', ['completed', 'under_review'])
                            ->whereDate('created_at', $date->toDateString())
                            ->sum('price');
                    } else {
                        $val = Ad::where('user_id', $user->id)
                            ->whereHas('orders', function($q) use ($date) {
                                $q->whereDate('created_at', $date->toDateString());
                            })
                            ->join('orders', 'ads.id', '=', 'orders.ad_id')
                            ->sum('orders.final_price');
                    }
                } else {
                    if ($isPartner) {
                        $val = ServiceOrder::where('buyer_id', $user->id)
                            ->whereDate('created_at', $date->toDateString())
                            ->count();
                    } else {
                        $val = Order::where('buyer_id', $user->id)
                            ->whereDate('created_at', $date->toDateString())
                            ->count();
                    }
                }
                $chartData[] = $val;
            }
        } elseif ($period === 'yearly') {
            $months = 12;
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $chartLabels[] = $date->translatedFormat('M'); // e.g. "يناير"
                
                if ($tab === 'sales') {
                    if ($isPartner) {
                        $val = ServiceOrder::where('seller_id', $user->id)
                            ->whereIn('status', ['completed', 'under_review'])
                            ->whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year)
                            ->sum('price');
                    } else {
                        $val = Ad::where('user_id', $user->id)
                            ->whereHas('orders', function($q) use ($date) {
                                $q->whereMonth('created_at', $date->month)
                                  ->whereYear('created_at', $date->year);
                            })
                            ->join('orders', 'ads.id', '=', 'orders.ad_id')
                            ->sum('orders.final_price');
                    }
                } else {
                    if ($isPartner) {
                        $val = ServiceOrder::where('buyer_id', $user->id)
                            ->whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year)
                            ->count();
                    } else {
                        $val = Order::where('buyer_id', $user->id)
                            ->whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year)
                            ->count();
                    }
                }
                $chartData[] = $val;
            }
        } else { // monthly (default) - weekly aggregation
            $weeks = ['الجمعة الماضية', 'الأسبوع الثالث', 'الأسبوع الثاني', 'الأسبوع الحالي'];
            foreach ($weeks as $index => $weekName) {
                $chartLabels[] = $weekName;
                
                $subWeeks = 3 - $index;
                $startDate = now()->subWeeks($subWeeks)->startOfWeek();
                $endDate = now()->subWeeks($subWeeks)->endOfWeek();
                
                if ($tab === 'sales') {
                    if ($isPartner) {
                        $val = ServiceOrder::where('seller_id', $user->id)
                            ->whereIn('status', ['completed', 'under_review'])
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->sum('price');
                    } else {
                        $val = Ad::where('user_id', $user->id)
                            ->whereHas('orders', function($q) use ($startDate, $endDate) {
                                $q->whereBetween('created_at', [$startDate, $endDate]);
                            })
                            ->join('orders', 'ads.id', '=', 'orders.ad_id')
                            ->sum('orders.final_price');
                    }
                } else {
                    if ($isPartner) {
                        $val = ServiceOrder::where('buyer_id', $user->id)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->count();
                    } else {
                        $val = Order::where('buyer_id', $user->id)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->count();
                    }
                }
                
                $chartData[] = $val;
            }
        }

        // Calculate Trend Percentage and Direction based on active tab and period
        if ($period === 'weekly') {
            // Last 7 days vs preceding 7 days
            $startDateCurrent = now()->subDays(6)->startOfDay();
            $endDateCurrent = now()->endOfDay();
            $startDatePrevious = now()->subDays(13)->startOfDay();
            $endDatePrevious = now()->subDays(7)->endOfDay();
        } elseif ($period === 'yearly') {
            // Last 12 months vs preceding 12 months
            $startDateCurrent = now()->subMonths(11)->startOfMonth();
            $endDateCurrent = now()->endOfDay();
            $startDatePrevious = now()->subMonths(23)->startOfMonth();
            $endDatePrevious = now()->subMonths(12)->endOfMonth();
        } else { // monthly (default)
            // Last 4 weeks (28 days) vs preceding 4 weeks
            $startDateCurrent = now()->subWeeks(3)->startOfWeek();
            $endDateCurrent = now()->endOfWeek();
            $startDatePrevious = now()->subWeeks(7)->startOfWeek();
            $endDatePrevious = now()->subWeeks(4)->endOfWeek();
        }

        if ($tab === 'sales') {
            if ($isPartner) {
                $currentValue = ServiceOrder::where('seller_id', $user->id)
                    ->whereIn('status', ['completed', 'under_review'])
                    ->whereBetween('created_at', [$startDateCurrent, $endDateCurrent])
                    ->sum('price');

                $previousValue = ServiceOrder::where('seller_id', $user->id)
                    ->whereIn('status', ['completed', 'under_review'])
                    ->whereBetween('created_at', [$startDatePrevious, $endDatePrevious])
                    ->sum('price');
            } else {
                // Sum of final price of orders sold by user
                $currentValQuery = Order::where('seller_id', $user->id)
                    ->whereBetween('created_at', [$startDateCurrent, $endDateCurrent]);
                if ($type !== 'all') {
                    $currentValQuery->whereHas('ad', function($q) use ($type) {
                        $q->where('type', $type);
                    });
                }
                $currentValue = $currentValQuery->sum('final_price');

                $prevValQuery = Order::where('seller_id', $user->id)
                    ->whereBetween('created_at', [$startDatePrevious, $endDatePrevious]);
                if ($type !== 'all') {
                    $prevValQuery->whereHas('ad', function($q) use ($type) {
                        $q->where('type', $type);
                    });
                }
                $previousValue = $prevValQuery->sum('final_price');
            }
        } else {
            if ($isPartner) {
                $currentValue = ServiceOrder::where('buyer_id', $user->id)
                    ->whereBetween('created_at', [$startDateCurrent, $endDateCurrent])
                    ->count();

                $previousValue = ServiceOrder::where('buyer_id', $user->id)
                    ->whereBetween('created_at', [$startDatePrevious, $endDatePrevious])
                    ->count();
            } else {
                // Count of orders bought by user
                $currentValQuery = Order::where('buyer_id', $user->id)
                    ->whereBetween('created_at', [$startDateCurrent, $endDateCurrent]);
                if ($type !== 'all') {
                    $currentValQuery->whereHas('ad', function($q) use ($type) {
                        $q->where('type', $type);
                    });
                }
                $currentValue = $currentValQuery->count();

                $prevValQuery = Order::where('buyer_id', $user->id)
                    ->whereBetween('created_at', [$startDatePrevious, $endDatePrevious]);
                if ($type !== 'all') {
                    $prevValQuery->whereHas('ad', function($q) use ($type) {
                        $q->where('type', $type);
                    });
                }
                $previousValue = $prevValQuery->count();
            }
        }

        $currentValue = floatval($currentValue);
        $previousValue = floatval($previousValue);

        if ($previousValue == 0.0) {
            if ($currentValue > 0.0) {
                $trendPercentage = 100;
                $trendDirection = 'up';
            } else {
                $trendPercentage = 0;
                $trendDirection = 'flat';
            }
        } else {
            $diff = $currentValue - $previousValue;
            $percentage = ($diff / $previousValue) * 100;
            if ($percentage > 0.0) {
                $trendPercentage = round($percentage, 2);
                $trendDirection = 'up';
            } elseif ($percentage < 0.0) {
                $trendPercentage = round(abs($percentage), 2);
                $trendDirection = 'down';
            } else {
                $trendPercentage = 0;
                $trendDirection = 'flat';
            }
        }

        return view('reports.index', compact(
            'items', 'tab', 'type', 'period', 'totalRevenue', 'purchasesCount', 
            'chartLabels', 'chartData', 'trendPercentage', 'trendDirection'
        ));
    }
}
