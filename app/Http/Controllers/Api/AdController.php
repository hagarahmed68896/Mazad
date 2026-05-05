<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function show($id)
    {
        $ad = Ad::with(['category', 'user', 'city', 'adProperties.property'])->find($id);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        // Increment views
        $ad->increment('views_count');

        // Related Ads
        $relatedAds = Ad::with(['category', 'user', 'city'])
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'active')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تفاصيل الإعلان',
            'data' => [
                'ad' => $ad,
                'related_ads' => $relatedAds
            ]
        ]);
    }
}
