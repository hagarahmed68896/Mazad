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

        // Can't bid on own or team member's ad
        if (Auth::user()->isSameTeam($ad->user_id)) {
            return back()->with('error', 'لا يمكنك المزايدة على إعلان خاص بك أو بأحد مناديبك/شركتك');
        }

        $type = $request->type ?? 'bid';

        if ($type === 'bid') {
            $hasJoined = Bid::where('ad_id', $ad->id)
                ->where('user_id', Auth::id())
                ->where('type', 'deposit')
                ->where('status', 'active')
                ->exists();

            if (!$hasJoined) {
                $depositAmount = $ad->deposit_amount > 0 ? $ad->deposit_amount : 1000;
                DB::transaction(function () use ($ad, $depositAmount) {
                    Bid::create([
                        'ad_id' => $ad->id,
                        'user_id' => Auth::id(),
                        'amount' => $depositAmount,
                        'type' => 'deposit',
                        'status' => 'active',
                    ]);
                    \App\Models\Transaction::create([
                        'transaction_number' => 'TRX-' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'user_id' => Auth::id(),
                        'ad_id' => $ad->id,
                        'type' => 'deposit',
                        'amount' => $depositAmount,
                        'payment_method' => 'bank_transfer',
                        'status' => 'completed',
                    ]);
                });
            }

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
            'max_limit' => 'nullable|numeric|min:1',
        ]);

        $increment = (float) ($ad->bid_increment ?? 500);
        $currentPrice = (float) $ad->price;
        $minBid = $currentPrice + $increment;

        $bidAmount = (float) $request->amount;
        $userMaxLimit = $request->filled('max_limit') ? (float) $request->max_limit : $bidAmount;

        // Validate minimum
        if ($bidAmount < $minBid) {
            return back()->withInput()->with('error', 'أقل مبلغ للمزايدة هو ' . number_format($minBid) . ' ريال');
        }

        if ($userMaxLimit < $bidAmount) {
            return back()->withInput()->with('error', 'يجب أن يكون الحد الأعلى أكبر من أو يساوي قيمة المزايدة');
        }

        $result = $this->processAutoBid($ad, Auth::id(), $userMaxLimit, $bidAmount);

        if ($result['outbid']) {
            return back()->with('warning', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Core auto-bidding engine (DB transaction with row locking)
     */
    public function processAutoBid(Ad $ad, int $userId, float $newMaxLimit, float $startBid = null): array
    {
        return DB::transaction(function () use ($ad, $userId, $newMaxLimit, $startBid) {
            // Lock the ad row to prevent race conditions
            $ad = Ad::lockForUpdate()->find($ad->id);
            $increment = (float) ($ad->bid_increment ?? 500);
            $currentPrice = (float) $ad->price;
            $minBid = $startBid ?: ($currentPrice + $increment);

            // Check if they are already the leading active bidder
            $currentTopBid = Bid::where('ad_id', $ad->id)
                ->where('type', 'bid')
                ->where('status', 'active')
                ->first();

            if ($currentTopBid && $currentTopBid->user_id === $userId) {
                if ($newMaxLimit >= $currentPrice) {
                    $currentTopBid->update([
                        'max_limit' => $newMaxLimit,
                        'notify_near_limit' => (bool) request('notify_near_limit', false),
                    ]);
                    return [
                        'outbid' => false,
                        'message' => 'تم تحديث الحد الأعلى لمزايدتك بنجاح إلى ' . number_format($newMaxLimit) . ' ريال.',
                        'current_price' => $currentPrice,
                    ];
                }
                return [
                    'outbid' => false,
                    'message' => 'أنت المزايد الأعلى حالياً على هذا الإعلان بالفعل.',
                    'current_price' => $currentPrice,
                ];
            }

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
                    'notify_near_limit' => (bool) request('notify_near_limit', false),
                ]);

                $ad->update([
                    'price' => $minBid,
                    'bids_count' => $ad->bids_count + 1,
                ]);

                $this->notifyAdOwner($ad->user_id, $ad, $minBid, 'bid');

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
                    'notify_near_limit' => (bool) request('notify_near_limit', false),
                ]);

                $ad->update([
                    'price' => $settledPrice,
                    'bids_count' => $ad->bids_count + 1,
                ]);

                // Notify old leader
                $this->notifyOutbid($currentTopBid->user_id, $ad, $settledPrice);

                $this->notifyAdOwner($ad->user_id, $ad, $settledPrice, 'bid');

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
                'notify_near_limit' => (bool) request('notify_near_limit', false),
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
                'notify_near_limit' => (bool) ($currentTopBid->notify_near_limit ?? false),
            ]);

            $ad->update([
                'price' => $autoPrice,
                'bids_count' => $ad->bids_count + 2,
            ]);

            // Notify the new bidder they lost
            $this->notifyOutbid($userId, $ad, $autoPrice);

            $this->notifyAdOwner($ad->user_id, $ad, $autoPrice, 'bid');

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
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create outbid notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify ad owner about new bid, tender, or offer
     */
    private function notifyAdOwner(int $userId, Ad $ad, float $amount, string $type): void
    {
        try {
            if ($type === 'tender') {
                $title = 'تم تقديم مناقصة جديدة 📝';
                $content = 'تم تقديم مناقصة بقيمة ' . number_format($amount) . ' ريال على إعلانك "' . $ad->title . '".';
                $notifType = 'notification';
            } elseif ($type === 'offer') {
                $title = 'عرض جديد على إعلانك 💰';
                $content = 'تم تقديم عرض جديد بقيمة ' . number_format($amount) . ' ريال على إعلانك "' . $ad->title . '".';
                $notifType = 'offer';
            } else {
                $title = 'مزايدة جديدة على إعلانك 🔨';
                $content = 'تم تقديم مزايدة جديدة بقيمة ' . number_format($amount) . ' ريال على إعلانك "' . $ad->title . '".';
                $notifType = 'notification';
            }

            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'content' => $content,
                'type' => $notifType,
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to notify ad owner: ' . $e->getMessage());
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

        $this->notifyAdOwner($ad->user_id, $ad, (float) $request->amount, 'offer');

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
                'notify_near_limit' => (bool) request('notify_near_limit', false),
            ]);
            $ad->update([
                'price' => $amount,
                'bids_count' => $ad->bids_count + 1,
            ]);
            $this->notifyAdOwner($ad->user_id, $ad, $amount, 'tender');
        });

        return back()->with('success', 'تم تقديم المناقصة بنجاح.');
    }

    /**
     * Auto-Tender Engine (Reverse Auction)
     */
    public function processAutoTender(Ad $ad, int $userId, float $initialAmount, float $newMinLimit): array
    {
        return DB::transaction(function () use ($ad, $userId, $initialAmount, $newMinLimit) {
            $ad = Ad::lockForUpdate()->find($ad->id);
            $increment = (float) ($ad->bid_increment ?? 500);
            $currentPrice = (float) $ad->price;
            $maxAllowed = $currentPrice - $increment;

            // Check if they are already the leading active tenderer
            $currentTopTender = Bid::where('ad_id', $ad->id)
                ->where('type', 'auto_tender')
                ->where('status', 'active')
                ->first();

            if ($currentTopTender && $currentTopTender->user_id === $userId) {
                if ($newMinLimit <= $currentPrice) {
                    $currentTopTender->update([
                        'min_limit' => $newMinLimit,
                        'notify_near_limit' => (bool) request('notify_near_limit', false),
                    ]);
                    return [
                        'outbid' => false,
                        'message' => 'تم تحديث الحد الأدنى لمناقصتك بنجاح إلى ' . number_format($newMinLimit) . ' ريال.',
                        'current_price' => $currentPrice,
                    ];
                }
                return [
                    'outbid' => false,
                    'message' => 'أنت صاحب أقل عرض حالياً بالفعل.',
                    'current_price' => $currentPrice,
                ];
            }

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
                    'notify_near_limit' => (bool) request('notify_near_limit', false),
                ]);
                $ad->update(['price' => $maxAllowed, 'bids_count' => $ad->bids_count + 1]);
                $this->notifyAdOwner($ad->user_id, $ad, $maxAllowed, 'tender');
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
                    'notify_near_limit' => (bool) request('notify_near_limit', false),
                ]);
                $ad->update(['price' => $settledPrice, 'bids_count' => $ad->bids_count + 1]);
                $this->notifyAdOwner($ad->user_id, $ad, $settledPrice, 'tender');
                return ['outbid' => false, 'message' => 'تم قبول عرضك كأقل سعر حالياً: ' . number_format($settledPrice), 'current_price' => $settledPrice];
            }

            // Existing tender is lower (stays winner)
            $autoPrice = max($newMinLimit - $increment, $existingMinLimit);
            Bid::create([
                'ad_id' => $ad->id, 'user_id' => $userId, 'amount' => $maxAllowed,
                'min_limit' => $newMinLimit, 'type' => 'auto_tender', 'status' => 'outbid',
                'notify_near_limit' => (bool) request('notify_near_limit', false),
            ]);
            $currentTopTender->update(['status' => 'replaced']);
            Bid::create([
                'ad_id' => $ad->id, 'user_id' => $currentTopTender->user_id, 'amount' => $autoPrice,
                'min_limit' => $existingMinLimit, 'is_auto' => true, 'type' => 'auto_tender', 'status' => 'active',
                'notify_near_limit' => (bool) ($currentTopTender->notify_near_limit ?? false),
            ]);
            $ad->update(['price' => $autoPrice, 'bids_count' => $ad->bids_count + 2]);
            $this->notifyAdOwner($ad->user_id, $ad, $autoPrice, 'tender');
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

    public function historyPage(Request $request, $id)
    {
        $ad = Ad::with(['category'])->findOrFail($id);
        
        $bids = Bid::where('ad_id', $ad->id)
            ->whereIn('type', ['bid', 'tender'])
            ->with('user')
            ->orderBy('amount', 'desc')
            ->paginate(15);

        // Map and mask user names to match screenshot (e.g. 0750-باسم)
        $bids->getCollection()->transform(function($bid) {
            $user = $bid->user;
            if ($user) {
                $phoneSuffix = substr($user->phone ?? '1234', -4);
                $firstName = explode(' ', $user->name)[0] ?? 'مزايد';
                $bid->masked_name = $phoneSuffix . '-' . $firstName;
            } else {
                $bid->masked_name = 'مزايد مجهول';
            }
            return $bid;
        });

        return view('ads.bids-history', compact('ad', 'bids'));
    }

    /**
     * Display Bids & Offers received on user's ads.
     */
    public function myBidsOffers(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $user = Auth::user();
        $tab = $request->get('tab', 'offers'); // 'offers' or 'auctions'
        $role = $request->get('role', 'seller'); // 'seller' (received) or 'buyer' (submitted)

        // Get only the latest bid record per ad for the buyer, or per buyer per ad for the seller
        $latestIdsQuery = Bid::select(DB::raw('MAX(id) as id'));
        if ($role === 'buyer') {
            $latestIdsQuery->where('user_id', $user->id)
                           ->groupBy('ad_id');
        } else {
            $latestIdsQuery->whereHas('ad', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->groupBy('ad_id', 'user_id');
        }
        $latestIds = $latestIdsQuery->pluck('id');

        $query = Bid::whereIn('id', $latestIds);

        if ($role === 'buyer') {
            $query->where('user_id', $user->id);
            if ($tab === 'offers') {
                $query->whereHas('ad', function($q) {
                    $q->where('type', 'direct_purchase');
                })->where('type', 'offer');
            } else {
                $query->whereHas('ad', function($q) {
                    $q->whereIn('type', ['electronic_auction', 'hybrid_auction', 'tender']);
                });
                
                if ($request->filled('type')) {
                    $query->whereHas('ad', function($q) use ($request) {
                        if ($request->type === 'auction') {
                            $q->whereIn('type', ['electronic_auction', 'hybrid_auction']);
                        } else {
                            $q->where('type', $request->type);
                        }
                    });
                }

                if ($request->filled('is_enfaz')) {
                    $query->whereHas('ad', function($q) {
                        $q->where('is_enfaz', 1);
                    });
                }
                
                $query->whereIn('type', ['bid', 'tender', 'auto_tender']);
            }
        } else {
            $query->whereHas('ad', function($q) use ($user, $tab, $request) {
                $q->where('user_id', $user->id);
                if ($tab === 'offers') {
                    $q->where('type', 'direct_purchase');
                } else {
                    if ($request->filled('type')) {
                        if ($request->type === 'auction') {
                            $q->whereIn('type', ['electronic_auction', 'hybrid_auction']);
                        } else {
                            $q->where('type', $request->type);
                        }
                    } else {
                        $q->whereIn('type', ['electronic_auction', 'hybrid_auction', 'tender']);
                    }

                    if ($request->filled('is_enfaz')) {
                        $q->where('is_enfaz', 1);
                    }
                }
            });
        }

        $query->with(['ad.category', 'ad.city', 'ad.user', 'user']);

        // Filter: Status (all, accepted, rejected, active, upcoming)
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            if ($status === 'accepted') {
                $query->where('status', 'accepted');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected');
            } elseif ($status === 'active') {
                $query->whereIn('status', ['active', 'counter']);
            } elseif ($status === 'upcoming') {
                $query->whereHas('ad', function($q) {
                    $q->where('status', 'upcoming');
                });
            }
        }

        // Filter: Keywords (Search)
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->whereHas('ad', function($aq) use ($request) {
                    $aq->where('title', 'like', '%' . $request->q . '%');
                })->orWhereHas('user', function($uq) use ($request) {
                    $uq->where('name', 'like', '%' . $request->q . '%');
                });
            });
        }

        // Filter: City
        if ($request->has('city') && !empty($request->city)) {
            $query->whereHas('ad', function($q) use ($request) {
                $q->where('city_id', $request->city);
            });
        }

        // Filter: Category
        $activeCategory = null;
        if ($request->has('category') && !empty($request->category)) {
            $activeCategory = \App\Models\Category::with('properties')->find($request->category);
            $query->whereHas('ad', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter: Dynamic Category Properties (props)
        if ($activeCategory && $request->has('props') && is_array($request->props)) {
            foreach ($request->props as $propId => $val) {
                if ($val !== null && $val !== '') {
                    $query->whereHas('ad.adProperties', function($q) use ($propId, $val) {
                        $q->where('property_id', $propId);
                        if (is_array($val)) {
                            $q->whereIn('value', $val);
                        } else {
                            $q->where('value', $val);
                        }
                    });
                }
            }
        }

        // Filter: Advertiser Type (فرد / شركة / مندوب)
        if ($request->has('advertiser_type') && !empty($request->advertiser_type)) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('account_type', $request->advertiser_type);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('amount', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('amount', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $bids = $query->paginate(10)->appends($request->all());

        // Categories, Cities, Settings for filters
        $categories = \App\Models\Category::whereNull('parent_id')->with('subCategories')->get();
        $cities = \App\Models\City::all();
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('ads.bids-offers', compact('bids', 'categories', 'cities', 'settings', 'tab', 'activeCategory', 'role'));
    }

    /**
     * Accept a bid or offer.
     */
    public function acceptBidOffer(Request $request, $bidId)
    {
        $bid = Bid::with('ad')->findOrFail($bidId);

        $isSeller = ($bid->ad->user_id === Auth::id());
        $isBuyer = ($bid->user_id === Auth::id());

        if (!$isSeller && !($isBuyer && $bid->status === 'counter')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        DB::transaction(function() use ($bid, $isBuyer) {
            // Update this bid status to accepted
            $bid->update(['status' => 'accepted']);

            // Reject all other active bids on this ad
            Bid::where('ad_id', $bid->ad_id)
                ->where('id', '!=', $bid->id)
                ->whereIn('status', ['active', 'counter'])
                ->update(['status' => 'rejected']);

            // Mark the ad as finished
            $bid->ad->update(['status' => 'finished']);

            // Create Order
            \App\Models\Order::create([
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'ad_id' => $bid->ad_id,
                'buyer_id' => $bid->user_id,
                'seller_id' => $bid->ad->user_id,
                'final_price' => $bid->amount,
                'status' => 'pending_payment',
            ]);

            // Notify the other party
            $notifyUserId = $isBuyer ? $bid->ad->user_id : $bid->user_id;
            $title = $isBuyer ? 'تم قبول عرضك المضاد! 🎉' : 'تم قبول عرضك! 🎉';
            $content = $isBuyer 
                ? 'تم قبول عرضك المضاد بقيمة ' . number_format($bid->amount) . ' ريال من قبل المشتري على إعلان "' . $bid->ad->title . '".'
                : 'تم قبول عرضك بقيمة ' . number_format($bid->amount) . ' ريال على إعلان "' . $bid->ad->title . '". يرجى الذهاب لصفحة مشترياتك لإتمام الدفع.';

            try {
                Notification::create([
                    'user_id' => $notifyUserId,
                    'title' => $title,
                    'content' => $content,
                    'type' => 'notification',
                    'status' => 'sent',
                ]);
            } catch (\Exception $e) {
                \Log::warning('Notification failed: ' . $e->getMessage());
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم قبول العرض والاتفاق بنجاح! 🎉'
        ]);
    }

    /**
     * Reject a bid or offer.
     */
    public function rejectBidOffer(Request $request, $bidId)
    {
        $bid = Bid::with('ad')->findOrFail($bidId);

        $isSeller = ($bid->ad->user_id === Auth::id());
        $isBuyer = ($bid->user_id === Auth::id());

        if (!$isSeller && !($isBuyer && $bid->status === 'counter')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        $bid->update(['status' => 'rejected']);

        $notifyUserId = $isBuyer ? $bid->ad->user_id : $bid->user_id;
        $title = 'تم رفض العرض';
        $content = $isBuyer 
            ? 'عذراً، تم رفض عرضك المضاد بقيمة ' . number_format($bid->amount) . ' ريال من قبل المشتري على إعلان "' . $bid->ad->title . '".'
            : 'عذراً، تم رفض عرضك بقيمة ' . number_format($bid->amount) . ' ريال على إعلان "' . $bid->ad->title . '".';

        try {
            Notification::create([
                'user_id' => $notifyUserId,
                'title' => $title,
                'content' => $content,
                'type' => 'notification',
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفض العرض بنجاح.'
        ]);
    }

    /**
     * Make a counter offer on a bid/offer.
     */
    public function counterBidOffer(Request $request, $bidId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $bid = Bid::with('ad')->findOrFail($bidId);

        $isSeller = ($bid->ad->user_id === Auth::id());
        $isBuyer = ($bid->user_id === Auth::id());

        if (!$isSeller && !$isBuyer) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        // Track remaining counters in session
        $sessionKey = "bid_counter_limit_" . $bid->id;
        $remaining = session()->get($sessionKey, 2);

        if ($remaining <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'لقد استنفدت الحد الأقصى للعروض المضادة على هذا العرض.'
            ], 400);
        }

        $remaining--;
        session()->put($sessionKey, $remaining);

        // Update bid amount and status (waiting for the other party)
        $newStatus = $isSeller ? 'counter' : 'active';
        $oldAmount = $bid->amount;
        $bid->update([
            'amount' => $request->amount,
            'status' => $newStatus
        ]);

        // Notify the other party about the counter-offer
        $notifyUserId = $isSeller ? $bid->user_id : $bid->ad->user_id;
        $title = $isSeller ? 'عرض مضاد من البائع 💰' : 'عرض مضاد من المشتري 💰';
        $content = $isSeller 
            ? 'قدم البائع عرضاً مضاداً بقيمة ' . number_format($request->amount) . ' ريال بدلاً من ' . number_format($oldAmount) . ' ريال على إعلان "' . $bid->ad->title . '". يمكنك قبوله أو تقديم سومة جديدة.'
            : 'قدم المشتري عرضاً مضاداً بقيمة ' . number_format($request->amount) . ' ريال بدلاً من ' . number_format($oldAmount) . ' ريال على إعلان "' . $bid->ad->title . '". يمكنك قبوله أو تقديم سومة جديدة.';

        try {
            Notification::create([
                'user_id' => $notifyUserId,
                'title' => $title,
                'content' => $content,
                'type' => 'notification',
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال عرضك المضاد بنجاح.',
            'data' => [
                'new_amount' => $request->amount,
                'remaining' => $remaining
            ]
        ]);
    }

    /**
     * Get details of a bid/offer for modal popup.
     */
    public function getDetails(Request $request, $bidId)
    {
        $bid = Bid::with(['ad.category', 'ad.city', 'user'])->findOrFail($bidId);

        if ($bid->ad->user_id !== Auth::id() && $bid->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك.'
            ], 403);
        }

        $sessionKey = "bid_counter_limit_" . $bid->id;
        $remaining = session()->get($sessionKey, 2);

        // Format dates
        $dateStr = $bid->updated_at->format('Y/m/d - h:i:s a');
        $dateStr = str_replace(['am', 'pm'], ['ص', 'م'], $dateStr);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $bid->id,
                'amount' => $bid->amount,
                'status' => $bid->status,
                'created_at' => $dateStr,
                'remaining_offers' => $remaining,
                'buyer' => [
                    'name' => $bid->user->name,
                    'phone' => $bid->user->phone,
                    'account_type_lbl' => $bid->user->account_type == 'company' ? 'شركة' : ($bid->user->account_type == 'partner' ? 'شريك' : ($bid->user->account_type == 'representative' ? 'مندوب' : 'فرد')),
                    'account_type' => $bid->user->account_type,
                    'joined_at' => $bid->user->created_at ? $bid->user->created_at->format('Y/m/d') : 'قديم'
                ],
                'ad' => [
                    'id' => $bid->ad->id,
                    'title' => $bid->ad->title,
                    'price' => $bid->ad->price,
                    'type_lbl' => $bid->ad->type == 'direct_purchase' ? 'شراء فوري' : 'مزاد',
                    'category' => $bid->ad->category->name ?? 'غير مصنف'
                ]
            ]
        ]);
    }
}
