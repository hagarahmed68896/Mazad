<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Bid;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function store(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للمشاركة.');
        }

        // Can't bid on own ad
        if ($ad->user_id === Auth::id()) {
            return back()->with('error', 'لا يمكنك المزايدة على إعلانك');
        }

        $type = $request->type ?? 'bid';

        if ($type === 'bid') {
            return $this->handleAuctionBid($request, $ad);
        } elseif ($type === 'offer') {
            return $this->handleOffer($request, $ad);
        } elseif ($type === 'tender' || $type === 'auto_tender') {
            return $this->handleTender($request, $ad, $type);
        } elseif ($type === 'deposit') {
            return $this->handleDeposit($request, $ad);
        }

        return back()->with('error', 'نوع العملية غير صحيح');
    }

    /**
     * ===== AUCTION BID with Auto-Bidding =====
     *
     * Logic:
     * - User enters their "الحد الأعلى" (max limit / ceiling)
     * - Minimum allowed = current_price + bid_increment
     * - If max_limit == minimum → one-time bid (no auto-bidding)
     * - If max_limit > minimum → system auto-bids on their behalf up to that ceiling
     *
     * When a new bid arrives:
     * - If no competitor: place at minimum
     * - If new max > old max: new bidder wins, price = min(old_max + increment, new_max)
     * - If new max <= old max: old bidder stays, system auto-counters at min(new_max + increment, old_max)
     * - Loser gets notified immediately
     */
    private function handleAuctionBid(Request $request, Ad $ad)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $increment = (float) ($ad->bid_increment ?? 500);
        $currentPrice = (float) $ad->price;
        $minBid = $currentPrice + $increment;
        $userMaxLimit = (float) $request->amount;

        // Validate minimum
        if ($userMaxLimit < $minBid) {
            return back()->withInput()->with('error', 'أقل مبلغ للمزايدة هو ' . number_format($minBid) . ' ريال');
        }

        $result = $this->processAutoBid($ad, Auth::id(), $userMaxLimit);

        if ($result['outbid']) {
            return back()->with('warning', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Core auto-bidding engine (DB transaction with row locking)
     */
    public function processAutoBid(Ad $ad, int $userId, float $newMaxLimit): array
    {
        return DB::transaction(function () use ($ad, $userId, $newMaxLimit) {
            // Lock the ad row to prevent race conditions
            $ad = Ad::lockForUpdate()->find($ad->id);
            $increment = (float) ($ad->bid_increment ?? 500);
            $currentPrice = (float) $ad->price;
            $minBid = $currentPrice + $increment;

            // Deactivate any existing bid from this user on this ad
            Bid::where('ad_id', $ad->id)
                ->where('user_id', $userId)
                ->where('type', 'bid')
                ->where('status', 'active')
                ->update(['status' => 'replaced']);

            // Find the current leading bidder (from someone else)
            $currentTopBid = Bid::where('ad_id', $ad->id)
                ->where('user_id', '!=', $userId)
                ->where('type', 'bid')
                ->where('status', 'active')
                ->orderByDesc('max_limit')
                ->first();

            // ──────────────────────────────────
            // CASE 1: No competing bidder
            // ──────────────────────────────────
            if (!$currentTopBid) {
                Bid::create([
                    'ad_id' => $ad->id,
                    'user_id' => $userId,
                    'amount' => $minBid,
                    'max_limit' => $newMaxLimit,
                    'type' => 'bid',
                    'status' => 'active',
                ]);

                $ad->update([
                    'price' => $minBid,
                    'bids_count' => $ad->bids_count + 1,
                ]);

                return [
                    'outbid' => false,
                    'message' => 'تم تسجيل مزايدتك بنجاح بمبلغ ' . number_format($minBid) . ' ريال',
                    'current_price' => $minBid,
                ];
            }

            $existingMaxLimit = (float) $currentTopBid->max_limit;

            // ──────────────────────────────────
            // CASE 2: New bidder has HIGHER ceiling → NEW WINS
            // ──────────────────────────────────
            if ($newMaxLimit > $existingMaxLimit) {
                $settledPrice = min($existingMaxLimit + $increment, $newMaxLimit);

                // Deactivate old winner
                $currentTopBid->update(['status' => 'outbid']);

                // Place new winning bid
                Bid::create([
                    'ad_id' => $ad->id,
                    'user_id' => $userId,
                    'amount' => $settledPrice,
                    'max_limit' => $newMaxLimit,
                    'type' => 'bid',
                    'status' => 'active',
                ]);

                $ad->update([
                    'price' => $settledPrice,
                    'bids_count' => $ad->bids_count + 1,
                ]);

                // Notify old leader
                $this->notifyOutbid($currentTopBid->user_id, $ad, $settledPrice);

                return [
                    'outbid' => false,
                    'message' => 'تم تسجيل مزايدتك بنجاح! السعر الحالي ' . number_format($settledPrice) . ' ريال',
                    'current_price' => $settledPrice,
                ];
            }

            // ──────────────────────────────────
            // CASE 3: Existing bidder has HIGHER or EQUAL ceiling → EXISTING STAYS
            // System auto-bids for them
            // ──────────────────────────────────
            $autoPrice = min($newMaxLimit + $increment, $existingMaxLimit);

            // Record new bidder's attempt (immediately outbid)
            Bid::create([
                'ad_id' => $ad->id,
                'user_id' => $userId,
                'amount' => $minBid,
                'max_limit' => $newMaxLimit,
                'type' => 'bid',
                'status' => 'outbid',
            ]);

            // System auto-counters for existing leader
            $currentTopBid->update(['status' => 'replaced']);
            Bid::create([
                'ad_id' => $ad->id,
                'user_id' => $currentTopBid->user_id,
                'amount' => $autoPrice,
                'max_limit' => $existingMaxLimit,
                'is_auto' => true,
                'type' => 'bid',
                'status' => 'active',
            ]);

            $ad->update([
                'price' => $autoPrice,
                'bids_count' => $ad->bids_count + 2,
            ]);

            // Notify the new bidder they lost
            $this->notifyOutbid($userId, $ad, $autoPrice);

            return [
                'outbid' => true,
                'message' => 'تم تجاوز مزايدتك تلقائياً! السعر الحالي ' . number_format($autoPrice) . ' ريال. يمكنك رفع الحد الأعلى للمزايدة مرة أخرى.',
                'current_price' => $autoPrice,
            ];
        });
    }

    /**
     * Send outbid notification
     */
    private function notifyOutbid(int $userId, Ad $ad, float $newPrice): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'title' => 'تم تجاوز مزايدتك',
                'content' => 'تم تجاوز مزايدتك على "' . $ad->title . '". السعر الحالي ' . number_format($newPrice) . ' ريال. قم برفع الحد الأعلى لمزايدتك الآن!',
                'type' => 'bid_outbid',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create outbid notification: ' . $e->getMessage());
        }
    }

    /**
     * Handle offer submissions (for direct purchase ads)
     */
    private function handleOffer(Request $request, Ad $ad)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'validity_days' => 'nullable|integer|min:1',
        ]);

        Bid::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'validity_days' => $request->validity_days,
            'type' => 'offer',
            'status' => 'active',
        ]);

        return back()->with('success', 'تم تقديم عرضك بنجاح.');
    }

    /**
     * ===== TENDER BID with Auto-Tender =====
     * 
     * Logic (Reverse of Auction):
     * - User sets a "min_limit" (the floor).
     * - Current price is lowered by increment.
     * - If new user's floor is lower than existing leader's floor, new user wins.
     */
    private function handleTender(Request $request, Ad $ad, string $type)
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
            return back()->withInput()->with('error', 'يجب أن يكون مبلغ المناقصة أقل من أو يساوي ' . number_format($maxAllowed) . ' ريال');
        }

        // If it's an auto-tender, we use the engine
        if ($type === 'auto_tender' || $request->has('min_limit')) {
            $minLimit = $request->min_limit ?? $amount;
            $result = $this->processAutoTender($ad, Auth::id(), $amount, $minLimit);
            
            if ($result['outbid']) {
                return back()->with('warning', $result['message']);
            }
            return back()->with('success', $result['message']);
        }

        // Manual Tender
        DB::transaction(function () use ($ad, $amount) {
            $ad = Ad::lockForUpdate()->find($ad->id);
            Bid::create([
                'ad_id' => $ad->id,
                'user_id' => Auth::id(),
                'amount' => $amount,
                'type' => 'tender',
                'status' => 'active',
            ]);
            $ad->update([
                'price' => $amount,
                'bids_count' => $ad->bids_count + 1,
            ]);
        });

        return back()->with('success', 'تم تقديم المناقصة بنجاح.');
    }

    /**
     * Auto-Tender Engine (Reverse Auction)
     */
    private function processAutoTender(Ad $ad, int $userId, float $initialAmount, float $newMinLimit): array
    {
        return DB::transaction(function () use ($ad, $userId, $initialAmount, $newMinLimit) {
            $ad = Ad::lockForUpdate()->find($ad->id);
            $increment = (float) ($ad->bid_increment ?? 500);
            $currentPrice = (float) $ad->price;
            $maxAllowed = $currentPrice - $increment;

            // Deactivate existing
            Bid::where('ad_id', $ad->id)->where('user_id', $userId)->where('type', 'auto_tender')->update(['status' => 'replaced']);

            // Current leading tender
            $currentTopTender = Bid::where('ad_id', $ad->id)
                ->where('user_id', '!=', $userId)
                ->where('type', 'auto_tender')
                ->where('status', 'active')
                ->orderBy('min_limit', 'asc') // In tender, lower min_limit is better for the buyer
                ->first();

            if (!$currentTopTender) {
                Bid::create([
                    'ad_id' => $ad->id, 'user_id' => $userId, 'amount' => $maxAllowed,
                    'min_limit' => $newMinLimit, 'type' => 'auto_tender', 'status' => 'active',
                ]);
                $ad->update(['price' => $maxAllowed, 'bids_count' => $ad->bids_count + 1]);
                return ['outbid' => false, 'message' => 'أنت صاحب أقل عرض حالياً بمبلغ ' . number_format($maxAllowed), 'current_price' => $maxAllowed];
            }

            $existingMinLimit = (float) $currentTopTender->min_limit;

            // New tender is lower (better)
            if ($newMinLimit < $existingMinLimit) {
                $settledPrice = max($existingMinLimit - $increment, $newMinLimit);
                $currentTopTender->update(['status' => 'outbid']);
                Bid::create([
                    'ad_id' => $ad->id, 'user_id' => $userId, 'amount' => $settledPrice,
                    'min_limit' => $newMinLimit, 'type' => 'auto_tender', 'status' => 'active',
                ]);
                $ad->update(['price' => $settledPrice, 'bids_count' => $ad->bids_count + 1]);
                return ['outbid' => false, 'message' => 'تم قبول عرضك كأقل سعر حالياً: ' . number_format($settledPrice), 'current_price' => $settledPrice];
            }

            // Existing tender is lower (stays winner)
            $autoPrice = max($newMinLimit - $increment, $existingMinLimit);
            Bid::create([
                'ad_id' => $ad->id, 'user_id' => $userId, 'amount' => $maxAllowed,
                'min_limit' => $newMinLimit, 'type' => 'auto_tender', 'status' => 'outbid',
            ]);
            $currentTopTender->update(['status' => 'replaced']);
            Bid::create([
                'ad_id' => $ad->id, 'user_id' => $currentTopTender->user_id, 'amount' => $autoPrice,
                'min_limit' => $existingMinLimit, 'is_auto' => true, 'type' => 'auto_tender', 'status' => 'active',
            ]);
            $ad->update(['price' => $autoPrice, 'bids_count' => $ad->bids_count + 2]);
            return ['outbid' => true, 'message' => 'يوجد عرض منافس أقل من حدك الأدنى! السعر الحالي ' . number_format($autoPrice), 'current_price' => $autoPrice];
        });
    }

    /**
     * Handle deposit payment to join auction
     */
    private function handleDeposit(Request $request, Ad $ad)
    {
        $userId = Auth::id();

        // Check if already joined
        $exists = Bid::where('ad_id', $ad->id)
            ->where('user_id', $userId)
            ->where('type', 'deposit')
            ->exists();

        if ($exists) {
            return back()->with('info', 'لقد اشتركت في هذا المزاد مسبقاً.');
        }

        $amount = $ad->deposit_amount > 0 ? $ad->deposit_amount : 1000;

        return DB::transaction(function () use ($ad, $userId, $amount, $request) {
            // Create Deposit Bid record
            Bid::create([
                'ad_id' => $ad->id,
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'active',
            ]);

            // Create Transaction record
            \App\Models\Transaction::create([
                'transaction_number' => 'TRX-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'user_id' => $userId,
                'ad_id' => $ad->id,
                'type' => 'deposit',
                'amount' => $amount,
                'payment_method' => $request->payment_method ?? 'bank_transfer',
                'status' => 'completed',
            ]);

            return back()->with('success', 'تم الاشتراك في المزاد بنجاح! يمكنك الآن البدء بالمزايدة.');
        });
    }
}
