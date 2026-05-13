<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Store a new order (Buy Now / Direct Purchase)
     */
    public function store(Request $request, $adId)
    {
        $ad = Ad::find($adId);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        // Prevent buying own ad
        if ($ad->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك شراء إعلانك الخاص.'
            ], 403);
        }

        // Prevent duplicate orders
        $existing = Order::where('ad_id', $ad->id)
            ->where('buyer_id', Auth::id())
            ->whereIn('status', ['pending_payment', 'receiving', 'received', 'paid'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بشراء هذا الإعلان مسبقاً.'
            ], 400);
        }

        // Prevent buying a finished ad
        if ($ad->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإعلان لم يعد متاحاً للشراء.'
            ], 400);
        }

        // Calculate fees
        $admin_fees = (float) (Setting::where('key', 'admin_fees')->first()->value ?? 19);
        $transfer_fees = (float) (Setting::where('key', 'transfer_fees')->first()->value ?? 500);
        
        $total_price = $ad->price + $admin_fees + $transfer_fees;

        // Create the order
        $order = Order::create([
            'order_number' => strtoupper(Str::random(8)),
            'ad_id'        => $ad->id,
            'buyer_id'     => Auth::id(),
            'seller_id'    => $ad->user_id,
            'final_price'  => round($total_price),
            'status'       => 'pending_payment',
        ]);

        // Log a transaction
        Transaction::create([
            'transaction_number' => 'TXN-' . strtoupper(Str::random(8)),
            'user_id'            => Auth::id(),
            'ad_id'              => $ad->id,
            'type'               => 'purchase',
            'amount'             => round($total_price),
            'payment_method'     => $request->payment_method ?? 'bank_transfer',
            'status'             => 'under_review',
        ]);

        // Mark ad as finished
        $ad->update(['status' => 'finished']);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم طلب الشراء بنجاح! رقم الطلب: #' . $order->order_number,
            'data' => [
                'order_number' => $order->order_number,
                'final_price' => (float) $order->final_price,
                'status' => $order->status
            ]
        ]);
    }
}
