<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialSettlement;

class FinancialSettlementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'all');
        $advertiser_type = $request->get('advertiser_type', 'all');
        $payment_method = $request->get('payment_method', 'all');

        $query = FinancialSettlement::with(['user', 'ad', 'serviceOrder.service', 'serviceOrder.ad']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('settlement_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ad', function($aq) use ($search) {
                      $aq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($advertiser_type !== 'all') {
            $query->whereHas('user', function($q) use ($advertiser_type) {
                $q->where('account_type', $advertiser_type);
            });
        }

        if ($payment_method !== 'all') {
            $query->where('payment_method', $payment_method);
        }

        switch ($sort) {
            case 'name':
                $query->join('users', 'financial_settlements.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc')
                      ->select('financial_settlements.*');
                break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            case 'newest': $query->orderBy('created_at', 'desc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $settlements = $query->paginate(10);
        
        // Fake data based on UI screenshot
        $totalPaymentsCount = 900000;
        $totalSettlementsCount = 900000;

        $cardStats = [
            [
                'label' => 'إجمالي التسويات المالية',
                'value' => number_format($totalSettlementsCount),
                'is_money' => true,
                'percentage' => 4.0,
                'color' => '#10B981',
                'icon' => 'total',
                'sparkline' => [20, 35, 25, 45, 30, 55, 40, 60]
            ],
            [
                'label' => 'إجمالي المدفوعات',
                'value' => number_format($totalPaymentsCount),
                'is_money' => true,
                'percentage' => 5.12,
                'color' => '#10B981',
                'icon' => 'total',
                'sparkline' => [10, 20, 15, 30, 25, 40, 35, 50]
            ],
        ];

        return view('admin.payments.settlements.index', compact(
            'settlements', 'search', 'sort', 'advertiser_type', 'payment_method',
            'cardStats'
        ));
    }

    public function download(Request $request)
    {
        $search = $request->get('search');
        $advertiser_type = $request->get('advertiser_type', 'all');
        $payment_method = $request->get('payment_method', 'all');

        $query = FinancialSettlement::with(['user', 'ad', 'serviceOrder.service', 'serviceOrder.ad']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('settlement_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ad', function($aq) use ($search) {
                      $aq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($advertiser_type !== 'all') {
            $query->whereHas('user', function($q) use ($advertiser_type) {
                $q->where('account_type', $advertiser_type);
            });
        }

        if ($payment_method !== 'all') {
            $query->where('payment_method', $payment_method);
        }

        $settlements = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=settlements_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'رقم التسوية', 'اسم الإعلان', 'اسم المعلن', 'نوع المعلن', 'إجمالي القيمة', 'طريقة الدفع', 'الحالة', 'التاريخ'
        ];

        $callback = function() use ($settlements, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            $statusLabels = [
                'completed' => 'محولة',
                'transferred' => 'محولة',
                'pending' => 'معلقة',
            ];

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
                'associate' => 'شريك',
            ];

            foreach ($settlements as $settlement) {
                fputcsv($file, [
                    $settlement->settlement_number,
                    $settlement->ad->title ?? '-',
                    $settlement->user->name ?? '-',
                    $userTypeLabels[$settlement->user->account_type ?? 'individual'] ?? 'فرد',
                    $settlement->total_amount . ' ريال',
                    $settlement->payment_method ?? 'طريقة الدفع',
                    $statusLabels[$settlement->status] ?? 'معلقة',
                    $settlement->created_at ? $settlement->created_at->format('Y-m-d') : '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function transfer(FinancialSettlement $settlement)
    {
        if ($settlement->status !== 'transferred' && $settlement->status !== 'completed') {
            $settlement->update(['status' => 'transferred']);
            
            // Release the related service order report to the buyer
            if ($settlement->service_order_id) {
                $settlement->serviceOrder()->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
            
            return back()->with('success', 'تم تغيير حالة التسوية إلى محولة بنجاح وتم تسليم التقرير للمشتري');
        }
        
        return back()->with('info', 'التسوية محولة مسبقاً');
    }
}
