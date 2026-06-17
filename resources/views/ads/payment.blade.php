@extends('layouts.main')

@section('title', 'تفاصيل الدفع | مزاد')

@section('content')
<style>
    .navbar, .categories-nav, .footer { display: none !important; }
    body { background-color: #F8FAFC; }

    .pay-container {
        width: 100%;
        max-width: 500px;
        background: white;
        border-radius: 32px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        padding: 2.5rem;
        border: 1px solid #F1F5F9;
    }

    .pay-input {
        width: 100%;
        padding: 1.25rem;
        border-radius: 16px;
        border: 2px solid #F1F5F9;
        background-color: #fff;
        font-weight: 600;
        transition: all 0.2s;
    }
    .pay-input:focus {
        border-color: #1A73E8;
        outline: none;
        box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
    }

    .pay-tab {
        flex: 1;
        padding: 1rem;
        border: 2px solid #F1F5F9;
        border-radius: 20px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pay-tab.active {
        border-color: #1A73E8;
        background: #F8FAFF;
    }
    .pay-tab img { height: 24px; object-fit: contain; }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        font-weight: 700;
        color: #64748B;
    }
    .summary-row .val { color: #1E293B; }
    .summary-row.total {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid #F8FAFC;
        font-size: 1.25rem;
        font-weight: 900;
    }
    .summary-row.total .val { color: #1A73E8; }

    .pay-btn {
        width: 100%;
        padding: 1.25rem;
        background-color: #1A73E8;
        color: white;
        border: none;
        border-radius: 16px;
        font-size: 1.1rem;
        font-weight: 900;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 2rem;
    }
    .pay-btn:hover { background-color: #1557B0; transform: translateY(-2px); }

    .custom-login-footer {
        background-color: #1557B0;
        background-image: linear-gradient(135deg, #1557B0 0%, #0a3066 100%);
        padding: 2rem 0;
        margin-top: auto;
    }
</style>

<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white py-6">
        <div class="container flex justify-between items-center">
            <a href="{{ route('ads.show', $ad->id) }}" class="px-6 py-2 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                خروج
            </a>
            <div class="logo">
                <a href="/">
                    @include('partials.logo', ['size' => 'text-4xl'])
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center py-12 px-4">
        <div class="flex items-center gap-3 mb-10">
            <h1 class="text-4xl font-black text-gray-900">تفاصيل الدفع</h1>
            <i class="fa-solid fa-arrow-left text-2xl text-gray-400"></i>
        </div>

        <div class="pay-container">
            <form action="{{ route('ads.payment.process', $ad->id) }}" method="POST" id="payForm">
                @csrf
                <!-- Payment Method Tabs -->
                <div class="flex gap-4 mb-8">
                    <div class="pay-tab active" onclick="selectTab(this, 'mada')">
                        <img src="{{ asset('images/images 2.svg') }}" alt="Mada">
                    </div>
                    <div class="pay-tab" onclick="selectTab(this, 'visa')">
                        <img src="{{ asset('images/image 10.svg') }}" alt="Visa">
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-black mb-2 text-right">اسم البطاقة</label>
                        <input type="text" name="card_name" placeholder="أدخل اسم البطاقة" class="pay-input text-right" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-black mb-2 text-right">رقم البطاقة</label>
                        <input type="text" name="card_number" placeholder="أدخل رقم البطاقة" class="pay-input text-right" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-black mb-2 text-right">CVV</label>
                            <input type="text" name="cvv" placeholder="CVV" class="pay-input text-center" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-black mb-2 text-right">تاريخ الانتهاء</label>
                            <input type="text" name="expiry" placeholder="تاريخ الانتهاء (شهر/سنة)" class="pay-input text-center" required>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="pt-6 mt-6 border-t border-gray-50">
                        <div class="summary-row">
                            <span class="val flex items-center gap-1 font-black">50 <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4"></span>
                            <span>السعر</span>
                        </div>
                        <div class="summary-row">
                            <span class="val flex items-center gap-1 font-black">10 <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4"></span>
                            <span>سعر تميز الاعلان</span>
                        </div>
                        <div class="summary-row">
                            <span class="val flex items-center gap-1 font-black">10 <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4"></span>
                            <span>الرسوم</span>
                        </div>
                        <div class="summary-row total">
                            <span class="val flex items-center gap-1 font-black">70 <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4"></span>
                            <span>الإجمالي</span>
                        </div>
                    </div>

                    <button type="submit" class="pay-btn">إتمام الدفع</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="custom-login-footer">
        <div class="container flex flex-col md:flex-row justify-between items-center text-white/80 font-bold text-sm">
            <div class="flex gap-8 mb-4 md:mb-0">
                <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">سياسة الخصوصية</a>
                <a href="{{ route('terms') }}" class="hover:text-white transition-colors">الشروط و الاحكام</a>
            </div>
            <div dir="ltr">
                {{ $settings['copyright'] ?? '©2025 copyright' }}
            </div>
        </div>
    </footer>
</div>

<script>
    function selectTab(el, method) {
        document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }
</script>
@endsection
