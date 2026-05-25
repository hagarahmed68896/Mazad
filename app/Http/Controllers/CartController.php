<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('ad.user')->get();
        return view('cart.index', compact('cartItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $ad = Ad::findOrFail($request->ad_id);

        // Check if item already in cart
        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('ad_id', $ad->id)
                        ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'ad_id' => $ad->id,
                'quantity' => $request->quantity,
                'price' => $ad->price
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة المنتج للسلة بنجاح',
                'cart_count' => Cart::where('user_id', Auth::id())->count()
            ]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المنتج للسلة بنجاح');
    }

    public function update(Request $request, $id)
    {
        $cartItem = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $cartItem = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->delete();

        return redirect()->back()->with('success', 'تم حذف المنتج من السلة');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'required|string|max:20',
            'buyer_address' => 'required|string',
            'buyer_latitude' => 'nullable|numeric',
            'buyer_longitude' => 'nullable|numeric',
            'payment_method' => 'required|string|in:bank_transfer,cod,card,mada',
            'order_notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Update user profile info
        $user->update([
            'name' => $request->buyer_name,
            'phone' => $request->buyer_phone,
            'address' => $request->buyer_address,
            'latitude' => $request->buyer_latitude,
            'longitude' => $request->buyer_longitude,
        ]);

        $cartItems = Cart::where('user_id', $user->id)->with('ad')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'سلتك فارغة حالياً'
            ], 422);
        }

        // Calculate admin fees etc (similar to OrderController)
        $admin_fees = (float) (\App\Models\Setting::where('key', 'admin_fees')->first()->value ?? 19);
        $transfer_fees = (float) (\App\Models\Setting::where('key', 'transfer_fees')->first()->value ?? 500);

        // We can split/apportion fees across orders or just charge them on the checkout.
        // Let's create orders for each ad in the cart.
        $ordersCreated = 0;
        foreach ($cartItems as $item) {
            $ad = $item->ad;

            // Basic checks
            if ($ad->user_id === $user->id) {
                continue; // Can't buy own ad
            }

            if ($ad->status === 'finished') {
                continue; // Skip finished ads
            }

            // Price for this item based on quantity
            $itemPrice = $item->price * $item->quantity;
            $shippingPrice = (float) ($ad->shipping_price ?? 0);
            
            // Total price matches single checkout: item price + shipping + fees
            $total_price = $itemPrice + $shippingPrice + $admin_fees + $transfer_fees;

            // Create Order
            $order = Order::create([
                'order_number' => strtoupper(Str::random(8)),
                'ad_id'        => $ad->id,
                'buyer_id'     => $user->id,
                'seller_id'    => $ad->user_id,
                'final_price'  => round($total_price),
                'status'       => 'pending_payment',
            ]);

            // Create Transaction
            Transaction::create([
                'transaction_number' => 'TXN-' . strtoupper(Str::random(8)),
                'user_id'            => $user->id,
                'ad_id'              => $ad->id,
                'type'               => 'purchase',
                'amount'             => round($total_price),
                'payment_method'     => $request->payment_method,
                'status'             => 'under_review',
            ]);

            // Manage ad quantity and status
            if ($ad->quantity > $item->quantity) {
                $ad->decrement('quantity', $item->quantity);
            } else {
                $ad->update([
                    'quantity' => 0,
                    'status' => 'finished'
                ]);
            }

            $ordersCreated++;
        }

        if ($ordersCreated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم إتمام أي طلب، قد تكون المنتجات غير متوفرة أو تملك الإعلانات بنفسك.'
            ], 422);
        }

        // Clear user's cart
        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم طلبات الشراء بنجاح وجاري مراجعة التحويلات!',
            'redirect' => route('home'),
        ]);
    }
}
