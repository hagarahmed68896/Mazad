<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Category;
use App\Models\User;
use App\Models\Service;

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

    public function auctionsDownload(Request $request)
    {
        $type = $request->get('type', 'electronic_auction');
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=auctions_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('اسم الإعلان'), 
            __('فئة الإعلان'), 
            __('اسم المعلن'), 
            __('نوع المعلن'), 
            __('سعر العربون'), 
            __('السعر الحالي'), 
            __('عدد المزايدات'),
        ];

        if ($type === 'hybrid_auction') {
            $columns[] = __('حالة المزايدة');
        }

        $columns[] = __('الحالة');
        $columns[] = __('التاريخ');

        $callback = function() use ($ads, $columns, $type) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
            ];

            $statusLabels = [
                'active' => 'قائم',
                'upcoming' => 'مستقبلي',
                'finished' => 'منتهي',
            ];

            $bidStatusLabels = [
                'not_started' => 'لم تبدأ المزايدة',
                'ongoing' => 'جاري',
                'postponed' => 'مؤجل',
                'sold' => 'تم البيع',
                'not_sold' => 'لم يتم البيع',
            ];

            foreach ($ads as $ad) {
                $row = [
                    $ad->title,
                    $ad->category->name ?? 'غير مصنف',
                    $ad->user->name ?? 'مستخدم غير معروف',
                    $userTypeLabels[$ad->user->account_type] ?? 'غير محدد',
                    $ad->deposit_amount,
                    $ad->price,
                    $ad->bids_count,
                ];

                if ($type === 'hybrid_auction') {
                    $row[] = $bidStatusLabels[$ad->bid_status] ?? ($ad->status === 'upcoming' ? '-' : ($ad->bid_status ?? '-'));
                }

                $row[] = $statusLabels[$ad->status] ?? $ad->status;
                $row[] = $ad->created_at->format('Y-m-d');

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function incomplete(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');

        $query = Ad::with(['user', 'category'])->where('status', 'pending');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->paginate(10);
        $categories = Category::all();

        return view('admin.ads.incomplete', compact(
            'ads', 'search', 'sort', 'category_id', 'user_type', 'categories'
        ));
    }

    public function incompleteDownload(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');

        $query = Ad::with(['user', 'category'])->where('status', 'pending');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=incomplete_ads_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('اسم الإعلان'), 
            __('فئة الإعلان'), 
            __('الوصف'),
            __('اسم المعلن'), 
            __('نوع المعلن'), 
            __('سعر العربون'), 
            __('السعر'), 
            __('التاريخ')
        ];

        $callback = function() use ($ads, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
            ];

            foreach ($ads as $ad) {
                fputcsv($file, [
                    $ad->title,
                    $ad->category->name ?? 'غير مصنف',
                    $ad->description,
                    $ad->user->name ?? 'مستخدم غير معروف',
                    $userTypeLabels[$ad->user->account_type] ?? 'غير محدد',
                    $ad->deposit_amount,
                    $ad->price,
                    $ad->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function directPurchase(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        $query = Ad::with(['user', 'category'])->where('type', 'direct_purchase');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->paginate(10);
        $categories = Category::all();

        return view('admin.ads.direct_purchase', compact(
            'ads', 'search', 'sort', 'category_id', 'user_type', 'status', 'categories'
        ));
    }

    public function directPurchaseDownload(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        $query = Ad::with(['user', 'category'])->where('type', 'direct_purchase');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=direct_purchase_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('اسم الإعلان'), 
            __('فئة الإعلان'), 
            __('الوصف'),
            __('اسم المعلن'), 
            __('نوع المعلن'), 
            __('سعر العربون'), 
            __('السعر'), 
            __('عدد السومات'),
            __('الحالة'),
            __('التاريخ')
        ];

        $callback = function() use ($ads, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
            ];

            $statusLabels = [
                'active' => 'قائم',
                'upcoming' => 'مستقبلي',
                'finished' => 'منتهي',
                'pending' => 'غير مكتمل',
            ];

            foreach ($ads as $ad) {
                fputcsv($file, [
                    $ad->title,
                    $ad->category->name ?? 'غير مصنف',
                    $ad->description,
                    $ad->user->name ?? 'مستخدم غير معروف',
                    $userTypeLabels[$ad->user->account_type] ?? 'غير محدد',
                    $ad->deposit_amount,
                    $ad->price,
                    $ad->bids_count,
                    $statusLabels[$ad->status] ?? $ad->status,
                    $ad->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function tenders(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        $query = Ad::with(['user', 'category'])->where('type', 'tender');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->paginate(10);
        $categories = Category::all();

        return view('admin.ads.tenders', compact(
            'ads', 'search', 'sort', 'category_id', 'user_type', 'status', 'categories'
        ));
    }

    public function tendersDownload(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        $query = Ad::with(['user', 'category'])->where('type', 'tender');

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $ads = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=tenders_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('اسم الإعلان'), 
            __('فئة الإعلان'), 
            __('الوصف'),
            __('اسم المعلن'), 
            __('نوع المعلن'), 
            __('سعر العربون'), 
            __('السعر الحالي'), 
            __('عدد المناقصات'),
            __('الحالة'),
            __('التاريخ')
        ];

        $callback = function() use ($ads, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
            ];

            $statusLabels = [
                'active' => 'قائم',
                'upcoming' => 'مستقبلي',
                'finished' => 'منتهي',
                'pending' => 'غير مكتمل',
            ];

            foreach ($ads as $ad) {
                fputcsv($file, [
                    $ad->title,
                    $ad->category->name ?? 'غير مصنف',
                    $ad->description,
                    $ad->user->name ?? 'مستخدم غير معروف',
                    $userTypeLabels[$ad->user->account_type] ?? 'غير محدد',
                    $ad->deposit_amount,
                    $ad->price,
                    $ad->bids_count,
                    $statusLabels[$ad->status] ?? $ad->status,
                    $ad->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function services(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');

        $query = Service::with(['user', 'category']);

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $services = $query->paginate(10);
        $categories = Category::all();

        return view('admin.ads.services', compact(
            'services', 'search', 'sort', 'category_id', 'categories'
        ));
    }

    public function servicesDownload(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');

        $query = Service::with(['user', 'category']);

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

        switch ($sort) {
            case 'name': $query->orderBy('title', 'asc'); break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $services = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=services_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('اسم الخدمة'), 
            __('فئة الخدمة'), 
            __('الوصف'),
            __('اسم المعلن'), 
            __('السعر'), 
            __('التاريخ')
        ];

        $callback = function() use ($services, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($services as $service) {
                fputcsv($file, [
                    $service->title,
                    $service->category->name ?? 'غير مصنف',
                    $service->description,
                    $service->user->name ?? 'مستخدم غير معروف',
                    $service->price,
                    $service->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
