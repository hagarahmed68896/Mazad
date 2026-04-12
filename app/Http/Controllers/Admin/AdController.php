<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Category;
use App\Models\User;

class AdController extends Controller
{
    public function auctions(Request $request)
    {
        $type = $request->get('type', 'electronic_auction'); // 'electronic_auction' or 'hybrid_auction'
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        // Summary Stats
        $totalAuctions = Ad::whereIn('type', ['electronic_auction', 'hybrid_auction'])->count();
        $totalElectronic = Ad::where('type', 'electronic_auction')->count();
        $totalHybrid = Ad::where('type', 'hybrid_auction')->count();

        // Growth Calculations (Dynamic Logic)
        $getGrowth = function($query) {
            $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
            $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        $totalGrowth = $getGrowth(Ad::whereIn('type', ['electronic_auction', 'hybrid_auction']));
        $electronicGrowth = $getGrowth(Ad::where('type', 'electronic_auction'));
        $hybridGrowth = $getGrowth(Ad::where('type', 'hybrid_auction'));

        $cardStats = [
            [
                'label' => 'إجمالي المزادات',
                'value' => number_format($totalAuctions),
                'percentage' => $totalGrowth,
                'color' => '#1A73E8',
                'icon' => 'total',
                'sparkline' => [20, 35, 25, 45, 30, 55, 40, 60]
            ],
            [
                'label' => 'إجمالي المزادات الإلكترونية',
                'value' => number_format($totalElectronic),
                'percentage' => $electronicGrowth,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [15, 25, 20, 35, 40, 30, 45, 50]
            ],
            [
                'label' => 'إجمالي مزادات الهجين',
                'value' => number_format($totalHybrid),
                'percentage' => $hybridGrowth,
                'color' => '#8B5CF6',
                'icon' => 'scheduled',
                'sparkline' => [10, 15, 12, 20, 18, 25, 22, 30]
            ],
        ];

        // Query Builder
        $query = Ad::with(['user', 'category'])->where('type', $type);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($category_id !== 'all') {
            $query->where('category_id', $category_id);
        }

        if ($user_type !== 'all') {
            $query->whereHas('user', function($uq) use ($user_type) {
                $uq->where('account_type', $user_type);
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Sorting
        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->paginate(10);
        $categories = Category::all();

        return view('admin.ads.auctions', compact(
            'ads', 'cardStats', 'type', 'search', 'sort', 'category_id', 'user_type', 'status', 'categories'
        ));
    }
}
