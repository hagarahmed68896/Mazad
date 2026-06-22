<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Standardized API Response
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
     * Get All FAQs
     */
    public function faqs()
    {
        $faqs = Faq::where('status', true)->latest()->get();

        // Seed mock FAQs if database is empty
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
                ]
            ];
            foreach ($mockFaqs as $f) {
                Faq::create($f);
            }
            $faqs = Faq::where('status', true)->latest()->get();
        }

        return $this->apiResponse(true, 'قائمة الأسئلة الشائعة', $faqs);
    }

    /**
     * Get Contact Info & Social Media Links
     */
    public function contactInfo()
    {
        $settings = \App\Models\Setting::where('group', 'contacts')->pluck('value', 'key')->toArray();

        // Parse social links if stored as JSON string
        $socialLinks = [];
        if (isset($settings['social_links'])) {
            $decoded = json_decode($settings['social_links'], true);
            if (is_array($decoded)) {
                $socialLinks = $decoded;
            }
        }

        $data = [
            'phone' => $settings['phone'] ?? '+966 50 123 4567',
            'email' => $settings['email'] ?? 'support@yourapp.sa',
            'copyright' => $settings['copyright'] ?? '© 2026 جميع الحقوق محفوظة',
            'social_links' => $socialLinks
        ];

        return $this->apiResponse(true, 'بيانات التواصل والدعم الفني', $data);
    }

    /**
     * Get Privacy Policy
     */
    public function privacy()
    {
        $privacyPolicy = [
            [
                'id' => 1,
                'title' => '1. استخدام المنصة',
                'content' => 'باستخدامك للمنصة، فإنك توافق على استخدام الخدمات بطريقة قانونية ومسؤولة ووفقاً للسياسات المعلنة. يُمنع استخدام المنصة بأي شكل يلحق الضرر بالخوادم أو بالأنظمة الأمنية المخصصة للمنصة أو بالبنية التحتية.'
            ],
            [
                'id' => 2,
                'title' => '2. إنشاء الحساب',
                'content' => 'يتعين على المستخدم تقديم معلومات دقيقة وصحيحة ومحدثة عند التسجيل في المنصة. تقع على عاتق المستخدم كامل المسؤولية في الحفاظ على سرية معلومات حسابه وكلمة المرور، ويُمنع منعاً باتاً مشاركة بيانات الدخول مع أي طرف ثالث تحت أي ظرف من الظروف.'
            ],
            [
                'id' => 3,
                'title' => '3. حقوق الملكية الفكرية',
                'content' => 'جميع المحتويات والنصوص والتصاميم والشعارات والأيقونات والبرمجيات المتوافرة ضمن المنصة هي ملكية فكرية حصرية ومحمية بموجب حقوق النشر والعلامات التجارية المحلية والدولية. لا يحق لأي شخص نسخ أو استخدام هذه المحتويات دون إذن كتابي مسبق.'
            ],
            [
                'id' => 4,
                'title' => '4. استخدام البيانات',
                'content' => 'نحتفظ بالحق في استخدام البيانات والمعلومات غير الشخصية التي يتم جمعها بطرق تلقائية أو التي يقدمها المستخدم بغرض تحسين جودة الخدمات، وتطوير المنتجات، وتخصيص التنبيهات واهتمامات المزايدة الفعالة مع الحفاظ الكامل على أمان البيانات الشخصية وتشفيرها.'
            ],
            [
                'id' => 5,
                'title' => '5. إنهاء الحساب',
                'content' => 'يحق لإدارة المنصة وبموجب تقديرها الخاص تعليق أو إنهاء حساب المستخدم أو حظر وصوله للمنصة والخدمات في حال ثبوت انتهاك بنود الشروط والأحكام أو سياسة الخصوصية، أو ممارسة المزايدات الوهمية والعشوائية، أو إساءة الاستخدام.'
            ]
        ];

        return $this->apiResponse(true, 'سياسة الخصوصية', $privacyPolicy);
    }

    /**
     * Get Terms and Conditions
     */
    public function terms()
    {
        $termsConditions = [
            [
                'id' => 1,
                'title' => '1. استخدام المنصة',
                'content' => 'باستخدامك للمنصة، فإنك توافق على استخدام الخدمات بطريقة قانونية ومسؤولة ووفقاً للسياسات المعلنة. يُمنع استخدام المنصة بأي شكل يلحق الضرر بالخوادم أو بالأنظمة الأمنية المخصصة للمنصة أو بالبنية التحتية.'
            ],
            [
                'id' => 2,
                'title' => '2. إنشاء الحساب',
                'content' => 'يتعين على المستخدم تقديم معلومات دقيقة وصحيحة ومحدثة عند التسجيل في المنصة. تقع على عاتق المستخدم كامل المسؤولية في الحفاظ على سرية معلومات حسابه وكلمة المرور، ويُمنع منعاً باتاً مشاركة بيانات الدخول مع أي طرف ثالث تحت أي ظرف من الظروف.'
            ],
            [
                'id' => 3,
                'title' => '3. حقوق الملكية الفكرية',
                'content' => 'جميع المحتويات والنصوص والتصاميم والشعارات والأيقونات والبرمجيات المتوافرة ضمن المنصة هي ملكية فكرية حصرية ومحمية بموجب حقوق النشر والعلامات التجارية المحلية والدولية. لا يحق لأي شخص نسخ أو استخدام هذه المحتويات دون إذن كتابي مسبق.'
            ],
            [
                'id' => 4,
                'title' => '4. استخدام البيانات',
                'content' => 'نحتفظ بالحق في استخدام البيانات والمعلومات غير الشخصية التي يتم جمعها بطرق تلقائية أو التي يقدمها المستخدم بغرض تحسين جودة الخدمات، وتطوير المنتجات، وتخصيص التنبيهات واهتمامات المزايدة الفعالة مع الحفاظ الكامل على أمان البيانات الشخصية وتشفيرها.'
            ],
            [
                'id' => 5,
                'title' => '5. إنهاء الحساب',
                'content' => 'يحق لإدارة المنصة وبموجب تقديرها الخاص تعليق أو إنهاء حساب المستخدم أو حظر وصوله للمنصة والخدمات في حال ثبوت انتهاك بنود الشروط والأحكام أو سياسة الخصوصية، أو ممارسة المزايدات الوهمية والعشوائية، أو إساءة الاستخدام.'
            ]
        ];

        return $this->apiResponse(true, 'الشروط والأحكام', $termsConditions);
    }

    /**
     * Submit Support Ticket
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

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^5[0-9]{8}$/'],
            'description' => 'required|string|min:10',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'description.required' => 'وصف المشكلة مطلوب.',
            'description.min' => 'يرجى كتابة تفاصيل المشكلة بحد أدنى 10 أحرف.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $userId = Auth::guard('sanctum')->id();

        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . strtoupper(Str::random(8)),
            'type' => 'inquiry',
            'user_id' => $userId,
            'phone' => $request->phone,
            'reported_about' => 'الدعم الفني والشكاوى (تطبيق)',
            'description' => $request->description,
            'status' => 'under_review',
        ]);

        $ticket->logs()->create([
            'action' => 'ticket_created',
            'note' => 'تم إنشاء تذكرة الدعم بنجاح عبر التطبيق.'
        ]);

        return $this->apiResponse(true, 'تم تقديم طلب الدعم بنجاح', [
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i')
        ]);
    }

    /**
     * Get My Inquiries
     */
    public function myInquiries(Request $request)
    {
        $user = $request->user();
        $tickets = Ticket::where('user_id', $user->id)
            ->with(['logs'])
            ->latest()
            ->get();

        // Seed mock tickets for display if empty
        if ($tickets->isEmpty()) {
            $sampleTickets = [
                [
                    'ticket_number' => 'TCK-' . strtoupper(Str::random(8)),
                    'type' => 'inquiry',
                    'user_id' => $user->id,
                    'phone' => $user->phone ?? '0512345678',
                    'reported_about' => 'كيف تختار العقار؟',
                    'description' => 'أريد معرفة المعايير الأساسية لاختيار العقار المناسب في المزادات وكيفية التثبت من الصكوك.',
                    'status' => 'resolved',
                    'notes' => 'تم الرد: لاختيار العقار المناسب، يرجى مراجعة الصك الإلكتروني المرفق في صفحة المزاد، والتنسيق لمعاينة العقار على الطبيعة من خلال رقم ممثل البيع الموضح.'
                ]
            ];
            foreach ($sampleTickets as $st) {
                $t = Ticket::create($st);
                $t->logs()->create([
                    'action' => 'resolved',
                    'note' => 'تم الرد على استفسارك وتزويدك بكافة النصائح المطلوبة.'
                ]);
            }
            $tickets = Ticket::where('user_id', $user->id)->with(['logs'])->latest()->get();
        }

        $formattedTickets = $tickets->map(function ($ticket) {
            return [
                'ticket_number' => $ticket->ticket_number,
                'reported_about' => $ticket->reported_about,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'status_lbl' => $ticket->status === 'resolved' ? 'تم الحل' : 'قيد المراجعة',
                'created_at' => $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i') : null,
                'reply' => $ticket->notes,
                'logs' => $ticket->logs->map(function ($log) {
                    return [
                        'action' => $log->action,
                        'note' => $log->note,
                        'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i') : null
                    ];
                })
            ];
        });

        return $this->apiResponse(true, 'قائمة استفساراتي', $formattedTickets);
    }

    /**
     * Get Social Media Links
     */
    public function socialMediaLinks()
    {
        $settings = \App\Models\Setting::where('group', 'contacts')->pluck('value', 'key')->toArray();

        $socialLinks = [];
        if (isset($settings['social_links'])) {
            $decoded = json_decode($settings['social_links'], true);
            if (is_array($decoded)) {
                $socialLinks = $decoded;
            }
        }

        // Return fallback if empty
        if (empty($socialLinks)) {
            $socialLinks = [
                ['platform' => 'facebook', 'url' => 'https://facebook.com'],
                ['platform' => 'twitter', 'url' => 'https://twitter.com'],
                ['platform' => 'instagram', 'url' => 'https://instagram.com'],
                ['platform' => 'snapchat', 'url' => 'https://snapchat.com'],
            ];
        }

        return $this->apiResponse(true, 'روابط التواصل الاجتماعي', $socialLinks);
    }
}
