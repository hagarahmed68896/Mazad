<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function individuals(Request $request)
    {
        // Top Stats
        $totalIndividuals = User::where('account_type', 'individual')->count();
        $activeIndividuals = User::where('account_type', 'individual')->where('status', 'active')->count();
        $blockedIndividuals = User::where('account_type', 'individual')->where('status', 'banned')->count();

        // Growth Calculations (This Week vs Last Week Registrations)
        $getGrowth = function($query) {
            $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
            $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        $totalGrowth = $getGrowth(User::where('account_type', 'individual'));
        $activeGrowth = $getGrowth(User::where('account_type', 'individual')->where('status', 'active'));
        $blockedGrowth = $getGrowth(User::where('account_type', 'individual')->where('status', 'banned'));

        // Search Filter
        $search = $request->get('search');
        
        $query = User::where('account_type', 'individual');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination setup 
        $users = $query->latest()->paginate(10);

        $cardStats = [
            [
                'label' => 'إجمالي المستخدمين الأفراد',
                'value' => number_format($totalIndividuals),
                'percentage' => $totalGrowth,
                'color' => '#1A73E8',
                'icon' => 'total',
                'sparkline' => [10, 25, 15, 35, 45, 20, 50, 45]
            ],
            [
                'label' => 'الحسابات النشطة',
                'value' => number_format($activeIndividuals),
                'percentage' => $activeGrowth,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [20, 15, 30, 25, 45, 35, 40, 45]
            ],
            [
                'label' => 'الحسابات المحظورة',
                'value' => number_format($blockedIndividuals),
                'percentage' => $blockedGrowth,
                'color' => '#EF4444',
                'icon' => 'default',
                'sparkline' => [5, 15, 5, 20, 10, 25, 10, 15]
            ],
        ];

        return view('admin.accounts.individuals', compact(
            'users', 'totalIndividuals', 'activeIndividuals', 'blockedIndividuals', 
            'totalGrowth', 'activeGrowth', 'blockedGrowth', 'search', 'cardStats'
        ));
    }

    public function create() { return back(); }
    public function show($id) { return back(); }
    public function edit($id) { return back(); }
    public function destroy($id) { return back(); }
    public function bulkDestroy(Request $request) { 
        return response()->json(['success' => true]); 
    }
    public function download(Request $request) { return back(); }
}
