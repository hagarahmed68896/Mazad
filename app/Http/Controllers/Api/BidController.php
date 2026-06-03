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
            'notify_near_limit' => (bool) $request->notify_near_limit,
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

    /**
     * Get bids & offers received on authenticated user's ads via API.
     */
    public function myBidsOffers(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'offers'); // 'offers' or 'auctions'
        $role = $request->get('role', 'seller'); // 'seller' (received) or 'buyer' (submitted)

        $query = Bid::query();

        if ($role === 'buyer') {
            $query->where('user_id', $user->id);
            if ($tab === 'offers') {
                $query->whereHas('ad', function($q) {
                    $q->where('type', 'direct_purchase');
                })->where('type', 'offer');
            } else {
                $query->whereHas('ad', function($q) {
                    $q->whereIn('type', ['electronic_auction', 'hybrid_auction', 'tender']);
                })->whereIn('type', ['bid', 'tender', 'auto_tender']);
            }
        } else {
            $query->whereHas('ad', function($q) use ($user, $tab) {
                $q->where('user_id', $user->id);
                if ($tab === 'offers') {
                    $q->where('type', 'direct_purchase');
                } else {
                    $q->whereIn('type', ['electronic_auction', 'hybrid_auction', 'tender']);
                }
            });
        }

        $query->with(['ad.category', 'ad.city', 'ad.user', 'user']);

        // Filter: Status (all, accepted, rejected, active)
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            if ($status === 'accepted') {
                $query->where('status', 'accepted');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected');
            } elseif ($status === 'active') {
                $query->where('status', 'active');
            }
        }

        // Keywords
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->whereHas('ad', function($aq) use ($request) {
                    $aq->where('title', 'like', '%' . $request->q . '%');
                })->orWhereHas('user', function($uq) use ($request) {
                    $uq->where('name', 'like', '%' . $request->q . '%');
                });
            });
        }

        // Category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->whereHas('ad', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Advertiser Type (فرد / شركة / مندوب)
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

        $bids = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'قائمة المزايدات والعروض الواردة',
            'data' => $this->formatPagination($bids)
        ]);
    }

    /**
     * Accept a bid or offer via API.
     */
    public function acceptBidOffer(Request $request, $bidId)
    {
        $bid = Bid::with('ad')->findOrFail($bidId);

        if ($bid->ad->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($bid) {
            $bid->update(['status' => 'accepted']);
            Bid::where('ad_id', $bid->ad_id)->where('id', '!=', $bid->id)->where('status', 'active')->update(['status' => 'rejected']);
            $bid->ad->update(['status' => 'finished']);

            \App\Models\Order::create([
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'ad_id' => $bid->ad_id,
                'buyer_id' => $bid->user_id,
                'seller_id' => Auth::id(),
                'final_price' => $bid->amount,
                'status' => 'pending_payment',
            ]);

            try {
                \App\Models\Notification::create([
                    'user_id' => $bid->user_id,
                    'title' => 'تم قبول عرضك! 🎉',
                    'content' => 'تم قبول عرضك بقيمة ' . number_format($bid->amount) . ' ريال على إعلان "' . $bid->ad->title . '". يرجى الذهاب لصفحة مشترياتك لإتمام الدفع.',
                    'type' => 'bid_accepted',
                ]);
            } catch (\Exception $e) {
                // ignore
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم قبول العرض بنجاح 🎉'
        ]);
    }

    /**
     * Reject a bid or offer via API.
     */
    public function rejectBidOffer(Request $request, $bidId)
    {
        $bid = Bid::with('ad')->findOrFail($bidId);

        if ($bid->ad->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        $bid->update(['status' => 'rejected']);

        try {
            \App\Models\Notification::create([
                'user_id' => $bid->user_id,
                'title' => 'تم رفض عرضك',
                'content' => 'عذراً، تم رفض عرضك بقيمة ' . number_format($bid->amount) . ' ريال على إعلان "' . $bid->ad->title . '".',
                'type' => 'bid_rejected',
            ]);
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفض العرض بنجاح.'
        ]);
    }

    /**
     * Make a counter offer via API.
     */
    public function counterBidOffer(Request $request, $bidId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'validity_days' => 'nullable|integer|min:1',
        ]);

        $bid = Bid::with('ad')->findOrFail($bidId);

        if ($bid->ad->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        $sessionKey = "bid_counter_limit_" . $bid->id;
        $remaining = session()->get($sessionKey, 2);

        if ($remaining <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'لقد استنفدت الحد الأقصى للعروض المضادة.'
            ], 400);
        }

        $remaining--;
        session()->put($sessionKey, $remaining);

        $oldAmount = $bid->amount;
        $bid->update([
            'amount' => $request->amount,
            'validity_days' => $request->has('validity_days') ? $request->validity_days : $bid->validity_days,
            'status' => 'active'
        ]);

        try {
            \App\Models\Notification::create([
                'user_id' => $bid->user_id,
                'title' => 'عرض مضاد من البائع 💰',
                'content' => 'قدم البائع عرضاً مضاداً بقيمة ' . number_format($request->amount) . ' ريال بدلاً من ' . number_format($oldAmount) . ' ريال على إعلان "' . $bid->ad->title . '".',
                'type' => 'bid_counter',
            ]);
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال العرض المضاد بنجاح.',
            'data' => [
                'new_amount' => (float) $request->amount,
                'validity_days' => $bid->validity_days,
                'remaining' => $remaining
            ]
        ]);
    }
}
