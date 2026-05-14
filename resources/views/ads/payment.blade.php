@extends('layouts.main')
@section('title', 'تفاصيل الدفع | مزاد')

@section('content')
<style>
    .navbar, .categories-nav, .footer { display: none !important; }
    body { background-color: #F8FAFC; }

    /* Card input styling */
    .pay-input {
        width: 100%;
        padding: 1rem 1.25rem;
        border-radius: 14px;
        border: 2px solid #F1F5F9;
        background: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.2s;
        direction: ltr;
        text-align: left;
        outline: none;
    }
    .pay-input:focus {
        border-color: #1A73E8;
        box-shadow: 0 0 0 4px rgba(26,115,232,0.1);
    }
    .pay-input::placeholder { color: #CBD5E1; direction: rtl; text-align: right; }

    /* Payment method tabs */
    .pay-tab {
        flex: 1;
        padding: 0.9rem;
        border: 2px solid #F1F5F9;
        border-radius: 16px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.25s;
    }
    .pay-tab.active {
        border-color: #1A73E8;
        background: #EBF4FF;
        box-shadow: 0 4px 12px rgba(26,115,232,0.15);
    }
    .pay-tab img { height: 28px; object-fit: contain; }

    /* Summary rows */
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #F1F5F9;
        font-weight: 700;
        color: #475569;
        font-size: 0.95rem;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row .val { color: #1E293B; }
    .summary-row.total { font-size: 1.15rem; font-weight: 900; }
    .summary-row.total .val { color: #1A73E8; }

    /* Security badge */
    .secure-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 12px;
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #166534;
    }

    /* Submit button */
    .pay-btn {
        width: 100%;
        padding: 1.1rem;
        background: linear-gradient(135deg, #1A73E8, #1557B0);
        color: white;
        border: none;
        border-radius: 16px;
        font-size: 1.1rem;
        font-weight: 900;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 8px 20px rgba(26,115,232,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    .pay-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(26,115,232,0.4); }
    .pay-btn:active { transform: translateY(0); }

    /* Skip link */
    .skip-link {
        display: block;
        text-align: center;
        margin-top: 1rem;
        color: #94A3B8;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        transition: color 0.2s;
    }
    .skip-link:hover { color: #475569; }

    /* Custom footer */
    .custom-login-footer {
        background: linear-gradient(135deg, #1557B0 0%, #0a3066 100%);
        padding: 2rem 0;
        margin-top: auto;
    }

    /* Card number formatting */
    .card-digits {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    /* Shimmer animation on submit */
    @keyframes shimmer {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    .pay-btn.loading {
        background: linear-gradient(90deg, #1A73E8 0%, #93C5FD 50%, #1A73E8 100%);
        background-size: 200% auto;
        animation: shimmer 1.5s linear infinite;
        pointer-events: none;
    }
</style>

<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white py-5 shadow-sm sticky top-0 z-50">
        <div class="container flex justify-between items-center">
            <div class="logo">
                <a href="/"> @include('partials.logo', ['size' => 'text-4xl']) </a>
            </div>

            <a href="{{ route('ads.show', $ad->id) }}"
               class="px-5 py-2 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                تخطي
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </a>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-grow container py-10 flex flex-col items-center">

        {{-- Page Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-2 rounded-full text-sm font-black mb-4">
                <i class="fa-solid fa-star"></i> تفعيل الإعلان المميز
            </div>
            <h1 class="text-3xl font-black text-gray-900 mb-2">→ تفاصيل الدفع</h1>
            <p class="text-gray-500 font-bold">أكمل عملية الدفع لتمييز إعلانك وإظهاره في المقدمة.</p>
        </div>

        <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

            {{-- LEFT: Payment Form --}}
            <div class="bg-white rounded-[28px] shadow-sm border border-gray-100 p-8 space-y-6">

                {{-- Payment Method Tabs --}}
                <div>
                    <p class="text-gray-700 font-black mb-3">طريقة الدفع</p>
                    <div class="flex gap-3">
                        <button type="button" id="tabMada" onclick="selectTab('mada')" class="pay-tab active">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Mada_Logo.svg/640px-Mada_Logo.svg.png" alt="مدى">
                        </button>
                        <button type="button" id="tabVisa" onclick="selectTab('visa')" class="pay-tab">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/640px-Visa_Inc._logo.svg.png" alt="Visa">
                        </button>
                        <button type="button" id="tabMaster" onclick="selectTab('mastercard')" class="pay-tab">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/640px-Mastercard-logo.svg.png" alt="Mastercard">
                        </button>
                    </div>
                    <input type="hidden" id="paymentMethod" name="payment_method" value="mada">
                </div>

                {{-- Card Form --}}
                <form action="{{ route('ads.payment.process', $ad->id) }}" method="POST" id="payForm">
                    @csrf
                    <div class="space-y-5">
                        {{-- Card Name --}}
                        <div>
                            <label class="block text-gray-700 font-black mb-2 text-sm text-right">اسم البطاقة</label>
                            <input type="text" name="card_name" id="card_name"
                                   class="pay-input" placeholder="أدخل اسم البطاقة"
                                   style="direction:rtl; text-align:right;"
                                   required>
                        </div>

                        {{-- Card Number --}}
                        <div>
                            <label class="block text-gray-700 font-black mb-2 text-sm text-right">رقم البطاقة</label>
                            <div class="relative">
                                <input type="text" name="card_number" id="card_number"
                                       class="pay-input !pl-12"
                                       placeholder="0000  0000  0000  0000"
                                       maxlength="19"
                                       oninput="formatCardNumber(this)"
                                       required>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-regular fa-credit-card text-gray-300 text-lg" id="cardIcon"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Expiry + CVV --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-black mb-2 text-sm text-right">تاريخ الانتهاء</label>
                                <input type="text" name="expiry" id="expiry"
                                       class="pay-input"
                                       placeholder="شهر / سنة"
                                       maxlength="5"
                                       oninput="formatExpiry(this)"
                                       required>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-black mb-2 text-sm text-right">CVV</label>
                                <input type="text" name="cvv" id="cvv"
                                       class="pay-input"
                                       placeholder="CVV"
                                       maxlength="4"
                                       oninput="this.value=this.value.replace(/\D/g,'')"
                                       required>
                            </div>
                        </div>

                        {{-- Security Badge --}}
                        <div class="secure-badge">
                            <i class="fa-solid fa-shield-halved text-green-600 text-base"></i>
                            <span>بياناتك مشفرة وآمنة بالكامل</span>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="pay-btn" id="submitBtn" onclick="handleSubmit(this)">
                            <i class="fa-solid fa-lock"></i>
                            إتمام الدفع
                        </button>
                    </div>
                </form>

                {{-- Skip link --}}
                <a href="{{ route('ads.show', $ad->id) }}" class="skip-link">
                    <i class="fa-solid fa-forward text-xs ml-1"></i>
                    تخطي وعرض الإعلان بدون تمييز
                </a>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="space-y-5">
                {{-- Ad Card --}}
                <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 flex gap-4 items-center">
                    @if($ad->image)
                        <img src="{{ asset($ad->image) }}" alt="{{ $ad->title }}"
                             class="w-20 h-20 rounded-2xl object-cover flex-shrink-0 border border-gray-100">
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-image text-3xl text-blue-200"></i>
                        </div>
                    @endif
                    <div class="flex-1 text-right">
                        <p class="text-xs font-bold text-gray-400 mb-1">إعلانك الجديد</p>
                        <h3 class="font-black text-gray-800 text-lg leading-tight">{{ $ad->title }}</h3>
                        <span class="inline-flex items-center gap-1 mt-2 bg-amber-50 text-amber-600 text-xs font-black px-3 py-1 rounded-full border border-amber-200">
                            <i class="fa-solid fa-star text-[10px]"></i> مميز
                        </span>
                    </div>
                </div>

                {{-- Price Breakdown --}}
                <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-black text-gray-900 text-lg mb-4 text-right">ملخص الطلب</h3>

                    <div class="summary-row">
                        <span class="val">{{ number_format($promotionPrice, 0) }} ر.س</span>
                        <span>سعر تمييز الإعلان</span>
                    </div>
                    @if($vatRate > 0)
                    <div class="summary-row">
                        <span class="val">{{ number_format($vatAmount, 2) }} ر.س</span>
                        <span>ضريبة القيمة المضافة ({{ $vatRate }}%)</span>
                    </div>
                    @endif
                    <div class="summary-row total mt-2 pt-2 border-t-2 border-gray-100">
                        <span class="val">{{ number_format($total, 2) }} ر.س</span>
                        <span>الإجمالي</span>
                    </div>
                </div>

                {{-- What you get --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-[24px] p-6 space-y-3">
                    <h4 class="font-black text-amber-800 text-base mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-crown text-amber-500"></i>
                        مميزات الإعلان المميز
                    </h4>
                    @foreach([
                        ['fa-arrow-up', 'يظهر في أعلى نتائج البحث'],
                        ['fa-eye', 'مشاهدات أكثر بنسبة 5x'],
                        ['fa-star', 'شارة "مميز" بارزة على الإعلان'],
                        ['fa-calendar-check', 'مدة التمييز 3 أيام كاملة'],
                    ] as $item)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid {{ $item[0] }} text-amber-600 text-sm"></i>
                        </div>
                        <span class="font-bold text-amber-900 text-sm">{{ $item[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="custom-login-footer mt-8">
        <div class="container flex flex-col md:flex-row justify-between items-center text-white/80 font-bold text-sm">
            <div class="flex gap-8 mb-4 md:mb-0">
                <a href="#" class="hover:text-white transition-colors">الشروط و الاحكام</a>
                <a href="#" class="hover:text-white transition-colors">سياسة الخصوصية</a>
            </div>
            <div dir="ltr">{{ $settings['copyright'] ?? '©2025 copyright' }}</div>
        </div>
    </footer>
</div>

<script>
    // Payment method tabs
    function selectTab(method) {
        ['mada','visa','mastercard'].forEach(m => {
            document.getElementById('tab' + m.charAt(0).toUpperCase() + m.slice(1))?.classList.remove('active');
        });
        document.getElementById('tab' + method.charAt(0).toUpperCase() + method.slice(1)).classList.add('active');
        document.getElementById('paymentMethod').value = method;
    }

    // Format card number with spaces: 0000 0000 0000 0000
    function formatCardNumber(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 16);
        input.value = v.replace(/(.{4})/g, '$1 ').trim();
    }

    // Format expiry MM/YY
    function formatExpiry(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 2) {
            v = v.substring(0, 2) + '/' + v.substring(2);
        }
        input.value = v;
    }

    // Submit with loading state
    function handleSubmit(btn) {
        setTimeout(() => {
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> جاري معالجة الدفع...';
        }, 10);
    }
</script>
@endsection
