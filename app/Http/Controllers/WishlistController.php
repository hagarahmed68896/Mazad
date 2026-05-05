<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Toggle an ad in the user's wishlist.
     */
    public function toggle(Request $request, $adId)
    {
        $ad = Ad::findOrFail($adId);
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'action' => $status,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }
}
