<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request, $adId)
    {
        $ad = Ad::findOrFail($adId);

        // Prevent buying own ad
        if ($ad->user_id === Auth::id()) {
            return back()->with('error', 'لا يمكنك شراء إعلانك الخاص.');
        }

        // Prevent duplicate orders for the same ad
        $existing = Order::where('ad_id', $ad->id)
            ->where('buyer_id', Auth::id())
            ->whereIn('status', ['pending_payment', 'receiving', 'received', 'paid'])
            ->first();

        if ($existing) {
            return back()->with('error', 'لقد قمت بشراء هذا الإعلان مسبقاً.');
        }

        // Prevent buying a finished ad
        if ($ad->status === 'finished') {
            return back()->with('error', 'هذا الإعلان لم يعد متاحاً للشراء.');
        }

        // Calculate fees (same logic as show.blade)
        $comm_rate = (float) (\App\Models\Setting::where('key', 'commission_rate')->first()->value ?? 2.5);
        if ($comm_rate > 50) $comm_rate = 2.5;
        $vat_rate = 15;
        $admin_fees = (float) (\App\Models\Setting::where('key', 'admin_fees')->first()->value ?? 19);
        $transfer_fees = (float) (\App\Models\Setting::where('key', 'transfer_fees')->first()->value ?? 500);

        $saai_val = $ad->price * ($comm_rate / 100);
        $vat_val = $saai_val * ($vat_rate / 100);
        
        // Total price for direct purchase matches the UI modal (Price + Admin Fees + Transfer Fees)
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

        // Log a transaction for tracking
        Transaction::create([
            'transaction_number' => 'TXN-' . strtoupper(Str::random(8)),
            'user_id'            => Auth::id(),
            'ad_id'              => $ad->id,
            'type'               => 'purchase',
            'amount'             => round($total_price),
            'payment_method'     => $request->payment_method ?? 'bank_transfer',
            'status'             => 'under_review',
        ]);

        // Mark ad as finished so no one else can buy it
        $ad->update(['status' => 'finished']);

        return back()->with('success', 'تم تقديم طلب الشراء بنجاح! رقم الطلب: #' . $order->order_number . '. سيتم التواصل معك قريباً لإتمام الدفع.');
    }
}
