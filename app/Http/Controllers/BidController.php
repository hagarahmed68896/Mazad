<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Bid;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required_unless:type,auto_tender|numeric|min:0',
            'min_limit' => 'required_if:type,auto_tender|numeric|min:0',
            'validity_days' => 'nullable|integer|min:1',
            'type' => 'nullable|string|in:bid,offer,tender,auto_tender'
        ]);

        $ad = Ad::findOrFail($id);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للمشاركة.');
        }

        $type = $request->type ?? 'bid';
        $amount = $request->amount;

        // If auto_tender, we might set initial amount to current price - 500
        if ($type === 'auto_tender') {
            $amount = $ad->price - 500;
            if ($request->min_limit >= $ad->price) {
                return back()->withInput()->with('error', 'الحد الأدنى يجب أن يكون أقل من السعر الحالي');
            }
        }

        // Price check logic
        if ($type === 'tender' || $type === 'auto_tender') {
            if ($amount >= $ad->price) {
                return back()->withInput()->with('error', 'يجب أن يكون مبلغ المناقصة أقل من السعر الحالي');
            }
        } else {
            // Ensure amount is greater than current price for regular bids/offers
            if ($amount <= $ad->price) {
                return back()->withInput()->with('error', 'عرضك أقل من السعر المسموح به');
            }
        }

        // Create the bid/offer/tender
        Bid::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::id(),
            'amount' => $amount,
            'min_limit' => $request->min_limit,
            'validity_days' => $request->validity_days,
            'type' => $type,
            'status' => 'active'
        ]);

        // Update current price for regular bids and tenders
        if ($type === 'bid' || $type === 'tender' || $type === 'auto_tender') {
            $ad->price = $amount;
            $ad->bids_count = $ad->bids_count + 1;
            $ad->save();
        }

        $msg = $type === 'offer' ? 'تم تقديم عرضك بنجاح.' : 
              ($type === 'tender' ? 'تم تقديم المناقصة بنجاح.' : 'تم تسجيل اشتراكك في المزاد بنجاح.');
        return back()->with('success', $msg);
    }
}
