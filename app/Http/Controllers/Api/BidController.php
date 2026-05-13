<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BidController as WebBidController;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Bid;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    /**
     * Get bid info for an ad (minimum bid, increment, current leader, etc.)
     */
    public function info($adId)
    {
        $ad = Ad::find($adId);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        $increment = (float) ($ad->bid_increment ?? 500);
        $currentPrice = (float) $ad->price;
        $minBid = $currentPrice + $increment;

        // Current leader
        $currentLeader = Bid::where('ad_id', $ad->id)
            ->where('type', 'bid')
            ->where('status', 'active')
            ->orderByDesc('amount')
            ->with('user:id,name')
            ->first();

        // Check if authenticated user has an active bid
        $myBid = null;
        if (auth('sanctum')->check()) {
            $myBid = Bid::where('ad_id', $ad->id)
                ->where('user_id', auth('sanctum')->id())
                ->where('type', 'bid')
                ->latest()
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'معلومات المزايدة',
            'data' => [
                'current_price' => $currentPrice,
                'bid_increment' => $increment,
                'minimum_bid' => $minBid,
                'total_bids' => $ad->bids()->where('type', 'bid')->count(),
                'current_leader' => $currentLeader ? [
                    'user_id' => $currentLeader->user_id,
                    'user_name' => $currentLeader->user->name ?? 'مجهول',
                    'amount' => (float) $currentLeader->amount,
                ] : null,
                'my_bid' => $myBid ? [
                    'amount' => (float) $myBid->amount,
                    'max_limit' => (float) $myBid->max_limit,
                    'status' => $myBid->status,
                    'is_leading' => $myBid->status === 'active',
                ] : null,
            ]
        ]);
    }

    /**
     * Place a bid (with auto-bidding logic)
     *
     * The user sends their max_limit (الحد الأعلى).
     * - If max_limit == minimum_bid → one-time bid
     * - If max_limit > minimum_bid → system auto-bids for them
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

        // Can't bid on own ad
        if ($ad->user_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك المزايدة على إعلانك'
            ], 403);
        }

        // Check ad is active
        if ($ad->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإعلان غير متاح للمزايدة حالياً'
            ], 400);
        }

        $type = $request->type ?? 'bid';

        if ($type === 'bid') {
            return $this->handleBid($request, $ad);
        } elseif ($type === 'offer') {
            return $this->handleOffer($request, $ad);
        } elseif ($type === 'tender') {
            return $this->handleTender($request, $ad);
        } elseif ($type === 'deposit') {
            return $this->handleDeposit($request, $ad);
        }

        return response()->json([
            'success' => false,
            'message' => 'نوع العملية غير صحيح'
        ], 400);
    }

    /**
     * Handle auction bid via API
     */
    private function handleBid(Request $request, Ad $ad)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ], [
            'amount.required' => 'مبلغ المزايدة مطلوب',
            'amount.numeric' => 'مبلغ المزايدة يجب أن يكون رقماً',
            'amount.min' => 'مبلغ المزايدة يجب أن يكون أكبر من صفر',
        ]);

        $increment = (float) ($ad->bid_increment ?? 500);
        $currentPrice = (float) $ad->price;
        $minBid = $currentPrice + $increment;
        $userMaxLimit = (float) $request->amount;

        // Validate minimum
        if ($userMaxLimit < $minBid) {
            return response()->json([
                'success' => false,
                'message' => 'أقل مبلغ للمزايدة هو ' . number_format($minBid) . ' ريال',
                'data' => [
                    'minimum_bid' => $minBid,
                    'current_price' => $currentPrice,
                    'bid_increment' => $increment,
                ]
            ], 422);
        }

        // Use the same core engine from the web controller
        $webController = new WebBidController();
        $result = $webController->processAutoBid($ad, $request->user()->id, $userMaxLimit);

        $ad->refresh();

        return response()->json([
            'success' => !$result['outbid'],
            'message' => $result['message'],
            'data' => [
                'current_price' => $result['current_price'],
                'outbid' => $result['outbid'],
                'minimum_next_bid' => $result['current_price'] + $increment,
            ]
        ], $result['outbid'] ? 200 : 200);
    }

    /**
     * Handle offer via API
     */
    private function handleOffer(Request $request, Ad $ad)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'validity_days' => 'nullable|integer|min:1',
        ]);

        if ((float) $request->amount <= (float) $ad->price) {
            return response()->json([
                'success' => false,
                'message' => 'عرضك أقل من أو يساوي السعر الحالي',
            ], 422);
        }

        $bid = Bid::create([
            'ad_id' => $ad->id,
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'validity_days' => $request->validity_days,
            'type' => 'offer',
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم عرضك بنجاح',
            'data' => $bid,
        ]);
    }

    /**
     * Handle tender via API
     */
    private function handleTender(Request $request, Ad $ad)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'min_limit' => 'nullable|numeric|min:1',
        ]);

        $amount = (float) $request->amount;
        $increment = (float) ($ad->bid_increment ?? 500);
        $currentPrice = (float) $ad->price;
        $maxAllowed = $currentPrice - $increment;

        if ($amount > $maxAllowed) {
            return response()->json([
                'success' => false,
                'message' => 'يجب أن يكون مبلغ المناقصة أقل من أو يساوي ' . number_format($maxAllowed) . ' ريال',
            ], 422);
        }

        $webController = new WebBidController();
        
        // Check if it's auto or manual based on presence of min_limit or type
        if ($request->type === 'auto_tender' || $request->has('min_limit')) {
            $minLimit = $request->min_limit ?? $amount;
            $result = $webController->processAutoTender($ad, $request->user()->id, $amount, $minLimit);
            
            return response()->json([
                'success' => !$result['outbid'],
                'message' => $result['message'],
                'data' => [
                    'current_price' => $result['current_price'],
                    'outbid' => $result['outbid']
                ]
            ]);
        }

        // Manual Tender logic
        $bid = \App\Models\Bid::create([
            'ad_id' => $ad->id,
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'type' => 'tender',
            'status' => 'active',
        ]);

        $ad->update([
            'price' => $amount,
            'bids_count' => $ad->bids_count + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم المناقصة بنجاح',
            'data' => $bid,
        ]);
    }

    /**
     * Get bids history for an ad
     */
    public function history($adId)
    {
        $ad = Ad::find($adId);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        $bids = Bid::where('ad_id', $adId)
            ->where('type', 'bid')
            ->with('user:id,name,avatar')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($bid) {
                return [
                    'id' => $bid->id,
                    'user_id' => $bid->user_id,
                    'user_name' => $bid->user->name ?? 'مجهول',
                    'amount' => (float) $bid->amount,
                    'status' => $bid->status,
                    'is_auto' => (bool) $bid->is_auto,
                    'created_at' => $bid->created_at->format('Y/m/d h:i:s A'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'سجل المزايدات',
            'data' => [
                'bids' => $bids,
                'total' => $bids->count(),
                'current_price' => (float) $ad->price,
            ]
        ]);
    }

    /**
     * Handle deposit via API
     */
    private function handleDeposit(Request $request, Ad $ad)
    {
        $userId = $request->user()->id;

        // Check if already joined
        $exists = Bid::where('ad_id', $ad->id)
            ->where('user_id', $userId)
            ->where('type', 'deposit')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'لقد اشتركت في هذا المزاد مسبقاً.'
            ], 400);
        }

        $amount = $ad->deposit_amount > 0 ? $ad->deposit_amount : 1000;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($ad, $userId, $amount, $request) {
            // Create Deposit Bid
            $bid = Bid::create([
                'ad_id' => $ad->id,
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'active',
            ]);

            // Create Transaction
            \App\Models\Transaction::create([
                'transaction_number' => 'TRX-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'user_id' => $userId,
                'ad_id' => $ad->id,
                'type' => 'deposit',
                'amount' => $amount,
                'payment_method' => $request->payment_method ?? 'bank_transfer',
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم الاشتراك في المزاد بنجاح! يمكنك الآن البدء بالمزايدة.',
                'data' => [
                    'bid_id' => $bid->id,
                    'deposit_amount' => (float) $amount
                ]
            ]);
        });
    }
}
