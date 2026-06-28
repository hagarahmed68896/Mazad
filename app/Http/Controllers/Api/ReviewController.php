<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Rate/Review a user or an ad.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ad_id' => 'nullable|exists:ads,id',
            'rated_user_id' => 'required_without:ad_id|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $adId = $request->ad_id;
        $ratedUserId = $request->rated_user_id;

        if ($adId) {
            $ad = \App\Models\Ad::findOrFail($adId);
            $ratedUserId = $ad->user_id;

            // Prevent rating own ad
            if ($ad->user_id === auth('sanctum')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك تقييم إعلانك الخاص.'
                ], 400);
            }
        } else {
            // Prevent rating oneself
            if ((int) $ratedUserId === auth('sanctum')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك تقييم نفسك.'
                ], 400);
            }
        }

        $review = Review::create([
            'reviewer_id' => auth('sanctum')->id(),
            'rated_user_id' => $ratedUserId,
            'ad_id' => $adId,
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
        
        $reviews = Review::with(['ratedUser:id,name,avatar', 'ad:id,title'])
            ->where('reviewer_id', $userId)
            ->latest()
            ->paginate(10);

        // Format clean response collection
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'reviewer_id' => $review->reviewer_id,
                'rated_user_id' => $review->rated_user_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reply' => $review->reply,
                'created_at' => $review->created_at,
                'created_at_human' => $review->created_at ? $review->created_at->diffForHumans() : null,
                'ad' => $review->ad ? [
                    'id' => $review->ad->id,
                    'title' => $review->ad->title
                ] : null,
                'rated_user' => $review->ratedUser ? [
                    'id' => $review->ratedUser->id,
                    'name' => $review->ratedUser->name,
                    'avatar_url' => $review->ratedUser->avatar ? (\Illuminate\Support\Str::startsWith($review->ratedUser->avatar, 'http') ? $review->ratedUser->avatar : url('/' . ltrim($review->ratedUser->avatar, '/'))) : null,
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب التقييمات بنجاح.',
            'data' => $this->formatPagination($reviews)
        ]);
    }

    /**
     * Get reviews and rating breakdown for a specific user ID.
     */
    public function userReviews(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        // Calculate distribution and average based on all visible reviews
        $allReviews = Review::where('rated_user_id', $user->id)
            ->where('is_visible', true)
            ->get();

        $totalReviews = $allReviews->count();
        $avgRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0.0;

        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $allReviews->where('rating', $i)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            $ratingDistribution[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        // Fetch paginated reviews
        $reviews = Review::with('reviewer:id,name,avatar')
            ->where('rated_user_id', $user->id)
            ->where('is_visible', true)
            ->latest()
            ->paginate(10);

        // Add human-readable timestamps and clean avatar URLs
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reply' => $review->reply,
                'created_at' => $review->created_at,
                'created_at_human' => $review->created_at ? $review->created_at->diffForHumans() : null,
                'reviewer' => $review->reviewer ? [
                    'id' => $review->reviewer->id,
                    'name' => $review->reviewer->name,
                    'avatar_url' => $review->reviewer->avatar ? (\Illuminate\Support\Str::startsWith($review->reviewer->avatar, 'http') ? $review->reviewer->avatar : url('/' . ltrim($review->reviewer->avatar, '/'))) : null,
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب التقييمات بنجاح.',
            'data' => [
                'average_rating' => $avgRating,
                'total_reviews' => $totalReviews,
                'rating_distribution' => $ratingDistribution,
                'reviews_info' => $this->formatPagination($reviews)
            ]
        ]);
    }

    /**
     * Get reviews and rating breakdown for a specific ad ID.
     */
    public function adReviews(Request $request, $id)
    {
        $ad = \App\Models\Ad::findOrFail($id);

        // Calculate distribution and average based on all visible reviews for this ad
        $allReviews = Review::where('ad_id', $ad->id)
            ->where('is_visible', true)
            ->get();

        $totalReviews = $allReviews->count();
        $avgRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0.0;

        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $allReviews->where('rating', $i)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            $ratingDistribution[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        // Fetch paginated reviews
        $reviews = Review::with('reviewer:id,name,avatar')
            ->where('ad_id', $ad->id)
            ->where('is_visible', true)
            ->latest()
            ->paginate(10);

        // Add human-readable timestamps and clean avatar URLs
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reply' => $review->reply,
                'created_at' => $review->created_at,
                'created_at_human' => $review->created_at ? $review->created_at->diffForHumans() : null,
                'reviewer' => $review->reviewer ? [
                    'id' => $review->reviewer->id,
                    'name' => $review->reviewer->name,
                    'avatar_url' => $review->reviewer->avatar ? (\Illuminate\Support\Str::startsWith($review->reviewer->avatar, 'http') ? $review->reviewer->avatar : url('/' . ltrim($review->reviewer->avatar, '/'))) : null,
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تقييمات الإعلان بنجاح.',
            'data' => [
                'average_rating' => $avgRating,
                'total_reviews' => $totalReviews,
                'rating_distribution' => $ratingDistribution,
                'reviews_info' => $this->formatPagination($reviews)
            ]
        ]);
    }

    /**
     * Update an existing review via API.
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Ensure user is the owner of the review
        if ($review->reviewer_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا التقييم.'
            ], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => $request->rating >= 4 ? 'positive' : 'negative',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل التقييم بنجاح.',
            'data' => $review
        ]);
    }

    /**
     * Delete a review via API.
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Ensure user is the owner of the review
        if ($review->reviewer_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا التقييم.'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقييم بنجاح.'
        ]);
    }
}
