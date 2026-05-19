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

    public function reply(Request $request, $id)
    {
        if (!Auth::check()) {
            return back()->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $review = Review::findOrFail($id);

        if ($review->rated_user_id !== Auth::id()) {
            return back()->with('error', 'غير مصرح لك بالرد على هذا التقييم.');
        }

        $review->update([
            'reply' => $request->reply,
        ]);

        return back()->with('success', 'تم إضافة ردك بنجاح.');
    }

    public function toggleVisibility(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً.'], 401);
        }

        $review = Review::findOrFail($id);

        if ($review->rated_user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذا التقييم.'], 403);
        }

        $review->update([
            'is_visible' => !$review->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'is_visible' => $review->is_visible,
            'message' => $review->is_visible ? 'تم نشر التقييم بنجاح وهو الآن مرئي للجميع.' : 'تم إخفاء التقييم بنجاح.',
        ]);
    }
}
