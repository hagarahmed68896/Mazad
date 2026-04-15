<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Ad;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $account_type = $request->get('account_type', 'all');
        $payment_method = $request->get('payment_method', 'all');
        $sort = $request->get('sort', 'all');

        // Statistics
        $totalRevenue = Transaction::where('status', 'completed')->sum('amount');
        $completedCount = Transaction::where('status', 'completed')->count();
        $incompleteCount = Transaction::where('status', 'failed')->count();
        $underReviewCount = Transaction::where('status', 'under_review')->count();

        $cardStats = [
            [
                'label' => 'إجمالي الإيرادات',
                'value' => number_format($totalRevenue),
                'suffix' => 'ريال',
                'is_money' => true,
                'percentage' => 4.5,
                'color' => '#10B981',
                'icon' => 'total',
                'sparkline' => [20, 35, 25, 45, 30, 55, 40, 60]
            ],
            [
                'label' => 'المدفوعات المكتملة',
                'value' => number_format($completedCount),
                'percentage' => 3.2,
                'color' => '#3B82F6',
                'icon' => 'sent',
                'sparkline' => [10, 20, 15, 30, 25, 40, 35, 50]
            ],
            [
                'label' => 'المدفوعات الغير مكتملة',
                'value' => number_format($incompleteCount),
                'percentage' => 2.1,
                'color' => '#EF4444',
                'icon' => 'warning',
                'sparkline' => [50, 40, 55, 45, 60, 50, 65, 55]
            ],
            [
                'label' => 'المدفوعات قيد المراجعة',
                'value' => number_format($underReviewCount),
                'percentage' => 1.5,
                'color' => '#64748B',
                'icon' => 'scheduled',
                'sparkline' => [30, 30, 32, 30, 31, 30, 33, 30]
            ],
        ];

        // Query Builder
        $query = Transaction::with(['user', 'ad']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($account_type !== 'all') {
            $query->whereHas('user', function($q) use ($account_type) {
                $q->where('account_type', $account_type);
            });
        }

        if ($payment_method !== 'all') {
            $query->where('payment_method', $payment_method);
        }

        // Sorting
        switch ($sort) {
            case 'name': 
                $query->join('users', 'transactions.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc')
                      ->select('transactions.*'); 
                break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            case 'newest': $query->orderBy('created_at', 'desc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $transactions = $query->paginate(10);

        return view('admin.payments.index', compact(
            'transactions', 'cardStats', 'search', 'status', 'account_type', 'payment_method', 'sort'
        ));
    }

    public function download(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $account_type = $request->get('account_type', 'all');
        $payment_method = $request->get('payment_method', 'all');

        $query = Transaction::with(['user', 'ad']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if ($status !== 'all') { $query->where('status', $status); }
        if ($account_type !== 'all') {
            $query->whereHas('user', function($q) use ($account_type) {
                $q->where('account_type', $account_type);
            });
        }
        if ($payment_method !== 'all') {
            $query->where('payment_method', $payment_method);
        }

        $transactions = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=transactions_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'رقم العملية', 'اسم المستخدم', 'نوع الحساب', 'نوع العملية', 'القيمة', 'طريقة الدفع', 'الحالة', 'التاريخ'
        ];

        $callback = function() use ($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            $statusLabels = [
                'completed' => 'مكتملة',
                'failed' => 'غير مكتملة',
                'under_review' => 'قيد المراجعة',
            ];

            $typeLabels = [
                'deposit' => 'عربون',
                'direct_purchase' => 'شراء فوري',
                'listing_fee' => 'رسوم إدراج إعلان',
                'service' => 'خدمة',
            ];

            $userTypeLabels = [
                'individual' => 'فرد',
                'company' => 'شركة',
                'representative' => 'مندوب',
                'associate' => 'شريك',
            ];

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->user->name ?? '-',
                    $userTypeLabels[$transaction->user->account_type] ?? 'غير محدد',
                    $typeLabels[$transaction->type] ?? $transaction->type,
                    $transaction->amount . ' ريال',
                    $transaction->payment_method ?? 'طريقة الدفع',
                    $statusLabels[$transaction->status] ?? $transaction->status,
                    $transaction->created_at->format('Y-m-d'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
