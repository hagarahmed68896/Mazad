<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return back()->with('error', 'يجب تسجيل الدخول أولاً لإضافة تقييم.');
        }

        $request->validate([
            'rated_user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'reviewer_id' => Auth::id(),
            'rated_user_id' => $request->rated_user_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => $request->rating >= 4 ? 'positive' : 'negative',
        ]);

        return back()->with('success', 'تم إضافة تقييمك بنجاح.');
    }
}
