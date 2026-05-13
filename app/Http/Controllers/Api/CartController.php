<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Ad;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('ad.category', 'ad.user')->get();
        
        $total = $cartItems->sum(function($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'total_price' => (float) $total,
                'count' => $cartItems->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $ad = Ad::findOrFail($request->ad_id);

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('ad_id', $ad->id)
                        ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            $cartItem = Cart::create([
                'user_id' => Auth::id(),
                'ad_id' => $ad->id,
                'quantity' => $request->quantity,
                'price' => (float) $ad->price
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة المنتج للسلة بنجاح',
            'data' => [
                'cart_item' => $cartItem,
                'cart_count' => Cart::where('user_id', Auth::id())->count()
            ]
        ]);
    }

    public function destroy($id)
    {
        $cartItem = Cart::where('user_id', Auth::id())->find($id);
        
        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير موجود في السلة'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج من السلة',
            'cart_count' => Cart::where('user_id', Auth::id())->count()
        ]);
    }

    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم إفراغ السلة بنجاح'
        ]);
    }
}
