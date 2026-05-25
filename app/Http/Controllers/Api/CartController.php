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

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = Cart::where('user_id', Auth::id())->find($id);
        
        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير موجود في السلة'
            ], 404);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الكمية بنجاح',
            'data' => $cartItem
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'required|string|max:20',
            'buyer_address' => 'required|string',
            'buyer_latitude' => 'nullable|numeric',
            'buyer_longitude' => 'nullable|numeric',
            'payment_method' => 'required|in:card,mada,bank_transfer,cod,apple_pay',
        ]);

        $cartItems = Cart::where('user_id', Auth::id())->with('ad')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'السلة فارغة'
            ], 400);
        }

        $orders = [];
        $totalAmount = 0;

        foreach ($cartItems as $item) {
            $shippingPrice = $item->ad->shipping_price ?? 0;
            $finalPrice = ($item->price * $item->quantity) + $shippingPrice;
            $totalAmount += $finalPrice;

            $orders[] = \App\Models\Order::create([
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'ad_id' => $item->ad_id,
                'buyer_id' => Auth::id(),
                'seller_id' => $item->ad->user_id,
                'final_price' => $finalPrice,
                'status' => 'pending' // Or 'paid' depending on logic
            ]);
        }

        // Create transaction
        \App\Models\Transaction::create([
            'transaction_number' => 'TRX-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'user_id' => Auth::id(),
            'amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'type' => 'payment',
            'status' => 'completed'
        ]);

        // Clear cart
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم إتمام الطلب بنجاح',
            'data' => [
                'orders' => $orders,
                'total_amount' => $totalAmount
            ]
        ]);
    }
}
