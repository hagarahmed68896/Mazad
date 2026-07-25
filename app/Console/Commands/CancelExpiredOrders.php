<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Bid;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel orders pending payment that have exceeded their award duration and reopen the auction';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting checks for expired orders...');

        // Fetch all orders pending payment
        $orders = Order::where('status', 'pending_payment')->with('ad')->get();

        if ($orders->isEmpty()) {
            $this->info('No orders in pending_payment state.');
            return 0;
        }

        $processedCount = 0;

        foreach ($orders as $order) {
            if (!$order->ad) {
                $this->warn("Order ID {$order->id} has no associated Ad. Skipping.");
                continue;
            }

            // Determine the award duration (payment window) in days
            $durationDays = (int) ($order->award_duration ?: ($order->ad->award_duration ?: 3));
            $expirationTime = $order->created_at->addDays($durationDays);

            if (now()->greaterThanOrEqualTo($expirationTime)) {
                $this->info("Order ID {$order->id} (No: {$order->order_number}) has expired. Expiration: {$expirationTime->toDateTimeString()}. Processing cancellation.");

                DB::transaction(function () use ($order) {
                    // 1. Cancel the order
                    $order->update(['status' => 'cancelled']);

                    // 2. Re-activate the advertisement
                    $order->ad->update(['status' => 'active']);

                    // 3. Mark the buyer's deposit on this ad as forfeited
                    Bid::where('ad_id', $order->ad_id)
                        ->where('user_id', $order->buyer_id)
                        ->where('type', 'deposit')
                        ->update(['status' => 'forfeited']);

                    // 4. Send notification to the buyer
                    $buyerNotif = new Notification([
                        'user_id' => $order->buyer_id,
                        'type' => 'alert',
                        'title' => 'تم إلغاء الطلب ومصادرة التأمين ⚠️',
                        'content' => "تم إلغاء طلبك رقم #{$order->order_number} ومصادرة مبلغ التأمين لعدم السداد خلال المهلة المحددة.",
                        'status' => 'sent'
                    ]);
                    $buyerNotif->custom_payload = [
                        'order_id' => (string) $order->id,
                        'ad_id' => (string) $order->ad_id,
                    ];
                    $buyerNotif->save();

                    // 5. Send notification to the seller
                    $sellerNotif = new Notification([
                        'user_id' => $order->seller_id,
                        'type' => 'alert',
                        'title' => 'إعادة فتح المزاد تلقائياً 🔄',
                        'content' => "انتهت مهلة المشتري للدفع للطلب رقم #{$order->order_number} دون سداد. تم إلغاء الطلب وإعادة فتح المزاد الخاص بك تلقائياً.",
                        'status' => 'sent'
                    ]);
                    $sellerNotif->custom_payload = [
                        'click_action' => 'ad_details',
                        'ad_id' => (string) $order->ad_id,
                    ];
                    $sellerNotif->save();

                    // 6. Send notification to all other bidders (previous participants)
                    $otherBidders = Bid::where('ad_id', $order->ad_id)
                        ->whereNotIn('user_id', [$order->buyer_id, $order->seller_id])
                        ->pluck('user_id')
                        ->unique();

                    foreach ($otherBidders as $bidderId) {
                        $bidderNotif = new Notification([
                            'user_id' => $bidderId,
                            'type' => 'alert',
                            'title' => 'إعادة فتح المزاد للفرصة الثانية 📣',
                            'content' => "تم إعادة فتح المزاد على الإعلان \"{$order->ad->title}\" مجدداً لعدم التزام المشتري الأول بالسداد. يمكنك الدخول والمزايدة مرة أخرى الآن!",
                            'status' => 'sent'
                        ]);
                        $bidderNotif->custom_payload = [
                            'click_action' => 'ad_details',
                            'ad_id' => (string) $order->ad_id,
                        ];
                        $bidderNotif->save();
                    }
                });

                Log::info("Order #{$order->order_number} was automatically cancelled and its Ad #{$order->ad_id} was reactivated due to payment expiry.");
                $processedCount++;
            }
        }

        $this->info("Completed. Cancelled {$processedCount} expired orders.");
        return 0;
    }
}
