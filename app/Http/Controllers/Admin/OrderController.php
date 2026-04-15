<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Category;
use App\Models\Ad;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $ad_type = $request->get('ad_type', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        // Statistics
        $totalPurchases = Order::count();
        $inProgressPurchases = Order::whereIn('status', ['pending_payment', 'receiving', 'paid'])->count();
        $completedPurchases = Order::where('status', 'received')->count();

        // Growth Mock values (Percentage)
        $cardStats = [
            [
                'label' => 'إجمالي المشتريات',
                'value' => number_format($totalPurchases),
                'percentage' => 4.5,
                'color' => '#1A73E8',
                'icon' => 'total',
                'sparkline' => [20, 35, 25, 45, 30, 55, 40, 60]
            ],
            [
                'label' => 'المشتريات قيد التنفيذ',
                'value' => number_format($inProgressPurchases),
                'percentage' => 3.2,
                'color' => '#EAB308',
                'icon' => 'scheduled',
                'sparkline' => [40, 30, 45, 35, 50, 40, 55, 45]
            ],
            [
                'label' => 'المشتريات المكتملة',
                'value' => number_format($completedPurchases),
                'percentage' => 6.1,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [25, 40, 35, 50, 45, 60, 55, 70]
            ],
        ];

        // Query Builder
        $query = Order::with(['ad.category', 'buyer', 'seller']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('ad', function($aq) use ($search) {
                      $aq->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('buyer', function($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($category_id !== 'all') {
            $query->whereHas('ad', function($aq) use ($category_id) {
                $aq->where('category_id', $category_id);
            });
        }

        if ($ad_type !== 'all') {
            $query->whereHas('ad', function($aq) use ($ad_type) {
                $aq->where('type', $ad_type);
            });
        }

        if ($user_type !== 'all') {
            $query->whereHas('seller', function($sq) use ($user_type) {
                $sq->where('account_type', $user_type);
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Sorting
        switch ($sort) {
            case 'name': 
                $query->whereHas('ad', function($aq) {
                    $aq->orderBy('title', 'asc');
                });
                break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $orders = $query->paginate(10);
        $categories = Category::all();

        return view('admin.purchases.index', compact(
            'orders', 'cardStats', 'search', 'sort', 'category_id', 'ad_type', 'user_type', 'status', 'categories'
        ));
    }

    public function download(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');
        $category_id = $request->get('category_id', 'all');
        $ad_type = $request->get('ad_type', 'all');
        $user_type = $request->get('user_type', 'all');
        $status = $request->get('status', 'all');

        $query = Order::with(['ad.category', 'buyer', 'seller']);

        // ... repeat filters if necessary or consolidate logic ...
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('ad', function($aq) use ($search) {
                      $aq->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('buyer', function($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if ($category_id !== 'all') { $query->whereHas('ad', function($aq) use ($category_id) { $aq->where('category_id', $category_id); }); }
        if ($ad_type !== 'all') { $query->whereHas('ad', function($aq) use ($ad_type) { $aq->where('type', $ad_type); }); }
        if ($user_type !== 'all') { $query->whereHas('seller', function($sq) use ($user_type) { $sq->where('account_type', $user_type); }); }
        if ($status !== 'all') { $query->where('status', $status); }

        switch ($sort) {
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $orders = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=purchases_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('رقم الطلب'), 
            __('اسم الإعلان'), 
            __('فئة الإعلان'), 
            __('اسم المستخدم'), 
            __('اسم المعلن'), 
            __('نوع المعلن'), 
            __('السعر النهائي'), 
            __('الحالة'),
            __('التاريخ'),
            __('كود التسليم')
        ];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            $statusLabels = [
                'received' => 'تم الاستلام',
                'receiving' => 'قيد التسليم',
                'pending_payment' => 'قيد الدفع',
                'paid' => 'تم الدفع',
            ];

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
            ];

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->ad->title ?? '-',
                    $order->ad->category->name ?? '-',
                    $order->buyer->name ?? '-',
                    $order->seller->name ?? '-',
                    $userTypeLabels[$order->seller->account_type] ?? 'غير محدد',
                    $order->final_price,
                    $statusLabels[$order->status] ?? $order->status,
                    $order->created_at->format('Y-m-d'),
                    $order->delivery_code
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
