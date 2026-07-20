<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Ad;
use App\Models\ServiceOrder;
use App\Models\Transaction;
use App\Models\FinancialSettlement;
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

        // Check for duplicate pending requests
        $existingOrder = ServiceOrder::where('buyer_id', $user->id)
            ->where(function($q) use ($service) {
                if ($service instanceof Service) {
                    $q->where('service_id', $service->id);
                } else {
                    $q->where('ad_id', $service->id);
                }
            })
            ->whereIn('status', ['paid', 'under_review'])
            ->first();

        if ($existingOrder) {
            return $this->apiResponse(false, 'لديك طلب نشط بالفعل لهذه الخدمة بانتظار الإنجاز من الشريك.', null, 400);
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

        // Check for duplicate pending requests
        $existingOrder = ServiceOrder::where('buyer_id', Auth::id())
            ->where(function($q) use ($service) {
                if ($service instanceof Service) {
                    $q->where('service_id', $service->id);
                } else {
                    $q->where('ad_id', $service->id);
                }
            })
            ->whereIn('status', ['paid', 'under_review'])
            ->first();

        if ($existingOrder) {
            return $this->apiResponse(false, 'لديك طلب نشط بالفعل لهذه الخدمة بانتظار الإنجاز من الشريك.', null, 400);
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

            // 3. Automated notification model alert to the seller
            $buyer = Auth::user();
            $sellerId = $service->user_id;

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
            $query->whereIn('status', ['completed', 'under_review']);
        } else {
            $query->whereIn('status', ['paid', 'under_review', 'completed']);
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

            // Notify the buyer (rating request)
            $buyerNotification = new Notification([
                'user_id' => $order->buyer_id,
                'type' => 'service_rate',
                'title' => 'تم إنجاز الخدمة بنجاح 🌟',
                'content' => "قام الشريك {$user->name} بإتمام خدمتك بنجاح ورفع تقرير الإنجاز للطلب #{$order->order_number}. يرجى الضغط هنا لتقييم الخدمة.",
                'status' => 'sent'
            ]);
            $buyerNotification->custom_payload = [
                'click_action' => 'service_rate',
                'service_id' => (string) ($order->service_id ?? $order->ad_id),
                'order_id' => (string) $order->id,
                'seller_id' => (string) $order->seller_id,
            ];
            $buyerNotification->save();

            // Notify the admin(s)
            $admins = \App\Models\User::where('account_type', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'alert',
                    'title' => 'طلب تسوية مالية جديد',
                    'content' => "قام الشريك {$user->name} بتقديم تقرير إنجاز للطلب #{$order->order_number} وتم إنشاء طلب تسوية مالية بقيمة {$order->price} ريال.",
                    'status' => 'sent'
                ]);
            }

            DB::commit();

            return $this->apiResponse(true, 'تم تقديم تقرير الإنجاز وإكمال الطلب بنجاح! تم تسجيل مستحقاتك في التسويات المالية.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse(false, 'حدث خطأ أثناء إتمام الخدمة: ' . $e->getMessage(), null, 500);
        }
    }
}
