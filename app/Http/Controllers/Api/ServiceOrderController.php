<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Ad;
use App\Models\ServiceOrder;
use App\Models\Transaction;
use App\Models\FinancialSettlement;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Setting;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ServiceOrderController extends Controller
{
    /**
     * Standardized JSON Response Helper
     */
    protected function apiResponse($success, $message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Get Service Billing Details for Checkout
     */
    public function checkout($id)
    {
        $service = Service::find($id);
        if (!$service) {
            $service = Ad::find($id);
        }

        if (!$service) {
            return $this->apiResponse(false, 'الخدمة غير موجودة', null, 404);
        }

        $user = Auth::user();
        if ($user && $user->id === $service->user_id) {
            return $this->apiResponse(false, 'لا يمكنك طلب خدمة خاصة بك.', null, 403);
        }

        $settings = Setting::all()->pluck('value', 'key');
        $commRate = (float) ($settings['commission_rate'] ?? 0.0);

        $price = (float) $service->price;
        $commission = round($price * ($commRate / 100), 2);
        $totalPrice = round($price + $commission, 2);

        $partner = $service->user;

        $responseData = [
            'service_id' => $service->id,
            'title' => $service->title,
            'price' => $price,
            'commission_rate' => $commRate . '%',
            'commission_amount' => $commission,
            'total_price' => $totalPrice,
            'partner' => [
                'id' => $partner->id ?? null,
                'name' => $partner->name ?? null,
                'avatar' => $partner && $partner->avatar ? asset($partner->avatar) : null
            ]
        ];

        return $this->apiResponse(true, 'بيانات تسعير الخدمة جاهزة', $responseData);
    }

    /**
     * Process service payment and checkout
     */
    public function processCheckout(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            $service = Ad::find($id);
        }

        if (!$service) {
            return $this->apiResponse(false, 'الخدمة غير موجودة', null, 404);
        }

        if (Auth::id() === $service->user_id) {
            return $this->apiResponse(false, 'لا يمكنك طلب خدمة خاصة بك.', null, 403);
        }

        $validator = Validator::make($request->all(), [
            'card_name'   => 'required|string|max:255',
            'card_number' => 'required|string|min:15|max:20',
            'expiry'      => 'required|string',
            'cvv'         => 'required|string|min:3|max:4',
            'payment_method' => 'required|string|in:card,mada'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'بيانات البطاقة غير صالحة', $validator->errors(), 422);
        }

        $settings = Setting::all()->pluck('value', 'key');
        $commRate = (float) ($settings['commission_rate'] ?? 0.0);

        $price = (float) $service->price;
        $commission = round($price * ($commRate / 100), 2);
        $totalPrice = round($price + $commission, 2);

        DB::beginTransaction();

        try {
            // 1. Create the Service Order
            $order = ServiceOrder::create([
                'order_number' => 'SRV-' . strtoupper(Str::random(8)),
                'buyer_id'     => Auth::id(),
                'seller_id'    => $service->user_id,
                'service_id'   => $service instanceof Service ? $service->id : null,
                'ad_id'        => $service instanceof Ad ? $service->id : null,
                'price'        => $price,
                'commission_amount' => $commission,
                'total_price'  => $totalPrice,
                'status'       => 'paid',
                'payment_method' => $request->payment_method,
                'paid_at'      => now(),
            ]);

            // 2. Log a completed Transaction
            Transaction::create([
                'transaction_number' => 'TXN-' . strtoupper(Str::random(8)),
                'user_id'            => Auth::id(),
                'ad_id'              => $service instanceof Ad ? $service->id : null,
                'type'               => 'purchase',
                'amount'             => $totalPrice,
                'payment_method'     => $request->payment_method,
                'status'             => 'completed',
            ]);

            // 3. Automated Chat initiation and notification message
            $buyer = Auth::user();
            $sellerId = $service->user_id;

            // Find or create chat conversation
            $conversation = Conversation::where(function($q) use ($buyer, $sellerId) {
                $q->where(function($sq) use ($buyer, $sellerId) {
                    $sq->where('user_one_id', $buyer->id)->where('user_two_id', $sellerId);
                })->orWhere(function($sq) use ($buyer, $sellerId) {
                    $sq->where('user_one_id', $sellerId)->where('user_two_id', $buyer->id);
                });
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_one_id' => $buyer->id,
                    'user_two_id' => $sellerId,
                    'last_message_at' => now(),
                ]);
            }

            // Send notification message in chat
            $messageContent = "🚨 طلب خدمة جديد: لقد قام العميل [{$buyer->name}] بطلب خدمتك: \"{$service->title}\" ودفع التكاليف بالكامل شاملة عمولة المنصة بقيمة {$totalPrice} ريال.\n\nرقم الطلب: #{$order->order_number}\n\nيرجى تنفيذ الخدمة وإرفاق تقرير إنجاز الخدمة في صفحة طلباتك المستلمة لتسوية مستحقاتك وتحويلها إلى محفظتك المعتمدة.";
            
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $buyer->id,
                'content'         => $messageContent,
                'type'            => 'text'
            ]);

            // Send notification model alert to the seller
            Notification::create([
                'user_id' => $sellerId,
                'type' => 'alert',
                'title' => 'طلب خدمة جديد مدفوع',
                'content' => "لقد قام العميل {$buyer->name} بطلب خدمتك \"{$service->title}\" بنجاح. رقم الطلب #{$order->order_number}.",
                'status' => 'sent'
            ]);

            DB::commit();

            return $this->apiResponse(true, 'تم الدفع بنجاح! تم تسجيل طلبك وإشعار الشريك منفذ الخدمة.', [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_paid' => $totalPrice,
                'paid_at' => $order->paid_at
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse(false, 'فشلت عملية الدفع، يرجى المحاولة لاحقاً: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get Buyer Purchased Services
     */
    public function myPurchasedServices()
    {
        $user = Auth::user();
        $serviceOrders = ServiceOrder::where('buyer_id', $user->id)
            ->with(['service', 'ad', 'seller'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $serviceOrders->map(function($order) {
            $sAd = $order->service ?? $order->ad;
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'service_title' => $sAd->title ?? 'طلب خدمة شريك',
                'total_price' => $order->total_price,
                'status' => $order->status,
                'partner_name' => $order->seller->name ?? null,
                'paid_at' => $order->paid_at ? $order->paid_at->toDateTimeString() : null
            ];
        });

        return $this->apiResponse(true, 'قائمة خدماتي المشتراة', $data);
    }

    /**
     * Partner's Received Service Orders list
     */
    public function partnerOrders(Request $request)
    {
        $user = Auth::user();
        if ($user->account_type !== 'partner') {
            return $this->apiResponse(false, 'غير مصرح للوصول لهذه الصفحة', null, 403);
        }

        $status = $request->query('status', 'all'); // 'all', 'paid', 'completed'

        $query = ServiceOrder::where('seller_id', $user->id)
            ->with(['buyer', 'service', 'ad'])
            ->orderBy('created_at', 'desc');

        if ($status === 'paid') {
            $query->where('status', 'paid');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } else {
            $query->whereIn('status', ['paid', 'completed']);
        }

        $orders = $query->get();

        $data = $orders->map(function($order) {
            $sAd = $order->service ?? $order->ad;
            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'service_title' => $sAd->title ?? '-',
                'price' => $order->price,
                'status' => $order->status,
                'created_at' => $order->created_at ? $order->created_at->toDateTimeString() : null,
                'buyer' => [
                    'name' => $order->buyer->name ?? null,
                    'phone' => $order->buyer->phone ?? null
                ]
            ];
        });

        return $this->apiResponse(true, 'قائمة طلبات الخدمات الواردة', $data);
    }

    /**
     * Partner submits completion report for a service order
     */
    public function completeOrder(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->account_type !== 'partner') {
            return $this->apiResponse(false, 'غير مصرح للوصول لهذه الصفحة', null, 403);
        }

        $order = ServiceOrder::where('seller_id', $user->id)
            ->where('status', 'paid')
            ->find($id);

        if (!$order) {
            return $this->apiResponse(false, 'الطلب غير موجود أو تمت معالجته مسبقاً', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'report_text' => 'required|string|max:10000',
            'report_file' => 'nullable|file|mimes:pdf,jpeg,jpg,png,doc,docx,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'بيانات التقرير غير صالحة', $validator->errors(), 422);
        }

        $reportFilePath = null;
        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $filename = time() . '_report_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/reports');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }
            $file->move($destinationPath, $filename);
            $reportFilePath = 'uploads/reports/' . $filename;
        }

        DB::beginTransaction();

        try {
            // Update service order status to under_review
            $order->update([
                'status' => 'under_review',
                'report_text' => $request->report_text,
                'report_file' => $reportFilePath,
                'completed_at' => null,
            ]);

            // Create Financial Settlement for the partner
            FinancialSettlement::create([
                'settlement_number' => 'SET-' . strtoupper(Str::random(6)),
                'user_id' => $order->seller_id,
                'service_order_id' => $order->id,
                'ad_id' => $order->ad_id, // link if ad fallback
                'total_amount' => $order->price, // Payout partner their service cost
                'payment_method' => 'تحويل بنكي',
                'status' => 'pending'
            ]);

            // Notify the buyer
            Notification::create([
                'user_id' => $order->buyer_id,
                'type' => 'alert',
                'title' => 'تم إنجاز الخدمة ورفع التقرير',
                'content' => "قام الشريك {$user->name} بإتمام خدمتك بنجاح ورفع تقرير الإنجاز. رقم الطلب #{$order->order_number}.",
                'status' => 'sent'
            ]);

            DB::commit();

            return $this->apiResponse(true, 'تم تقديم تقرير الإنجاز وإكمال الطلب بنجاح! تم تسجيل مستحقاتك في التسويات المالية.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse(false, 'حدث خطأ أثناء إتمام الخدمة: ' . $e->getMessage(), null, 500);
        }
    }
}
