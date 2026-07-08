<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\Policy;
use App\Models\TermCondition;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Show all active FAQs
     */
    public function faqs()
    {
        $faqs = Faq::where('status', true)->latest()->get();

        // Seed default beautiful mock data if table is completely empty
        if ($faqs->isEmpty()) {
            $mockFaqs = [
                [
                    'question' => 'كيف يمكنني الاشتراك والمزايدة في المزاد؟',
                    'answer' => 'للمشاركة في أي مزاد، يجب أولاً إنشاء حساب وتفعيله، ثم الانتقال لصفحة المزاد المطلوبة والضغط على زر "اشترك في المزاد" ودفع قيمة التأمين المحددة للبدء بتقديم السومات.',
                    'status' => true,
                    'target_group' => '["all"]'
                ],
                [
                    'question' => 'ما هو نظام المزايدة التلقائي وكيف يعمل؟',
                    'answer' => 'نظام المزايدة التلقائي يتيح لك تحديد "الحد الأعلى" لمبلغ المزايدة الخاص بك. وسيقوم النظام تلقائياً بالمزايدة نيابة عنك بأقل زيادة ممكنة لتظل أنت المتصدر، حتى يصل المزاد لحدك الأقصى.',
                    'status' => true,
                    'target_group' => '["all"]'
                ],
                [
                    'question' => 'كيف أقوم بطلب شراء إعلان ذو شراء مباشر؟',
                    'answer' => 'في إعلانات الشراء المباشر، يمكنك الضغط على "شراء فوري" لتأكيد الطلب والدفع مباشرة بالسعر المحدد، أو يمكنك الضغط على "تقديم عرض" لتقديم سعر تفاوضي للبائع لمراجعته.',
                    'status' => true,
                    'target_group' => '["all"]'
                ],
                [
                    'question' => 'كيف يتم استرداد مبلغ تأمين المزاد؟',
                    'answer' => 'في حال لم يحالفك الحظ بالفوز بالمزاد، سيقوم النظام تلقائياً وبشكل فوري بإعادة مبلغ التأمين بالكامل إلى محفظتك الإلكترونية أو حسابك البنكي المسجل خلال 24 ساعة.',
                    'status' => true,
                    'target_group' => '["all"]'
                ]
            ];

            foreach ($mockFaqs as $f) {
                Faq::create($f);
            }
            $faqs = Faq::where('status', true)->latest()->get();
        }

        return view('pages.faqs', compact('faqs'));
    }

    /**
     * Show Privacy Policy
     */
    public function privacy()
    {
        // Fetch active policy of type 'privacy' or fetch TermCondition
        // Let's seed beautiful Arabic content for privacy policy
        return view('pages.privacy');
    }

    /**
     * Show Terms and Conditions
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Show Contact Us Page
     */
    public function contactUs()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('pages.contact', compact('settings'));
    }

    /**
     * Store new support ticket
     */
    public function storeTicket(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->input('phone');
            if ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                if (str_starts_with($phone, '966')) {
                    $phone = substr($phone, 3);
                }
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                $request->merge(['phone' => $phone]);
            }
        }

        $request->validate([
            'phone' => ['required', 'regex:/^5[0-9]{8}$/'],
            'description' => 'required|string|min:10',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'description.required' => 'وصف المشكلة مطلوب.',
            'description.min' => 'يرجى كتابة تفاصيل المشكلة بحد أدنى 10 أحرف لتسهيل مساعدتك.',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . strtoupper(Str::random(8)),
            'type' => 'inquiry',
            'user_id' => Auth::check() ? Auth::id() : null,
            'phone' => $request->phone,
            'reported_about' => 'الدعم الفني والشكاوى',
            'description' => $request->description,
            'status' => 'under_review',
        ]);

        // Create log
        $ticket->logs()->create([
            'action' => 'ticket_created',
            'note' => 'تم إنشاء تذكرة الدعم الفني بنجاح.'
        ]);

        if (Auth::check()) {
            return redirect()->route('inquiries.my')->with('success', 'تم إرسال تذكرتك بنجاح! رقم التذكرة هو: ' . $ticket->ticket_number);
        }

        return redirect()->back()->with('success', 'تم إرسال طلبك بنجاح! فريق الدعم الفني سيتواصل معك قريباً. رقم تذكرتك هو: ' . $ticket->ticket_number);
    }

    /**
     * Show user support tickets / inquiries
     */
    public function myInquiries()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول لمشاهدة استفساراتك.');
        }

        $tickets = Ticket::where('user_id', Auth::id())
            ->with(['logs'])
            ->latest()
            ->paginate(10);



        return view('pages.inquiries', compact('tickets'));
    }
}
