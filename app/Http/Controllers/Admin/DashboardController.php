<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ad;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $defaultPeriod = $request->get('period', 'weekly');
        $usersPeriod   = $request->get('users_period', $defaultPeriod);
        $adsPeriod     = $request->get('ads_period', $defaultPeriod);
        $revenuePeriod = $request->get('revenue_period', $defaultPeriod);
        $adType        = $request->get('ad_type', 'all'); 

        $totalUsers = User::count();
        $totalAds = Ad::count();
        $totalRevenue = Transaction::where('status', 'completed')->sum('amount') ?: 0;
        
        $growthData = [
            'users' => number_format((rand(5, 50) / 10), 2),
            'ads' => number_format((rand(5, 50) / 10), 2),
            'revenue' => number_format((rand(5, 50) / 10), 2),
        ];

        // Recent Transactions
        $recentTransactions = Transaction::with(['user', 'ad'])
            ->latest()
            ->take(5)
            ->get();

        $cardStats = [
            [
                'label' => 'إجمالي المستخدمين',
                'value' => number_format($totalUsers),
                'percentage' => $growthData['users'],
                'color' => '#1A73E8',
                'icon' => 'total',
                'sparkline' => [20, 35, 25, 45, 55, 30, 65]
            ],
            [
                'label' => 'إجمالي الإعلانات',
                'value' => number_format($totalAds),
                'percentage' => $growthData['ads'],
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [15, 25, 30, 20, 40, 35, 50]
            ],
            [
                'label' => 'إجمالي الإيرادات',
                'value' => number_format($totalRevenue),
                'percentage' => $growthData['revenue'],
                'color' => '#6366F1',
                'icon' => 'scheduled',
                'is_money' => true,
                'sparkline' => [10, 20, 15, 30, 25, 45, 40]
            ],
        ];

        $charts = $this->getChartData($usersPeriod, $adsPeriod, $revenuePeriod, $adType);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'charts' => $charts,
                'usersPeriod' => $usersPeriod,
                'adsPeriod' => $adsPeriod,
                'revenuePeriod' => $revenuePeriod,
                'adType' => $adType,
            ]);
        }

        return view('dashboard', compact(
            'usersPeriod', 'adsPeriod', 'revenuePeriod', 'adType',
            'totalUsers', 'totalAds', 'totalRevenue', 'growthData',
            'recentTransactions', 'charts', 'cardStats'
        ));
    }

    private function getChartData($usersPeriod, $adsPeriod, $revenuePeriod, $adType)
    {
        Carbon::setLocale('ar');
        
        $buildDateRanges = function($period, $points = 7) {
            $ranges = [];
            for ($i = $points - 1; $i >= 0; $i--) {
                if ($period == 'yearly') {
                    $date = Carbon::now()->subMonths($i);
                    $label = $date->translatedFormat('M Y');
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();
                } elseif ($period == 'monthly') {
                    $date = Carbon::now()->subWeeks($i);
                    $label = 'أسبوع ' . $date->weekOfMonth;
                    $start = $date->copy()->startOfWeek();
                    $end = $date->copy()->endOfWeek();
                } else { // weekly
                    $date = Carbon::now()->subDays($i);
                    $label = $date->translatedFormat('l');
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->endOfDay();
                }
                $ranges[] = [
                    'label' => $label,
                    'start' => $start,
                    'end'   => $end,
                ];
            }
            return $ranges;
        };

        // 1. Users Chart
        $usersChart = ['labels' => [], 'individuals' => [], 'companies' => []];
        $userRanges = $buildDateRanges($usersPeriod);
        foreach ($userRanges as $range) {
            $usersChart['labels'][] = $range['label'];
            $usersChart['individuals'][] = User::where('account_type', 'individual')->whereBetween('created_at', [$range['start'], $range['end']])->count();
            $usersChart['companies'][] = User::where('account_type', 'company')->whereBetween('created_at', [$range['start'], $range['end']])->count();
        }

        // 2. Ads Chart
        $adsChart = ['labels' => [], 'auctions' => [], 'direct_purchase' => []];
        $adRanges = $buildDateRanges($adsPeriod);
        foreach ($adRanges as $range) {
            $adsChart['labels'][] = $range['label'];
            $adsChart['auctions'][] = Ad::whereIn('type', ['electronic_auction', 'hybrid_auction'])->whereBetween('created_at', [$range['start'], $range['end']])->count();
            $adsChart['direct_purchase'][] = Ad::where('type', 'direct_purchase')->whereBetween('created_at', [$range['start'], $range['end']])->count();
        }

        // 3. Revenue Chart
        $revenueChart = ['labels' => [], 'revenue' => []];
        $revenueRanges = $buildDateRanges($revenuePeriod);
        foreach ($revenueRanges as $range) {
            $revenueChart['labels'][] = $range['label'];
            $revenueChart['revenue'][] = Transaction::where('status', 'completed')->whereBetween('created_at', [$range['start'], $range['end']])->sum('amount');
        }

        // 4. Status Chart
        $statusChart = ['active' => 0, 'pending' => 0, 'upcoming' => 0, 'finished' => 0];
        $statusQuery = Ad::query();
        if ($adType == 'auctions') {
            $statusQuery->whereIn('type', ['electronic_auction', 'hybrid_auction']);
        } elseif ($adType == 'direct') {
            $statusQuery->where('type', 'direct_purchase');
        } elseif ($adType == 'tenders') {
            $statusQuery->where('type', 'tender');
        }

        $statusChart['active'] = (clone $statusQuery)->where('status', 'active')->count();
        $statusChart['pending'] = (clone $statusQuery)->where(function($q) {
            $q->whereNull('status')->orWhere('status', 'under_review');
        })->count(); 
        $statusChart['upcoming'] = (clone $statusQuery)->where('status', 'upcoming')->count();
        $statusChart['finished'] = (clone $statusQuery)->where('status', 'finished')->count();

        if (array_sum($statusChart) == 0) {
            $statusChart['active'] = 1;
        }

        return compact('usersChart', 'adsChart', 'revenueChart', 'statusChart', 'adType');
    }
}
