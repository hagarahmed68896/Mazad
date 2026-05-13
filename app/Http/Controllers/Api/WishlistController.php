<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Toggle an ad in the user's wishlist via API.
     */
    public function toggle(Request $request, $adId)
    {
        $ad = Ad::find($adId);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
                            ->where('ad_id', $ad->id)
                            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
            $message = 'تم إزالة الإعلان من المفضلة';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'ad_id' => $ad->id
            ]);
            $status = 'added';
            $message = 'تم إضافة الإعلان إلى المفضلة';
        }

        return response()->json([
            'success' => true,
            'action' => $status,
            'message' => $message,
            'data' => [
                'ad_id' => $ad->id,
                'is_favorite' => ($status === 'added')
            ]
        ]);
    }

    /**
     * Get the user's wishlist.
     */
    public function index()
    {
        $user = Auth::user();
        $favorites = Favorite::where('user_id', $user->id)
                            ->with('ad')
                            ->get()
                            ->pluck('ad');

        return response()->json([
            'success' => true,
            'message' => 'قائمة المفضلة',
            'data' => $favorites
        ]);
    }
}
