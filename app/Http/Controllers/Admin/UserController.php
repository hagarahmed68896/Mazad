<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;

class UserController extends Controller
{
    public function blockedAccounts(Request $request)
    {
        $accountType = $request->get('account_type', 'individual');
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');

        $query = User::where('status', 'banned')->where('account_type', $accountType)
            ->withCount(['ads', 'purchases as purchases_count', 'sales as sales_count', 'services']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'name': $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->paginate(10);
        $users->appends(['account_type' => $accountType, 'search' => $search, 'sort' => $sort]);

        return view('admin.accounts.blocked', compact('users', 'accountType', 'search', 'sort'));
    }

    public function partners(Request $request)
    {
        $totalPartners  = User::where('account_type', 'representative')->count();
        $activePartners = User::where('account_type', 'representative')->where('status', 'active')->count();
        $blockedPartners= User::where('account_type', 'representative')->where('status', 'banned')->count();

        $getGrowth = function($query) {
            $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
            $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        $totalGrowth   = $getGrowth(User::where('account_type', 'representative'));
        $activeGrowth  = $getGrowth(User::where('account_type', 'representative')->where('status', 'active'));
        $blockedGrowth = $getGrowth(User::where('account_type', 'representative')->where('status', 'banned'));

        $search = $request->get('search');
        $sort   = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');

        $query = User::where('account_type', 'representative');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name':   $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default:       $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->paginate(10);

        $cardStats = [
            [
                'label'      => 'إجمالي المندوبين',
                'value'      => number_format($totalPartners),
                'percentage' => $totalGrowth,
                'color'      => '#1A73E8',
                'icon'       => 'total',
                'sparkline'  => [15, 30, 20, 40, 50, 25, 60, 55]
            ],
            [
                'label'      => 'الحسابات النشطة',
                'value'      => number_format($activePartners),
                'percentage' => $activeGrowth,
                'color'      => '#10B981',
                'icon'       => 'sent',
                'sparkline'  => [25, 20, 35, 30, 50, 40, 45, 50]
            ],
            [
                'label'      => 'الحسابات المحظورة',
                'value'      => number_format($blockedPartners),
                'percentage' => $blockedGrowth,
                'color'      => '#EF4444',
                'icon'       => 'default',
                'sparkline'  => [10, 20, 10, 25, 15, 30, 15, 20]
            ],
        ];

        return view('admin.accounts.partners', compact(
            'users', 'totalPartners', 'activePartners', 'blockedPartners',
            'totalGrowth', 'activeGrowth', 'blockedGrowth', 'search', 'sort', 'status', 'cardStats'
        ));
    }

    public function companies(Request $request)
    {
        // Top Stats for Companies
        $totalCompanies = User::where('account_type', 'company')->count();
        $activeCompanies = User::where('account_type', 'company')->where('status', 'active')->count();
        $blockedCompanies = User::where('account_type', 'company')->where('status', 'banned')->count();

        // Growth Calculations
        $getGrowth = function($query) {
            $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
            $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        $totalGrowth = $getGrowth(User::where('account_type', 'company'));
        $activeGrowth = $getGrowth(User::where('account_type', 'company')->where('status', 'active'));
        $blockedGrowth = $getGrowth(User::where('account_type', 'company')->where('status', 'banned'));

        // Filters
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');
        
        $query = User::where('account_type', 'company');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('commercial_register', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name': $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->paginate(10);

        $cardStats = [
            [
                'label' => 'إجمالي المستخدمين الشركات',
                'value' => number_format($totalCompanies),
                'percentage' => $totalGrowth,
                'color' => '#1A73E8',
                'icon' => 'total',
                'sparkline' => [15, 30, 20, 40, 50, 25, 60, 55]
            ],
            [
                'label' => 'الحسابات النشطة',
                'value' => number_format($activeCompanies),
                'percentage' => $activeGrowth,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [25, 20, 35, 30, 50, 40, 45, 50]
            ],
            [
                'label' => 'الحسابات المحظورة',
                'value' => number_format($blockedCompanies),
                'percentage' => $blockedGrowth,
                'color' => '#EF4444',
                'icon' => 'default',
                'sparkline' => [10, 20, 10, 25, 15, 30, 15, 20]
            ],
        ];

        return view('admin.accounts.companies', compact(
            'users', 'totalCompanies', 'activeCompanies', 'blockedCompanies', 
            'totalGrowth', 'activeGrowth', 'blockedGrowth', 'search', 'sort', 'status', 'cardStats'
        ));
    }

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

        // Filters setup
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');
        
        $query = User::where('account_type', 'individual');
        
        // Search Filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Sorting Logic
        switch ($sort) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination setup 
        $users = $query->paginate(10);

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
            'totalGrowth', 'activeGrowth', 'blockedGrowth', 'search', 'sort', 'status', 'cardStats'
        ));
    }

    public function create() { return back(); }
    public function show(Request $request, $id)
    {
        \Carbon\Carbon::setLocale('ar');
        $item = User::findOrFail($id);
        
        // Chart Type
        $chartType = $request->get('chart_type', 'weekly');

        // Chart Data Logic
        $performanceData = [];
        if ($chartType === 'weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $performanceData[] = [
                    'label' => $date->translatedFormat('l'),
                    'count' => $item->purchases()->whereDate('created_at', $date->toDateString())->count() 
                             + $item->sales()->whereDate('created_at', $date->toDateString())->count()
                ];
            }
        } elseif ($chartType === 'yearly') {
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $performanceData[] = [
                    'label' => $month->translatedFormat('M Y'),
                    'count' => $item->purchases()->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count() 
                             + $item->sales()->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count()
                ];
            }
        } else { // monthly
            for ($i = 3; $i >= 0; $i--) {
                $week = now()->subWeeks($i);
                $performanceData[] = [
                    'label' => 'أسبوع ' . (4 - $i),
                    'count' => $item->purchases()->whereBetween('created_at', [$week->startOfWeek(), $week->endOfWeek()])->count() 
                             + $item->sales()->whereBetween('created_at', [$week->startOfWeek(), $week->endOfWeek()])->count()
                ];
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['performanceData' => $performanceData, 'chartType' => $chartType]);
        }

        // Helper for Growth Calculations (This Week vs Last Week)
        $getMetricGrowth = function($query, $isSum = false, $field = 'final_price') {
            if ($isSum) {
                $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->sum($field);
                $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->sum($field);
            } else {
                $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
                $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            }
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        // Helper for Sparklines (Last 7 Days Trend)
        $getRealSparkline = function($query, $isSum = false, $field = 'final_price') {
            $trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                if ($isSum) {
                    $trend[] = (clone $query)->whereDate('created_at', $date->toDateString())->sum($field);
                } else {
                    $trend[] = (clone $query)->whereDate('created_at', $date->toDateString())->count();
                }
            }
            return $trend;
        };

        // Performance Score (Example: Average Review Rating * 20 to get 100%)
        $avgRating = $item->reviewsReceived()->avg('rating') ?? 4.5; // Default 4.5 if no reviews
        $performanceScore = round($avgRating * 20);

        $stats = [
            'total_ads' => $item->ads()->count(),
            'total_sales' => $item->sales()->count(),
            'total_purchases' => $item->purchases()->count(),
            'total_revenue' => $item->sales()->sum('final_price'),
            'performance_score' => $performanceScore,
            'growths' => [
                'ads' => $getMetricGrowth($item->ads()),
                'sales' => $getMetricGrowth($item->sales()),
                'purchases' => $getMetricGrowth($item->purchases()),
                'revenue' => $getMetricGrowth($item->sales(), true),
            ],
            'sparklines' => [
                'ads' => $getRealSparkline($item->ads()),
                'sales' => $getRealSparkline($item->sales()),
                'purchases' => $getRealSparkline($item->purchases()),
                'revenue' => $getRealSparkline($item->sales(), true),
            ]
        ];

        // Recent Orders
        $orders = Order::where('buyer_id', $item->id)->orWhere('seller_id', $item->id)
            ->with(['ad.category', 'buyer', 'seller'])
            ->latest()
            ->get();

        // Payments (Transactions)
        $payments = $item->transactions()->latest()->get();
        
        // Invoices
        $invoices = $item->transactions()->with('ad')->latest()->get();
        $sales = $item->sales()->with('ad')->latest()->get();
        $reviews = $item->reviewsReceived()->latest()->get();

        $stats['order_count'] = $orders->count();
        $stats['total_payments'] = $invoices->sum('amount');

        if ($request->ajax()) {
            return response()->json([
                'performanceData' => $performanceData
            ]);
        }

        return view('admin.accounts.show', compact('item', 'stats', 'chartType', 'performanceData', 'invoices', 'orders', 'sales', 'reviews'));
    }

    public function toggleBlock($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status == 'active' ? 'banned' : 'active';
        $user->save();

        return back()->with('success', 'تم تحديث حالة المستخدم بنجاح');
    }

    public function edit($id) { return back(); }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $accountType = $user->account_type;
        $user->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if (!str_contains(url()->previous(), "/accounts/individuals/{$id}") && 
            !str_contains(url()->previous(), "/accounts/companies/{$id}") && 
            !str_contains(url()->previous(), "/accounts/partners/{$id}") && 
            !str_contains(url()->previous(), "/accounts/associates/{$id}")) {
            return back()->with('success', 'تم حذف الحساب بنجاح.');
        }

        $redirectRoute = match($accountType) {
            'company' => 'admin.accounts.companies',
            'representative' => 'admin.accounts.partners',
            'partner' => 'admin.accounts.associates',
            default => 'admin.accounts.individuals',
        };

        return redirect()->route($redirectRoute)->with('success', 'تم حذف الحساب بنجاح.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->selected ?? [];
        if (!empty($ids)) {
            User::whereIn('id', $ids)->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'تم حذف الحسابات المحددة بنجاح.');
    }

    public function bulkToggleBlock(Request $request)
    {
        $status = $request->status === 'active' ? 'active' : 'banned';
        User::whereIn('id', $request->selected ?? [])
            ->update(['status' => $status]);

        return back()->with('success', 'تم تحديث حالة المستخدمين بنجاح');
    }

    public function sendOrderCode($id)
    {
        $order = Order::findOrFail($id);
        
        // Generate code if missing
        if (!$order->delivery_code) {
            $order->delivery_code = rand(100000, 999999);
            $order->save();
        }

        // Create a notification for the buyer
        \App\Models\Notification::create([
            'user_id' => $order->buyer_id,
            'title' => 'كود التسليم',
            'content' => "كود التسليم الخاص بطلبك رقم #{$order->order_number} هو: {$order->delivery_code}",
            'type' => 'notification',
            'status' => 'sent'
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'تم إرسال الكود بنجاح إلى جوال المشتري',
            'delivery_code' => $order->delivery_code
        ]);
    }

    public function companiesDownload(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');

        $query = User::where('account_type', 'company');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('commercial_register', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name': $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=companies_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('المعرّف'), 
            __('الاسم'), 
            __('اسم الشركة'),
            __('السجل التجاري'),
            __('البريد الإلكتروني'), 
            __('رقم الجوال'), 
            __('الحالة'), 
            __('تاريخ الإنشاء')
        ];

        $callback = function() use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['ID']           = $user->id;
                $row['Name']         = $user->name;
                $row['Company Name'] = $user->company_name;
                $row['Commercial Register'] = $user->commercial_register;
                $row['Email']        = $user->email;
                $row['Phone']        = $user->phone;
                $row['Status']       = $user->status;
                $row['Created At']   = $user->created_at;

                fputcsv($file, array_values($row));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function download(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');

        $query = User::where('account_type', 'individual');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $users = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=individuals_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('المعرّف'), 
            __('الاسم'), 
            __('البريد الإلكتروني'), 
            __('رقم الجوال'), 
            __('الحالة'), 
            __('نوع الحساب'), 
            __('تاريخ الإنشاء')
        ];

        $callback = function() use ($users, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['ID']           = $user->id;
                $row['Name']         = $user->name;
                $row['Email']        = $user->email;
                $row['Phone']        = $user->phone;
                $row['Status']       = $user->status;
                $row['Account Type'] = $user->account_type;
                $row['Created At']   = $user->created_at;

                fputcsv($file, array_values($row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function partnersDownload(Request $request)
    {
        $search = $request->get('search');
        $sort   = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');

        $query = User::where('account_type', 'representative');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name':   $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default:       $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=partners_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('المعرّف'),
            __('الاسم'),
            __('البريد الإلكتروني'),
            __('رقم الجوال'),
            __('الحالة'),
            __('تاريخ الإنشاء')
        ];

        $callback = function() use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['ID']         = $user->id;
                $row['Name']       = $user->name;
                $row['Email']      = $user->email;
                $row['Phone']      = $user->phone;
                $row['Status']     = $user->status;
                $row['Created At'] = $user->created_at;
                fputcsv($file, array_values($row));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function associates(Request $request)
    {
        $totalAssociates  = User::where('account_type', 'partner')->count();
        $activeAssociates = User::where('account_type', 'partner')->where('status', 'active')->count();
        $blockedAssociates= User::where('account_type', 'partner')->where('status', 'banned')->count();

        $getGrowth = function($query) {
            $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
            $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        $totalGrowth   = $getGrowth(User::where('account_type', 'partner'));
        $activeGrowth  = $getGrowth(User::where('account_type', 'partner')->where('status', 'active'));
        $blockedGrowth = $getGrowth(User::where('account_type', 'partner')->where('status', 'banned'));

        $search = $request->get('search');
        $sort   = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');

        $query = User::withCount('services')->where('account_type', 'partner');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name':   $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default:       $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->paginate(10);

        $cardStats = [
            [
                'label'      => 'إجمالي الشركاء',
                'value'      => number_format($totalAssociates),
                'percentage' => $totalGrowth,
                'color'      => '#1A73E8',
                'icon'       => 'total',
                'sparkline'  => [15, 30, 20, 40, 50, 25, 60, 55]
            ],
            [
                'label'      => 'الحسابات النشطة',
                'value'      => number_format($activeAssociates),
                'percentage' => $activeGrowth,
                'color'      => '#10B981',
                'icon'       => 'sent',
                'sparkline'  => [25, 20, 35, 30, 50, 40, 45, 50]
            ],
            [
                'label'      => 'الحسابات المحظورة',
                'value'      => number_format($blockedAssociates),
                'percentage' => $blockedGrowth,
                'color'      => '#EF4444',
                'icon'       => 'default',
                'sparkline'  => [10, 20, 10, 25, 15, 30, 15, 20]
            ],
        ];

        return view('admin.accounts.associates', compact(
            'users', 'totalAssociates', 'activeAssociates', 'blockedAssociates',
            'totalGrowth', 'activeGrowth', 'blockedGrowth', 'search', 'sort', 'status', 'cardStats'
        ));
    }

    public function associatesDownload(Request $request)
    {
        $search = $request->get('search');
        $sort   = $request->get('sort', 'newest');
        $status = $request->get('status', 'all');

        $query = User::withCount('services')->where('account_type', 'partner');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'name':   $query->orderBy('name', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default:       $query->orderBy('created_at', 'desc'); break;
        }

        $users = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=associates_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('المعرّف'),
            __('الاسم'),
            __('البريد الإلكتروني'),
            __('رقم الجوال'),
            __('الخدمات'),
            __('الحالة'),
            __('تاريخ الإنشاء')
        ];

        $callback = function() use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['ID']         = $user->id;
                $row['Name']       = $user->name;
                $row['Email']      = $user->email;
                $row['Phone']      = $user->phone;
                $row['Services']   = $user->services_count ?? 0;
                $row['Status']     = $user->status;
                $row['Created At'] = $user->created_at;
                fputcsv($file, array_values($row));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
