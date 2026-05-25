<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Rate/Review a user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rated_user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Prevent rating oneself
        if ((int) $request->rated_user_id === auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تقييم نفسك.'
            ], 400);
        }

        $review = Review::create([
            'reviewer_id' => auth('sanctum')->id(),
            'rated_user_id' => $request->rated_user_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => $request->rating >= 4 ? 'positive' : 'negative',
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة تقييمك بنجاح.',
            'data' => $review
        ], 201);
    }

    /**
     * Reply to a review left on your profile.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $review = Review::findOrFail($id);

        if ($review->rated_user_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالرد على هذا التقييم.'
            ], 403);
        }

        $review->update([
            'reply' => $request->reply,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة ردك بنجاح.',
            'data' => $review
        ]);
    }

    /**
     * Toggle visibility (show/hide) of a review left on your profile.
     */
    public function toggleVisibility($id)
    {
        $review = Review::findOrFail($id);

        if ($review->rated_user_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا التقييم.'
            ], 403);
        }

        $review->update([
            'is_visible' => !$review->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'is_visible' => $review->is_visible,
            'message' => $review->is_visible ? 'تم نشر التقييم بنجاح وهو الآن مرئي للجميع.' : 'تم إخفاء التقييم بنجاح.',
            'data' => $review
        ]);
    }

    /**
     * Get paginated list of reviews received by the authenticated user.
     */
    public function myReviews()
    {
        $userId = auth('sanctum')->id();
        
        $reviews = Review::with('reviewer:id,name,avatar')
            ->where('rated_user_id', $userId)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب التقييمات بنجاح.',
            'data' => $this->formatPagination($reviews)
        ]);
    }
}
