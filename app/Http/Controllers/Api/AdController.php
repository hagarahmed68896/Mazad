<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function show(Request $request, $id)
    {
        $ad = Ad::with(['category', 'user.reviewsReceived.reviewer', 'city', 'adProperties.property', 'images', 'attachments', 'questions.user'])
                ->withCount('bids')
                ->find($id);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        // Increment views
        $ad->increment('views_count');

        // Check if favorited by current authenticated user (if using token)
        $isFavorited = false;
        if ($request->bearerToken() && $user = auth('sanctum')->user()) {
            $isFavorited = $ad->favorites()->where('user_id', $user->id)->exists();
        }
        $ad->is_favorited = $isFavorited;

        // Related Ads
        $relatedAds = Ad::with(['category', 'user', 'city'])
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'active')
            ->limit(6)
            ->get();

        // Bid info for auction ads
        $bidIncrement = (float) ($ad->bid_increment ?? 500);
        $minimumBid = (float) $ad->price + $bidIncrement;

        // Suggested Services
        $suggestedServices = \App\Models\Service::where('category_id', $ad->category_id)->limit(4)->get();

        return response()->json([
            'success' => true,
            'message' => 'تفاصيل الإعلان',
            'data' => [
                'ad' => $ad,
                'bid_info' => [
                    'bid_increment' => $bidIncrement,
                    'current_price' => (float) $ad->price,
                    'minimum_bid' => $minimumBid,
                ],
                'related_ads' => $relatedAds,
                'suggested_services' => $suggestedServices
            ]
        ]);
    }
}
