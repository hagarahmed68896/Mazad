<?php

namespace App\Http\Controllers;

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

class ServiceOrderController extends Controller
{
    /**
     * Show the checkout page for the service
     */
    public function checkout($id)
    {
        $service = Service::find($id);
        if (!$service) {
            // Fallback to service ads in the ads table
            $service = Ad::find($id);
        }

        if (!$service) {
            abort(404, 'الخدمة غير موجودة');
        }

        if (Auth::id() === $service->user_id) {
            return redirect()->back()->with('error', 'لا يمكنك طلب خدمة خاصة بك.');
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
            return redirect()->back()->with('error', 'لديك طلب نشط بالفعل لهذه الخدمة بانتظار الإنجاز من الشريك.');
        }

        $settings = Setting::all()->pluck('value', 'key');
        
        // Calculate platform commission/fee
        $commRate = (float) ($settings['commission_rate'] ?? 0.0);

        $price = (float) $service->price;
        $commission = round($price * ($commRate / 100), 2);
        $totalPrice = round($price + $commission, 2);

        return view('services.checkout', compact('service', 'price', 'commission', 'totalPrice', 'settings'));
    }

    /**
     * Process checkout payment
     */
    public function processCheckout(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            $service = Ad::find($id);
        }

        if (!$service) {
            abort(404, 'الخدمة غير موجودة');
        }

        if (Auth::id() === $service->user_id) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك طلب خدمة خاصة بك.'], 403);
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
            return response()->json(['success' => false, 'message' => 'لديك طلب نشط بالفعل لهذه الخدمة بانتظار الإنجاز من الشريك.'], 400);
        }

        $request->validate([
            'card_name'   => 'required|string|max:255',
            'card_number' => 'required|string|min:15|max:20',
            'expiry'      => 'required|string',
            'cvv'         => 'required|string|min:3|max:4',
            'payment_method' => 'required|string|in:card,mada'
        ]);

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

            return response()->json([
                'success' => true,
                'message' => 'تم الدفع بنجاح! تم تسجيل طلبك وإشعار الشريك منفذ الخدمة.',
                'redirect_url' => route('purchases.my', ['status' => 'services'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشلت عملية الدفع، يرجى المحاولة لاحقاً: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Partner's Received Service Orders list
     */
    public function partnerOrders(Request $request)
    {
        $user = Auth::user();
        if ($user->account_type !== 'partner') {
            abort(403, 'غير مصرح للوصول لهذه الصفحة');
        }

        $status = $request->query('status', 'all'); // 'all', 'paid', 'completed'

        // Calculate Partner Dashboard Stats
        $totalSalesAmount = ServiceOrder::where('seller_id', $user->id)
            ->whereIn('status', ['paid', 'under_review', 'completed'])
            ->sum('price');
        $netEarnings = $totalSalesAmount * 0.975; // 97.5% of total sales (excluding 2.5% platform commission)
        $newOrdersCount = ServiceOrder::where('seller_id', $user->id)
            ->where('status', 'paid')
            ->count();
        
        $totalOrdersCount = ServiceOrder::where('seller_id', $user->id)->count();
        $completedOrdersCount = ServiceOrder::where('seller_id', $user->id)
            ->whereIn('status', ['completed', 'under_review'])
            ->count();
        $soldRate = $totalOrdersCount > 0 ? round(($completedOrdersCount / $totalOrdersCount) * 100) : 0;

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

        $orders = $query->paginate(10)->appends($request->query());

        return view('ads.partner-orders', compact(
            'orders', 'status', 'totalSalesAmount', 'netEarnings', 'newOrdersCount', 'soldRate'
        ));
    }

    /**
     * Partner submits completion report for a service order
     */
    public function completeOrder(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->account_type !== 'partner') {
            abort(403);
        }

        $order = ServiceOrder::where('seller_id', $user->id)
            ->where('status', 'paid')
            ->findOrFail($id);

        $request->validate([
            'report_text' => 'required|string|max:10000',
            'report_file' => 'nullable|file|mimes:pdf,jpeg,jpg,png,doc,docx,webp|max:10240',
        ]);

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

            return redirect()->back()->with('success', 'تم تقديم تقرير الإنجاز وإكمال الطلب بنجاح! تم تسجيل مستحقاتك في التسويات المالية.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء إتمام الخدمة: ' . $e->getMessage());
        }
    }
}
