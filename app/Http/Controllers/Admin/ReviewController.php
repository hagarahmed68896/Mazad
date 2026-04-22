<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Carbon\Carbon;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        // Top Stats
        $totalReviews = Review::count();
        $positiveReviews = Review::where('status', 'positive')->count();
        $negativeReviews = Review::where('status', 'negative')->count();

        $getGrowth = function($query) {
            $thisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
            $lastWeek = (clone $query)->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
            return $lastWeek == 0 ? ($thisWeek > 0 ? 100 : 0) : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
        };

        $totalGrowth = $getGrowth(new Review);
        $positiveGrowth = $getGrowth(Review::where('status', 'positive'));
        $negativeGrowth = $getGrowth(Review::where('status', 'negative'));

        // Filters
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $accountType = $request->get('account_type', 'all');
        $sort = $request->get('sort', 'newest');

        $query = Review::with(['reviewer', 'ratedUser', 'ad', 'order']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('reviewer', function($sq) use ($search) { $sq->where('name', 'like', "%{$search}%"); })
                  ->orWhereHas('ratedUser', function($sq) use ($search) { $sq->where('name', 'like', "%{$search}%"); })
                  ->orWhereHas('ad', function($sq) use ($search) { $sq->where('title', 'like', "%{$search}%"); })
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($accountType !== 'all') {
            $query->whereHas('reviewer', function($q) use ($accountType) {
                $q->where('account_type', $accountType);
            });
        }

        switch ($sort) {
            case 'name': 
                $query->join('users', 'reviews.reviewer_id', '=', 'users.id')
                      ->select('reviews.*')
                      ->orderBy('users.name', 'asc');
                break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            case 'rating_high': $query->orderBy('rating', 'desc'); break;
            case 'rating_low': $query->orderBy('rating', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $reviews = $query->paginate(10);

        $cardStats = [
            [
                'label' => 'إجمالي التقييمات',
                'value' => number_format($totalReviews),
                'percentage' => $totalGrowth,
                'color' => '#1A73E8',
                'icon' => 'total',
                'sparkline' => [30, 45, 35, 50, 40, 60, 55, 70]
            ],
            [
                'label' => 'التقييمات الإيجابية',
                'value' => number_format($positiveReviews),
                'percentage' => $positiveGrowth,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [20, 30, 40, 35, 50, 45, 55, 60]
            ],
            [
                'label' => 'التقييمات السلبية',
                'value' => number_format($negativeReviews),
                'percentage' => $negativeGrowth,
                'color' => '#EF4444',
                'icon' => 'default',
                'sparkline' => [10, 5, 10, 5, 15, 10, 5, 10]
            ],
        ];

        return view('admin.reviews.index', compact(
            'reviews', 'totalReviews', 'positiveReviews', 'negativeReviews',
            'search', 'status', 'accountType', 'sort', 'cardStats'
        ));
    }

    public function download(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $accountType = $request->get('account_type', 'all');
        $sort = $request->get('sort', 'newest');

        $query = Review::with(['reviewer', 'ratedUser', 'ad', 'order']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('reviewer', function($sq) use ($search) { $sq->where('name', 'like', "%{$search}%"); })
                  ->orWhereHas('ratedUser', function($sq) use ($search) { $sq->where('name', 'like', "%{$search}%"); })
                  ->orWhereHas('ad', function($sq) use ($search) { $sq->where('title', 'like', "%{$search}%"); })
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($accountType !== 'all') {
            $query->whereHas('reviewer', function($q) use ($accountType) {
                $q->where('account_type', $accountType);
            });
        }

        switch ($sort) {
            case 'name': 
                $query->join('users', 'reviews.reviewer_id', '=', 'users.id')
                      ->select('reviews.*')
                      ->orderBy('users.name', 'asc');
                break;
            case 'oldest': $query->orderBy('created_at', 'asc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $reviews = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=reviews_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            '#', 'اسم المستخدم', 'نوع الحساب', 'رقم الطلب', 'اسم الإعلان', 'اسم المعلن', 'نوع المعلن', 'التقييم', 'التعليق', 'الحالة', 'التاريخ'
        ];

        $callback = function() use ($reviews, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $types = ['individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب', 'partner' => 'شريك'];

            foreach ($reviews as $index => $review) {
                $row = [
                    $index + 1,
                    $review->reviewer->name ?? '---',
                    $types[$review->reviewer->account_type ?? ''] ?? $review->reviewer->account_type ?? '---',
                    $review->order_id ?? '---',
                    $review->ad->title ?? '---',
                    $review->ratedUser->name ?? '---',
                    $types[$review->ratedUser->account_type ?? ''] ?? $review->ratedUser->account_type ?? '---',
                    $review->rating,
                    $review->comment,
                    $review->status == 'positive' ? 'تقييم إيجابي' : 'تقييم سلبي',
                    $review->created_at->format('Y-m-d')
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
