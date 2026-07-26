@extends('layouts.main')

@section('title', 'إتمام الدفع للخدمة | تمت')

@section('content')
<div class="checkout-page bg-[#F8FAFC] min-h-screen py-16" dir="rtl" style="text-align: right; font-family: 'Cairo', sans-serif;">
    <div class="container mx-auto px-4 md:px-16">
        
        <!-- Header -->
        <div class="mb-8 flex items-center gap-3">
            <a href="{{ route('services.show', $service->id) }}" class="text-slate-500 hover:text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <h1 class="text-3xl font-black text-[#1C1C1C]">إتمام الدفع والطلب</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Right Column: Payment Form -->
            <div class="lg:col-span-7 bg-white rounded-[32px] border border-slate-100 p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-6 bg-primary rounded-full"></div>
                    <h3 class="text-xl font-black text-gray-900">تفاصيل الدفع الآمن</h3>
                </div>

                <!-- Payment Method Toggle -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <!-- Mada -->
                    <button type="button" onclick="selectPaymentMethod('mada')" id="pm-mada" class="flex items-center justify-between p-4 rounded-2xl border-2 border-primary bg-blue-50/50 text-primary transition-all font-bold text-sm">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-dot"></i>
                            <span>مدى (Mada)</span>
                        </span>
                        <img src="{{ asset('images/mada.svg') }}" class="h-5 object-contain" alt="Mada">
                    </button>
                    
                    <!-- Credit Card -->
                    <button type="button" onclick="selectPaymentMethod('card')" id="pm-card" class="flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-all font-bold text-sm">
                        <span class="flex items-center gap-2">
                            <i class="fa-regular fa-circle"></i>
                            <span>بطاقة ائتمانية</span>
                        </span>
                        <div class="flex gap-1.5">
                            <img src="{{ asset('images/mastercard.svg') }}" class="h-4 object-contain" alt="Mastercard">
                            <img src="{{ asset('images/visa.svg') }}" class="h-4 object-contain" alt="Visa">
                        </div>
                    </button>
                </div>

                <!-- Form -->
                <form id="paymentForm" onsubmit="submitPayment(event)">
                    @csrf
                    <input type="hidden" name="payment_method" id="payment_method_input" value="mada">

                    <!-- Name on Card -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-3">الاسم المكتوب على البطاقة</label>
                        <div class="relative">
                            <input type="text" id="card_name" required placeholder="مثال: أحمد محمد"
                                   class="w-full pr-12 pl-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all outline-none">
                            <i class="fa-regular fa-user absolute right-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>

                    <!-- Card Number -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-3">رقم البطاقة</label>
                        <div class="relative">
                            <input type="text" id="card_number" required placeholder="0000 0000 0000 0000" maxlength="19"
                                   class="w-full pr-12 pl-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all outline-none" style="direction: ltr; text-align: right;">
                            <i class="fa-regular fa-credit-card absolute right-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>

                    <!-- Expiry & CVV -->
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-3">تاريخ الانتهاء (MM/YY)</label>
                            <input type="text" id="expiry" required placeholder="MM/YY" maxlength="5"
                                   class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all outline-none text-center" style="direction: ltr;">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-3">رمز الأمان (CVV)</label>
                            <input type="password" id="cvv" required placeholder="•••" maxlength="4"
                                   class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all outline-none text-center" style="direction: ltr;">
                        </div>
                    </div>

                    <!-- Lock Notice -->
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl mb-8">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-shield-halved text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-emerald-950 mb-0.5">اتصال مشفر وآمن 100%</h4>
                            <p class="text-xs text-emerald-700 font-medium">نحن لا نقوم بحفظ تفاصيل بطاقتك البنكية، عمليات الدفع مشفرة بالكامل.</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-5 bg-primary text-white rounded-2xl font-black text-base hover:bg-blue-700 shadow-xl shadow-primary/20 hover:shadow-2xl hover:shadow-primary/30 transition-all flex items-center justify-center gap-3 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-lock"></i>
                        <span>إتمام الدفع والطلب بأمان</span>
                    </button>
                </form>
            </div>

            <!-- Left Column: Order Summary & Service Details -->
            <div class="lg:col-span-5 flex flex-col gap-6">
                <!-- Service Details -->
                <div class="bg-white rounded-[32px] border border-slate-100 p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <img src="{{ $service->image ? (Str::startsWith($service->image, 'http') ? $service->image : '/' . ltrim($service->image, '/')) : 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=200' }}" 
                             class="w-20 h-20 rounded-2xl object-cover border border-slate-100 shadow-sm shrink-0">
                        <div class="min-w-0">
                            <span class="text-xs text-primary font-black bg-primary/5 px-3 py-1 rounded-lg">{{ $service->category->name ?? 'خدمة شريك' }}</span>
                            <h4 class="text-base font-black text-gray-900 truncate mt-2">{{ $service->title }}</h4>
                            <p class="text-xs text-slate-400 font-semibold mt-1">مقدم الخدمة: {{ $service->user->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Pricing Summary -->
                <div class="bg-white rounded-[32px] border border-slate-100 p-8 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 mb-6">ملخص الفاتورة</h3>
                    
                    <div class="space-y-4 font-bold text-sm text-slate-600 mb-6">
                        <div class="flex justify-between">
                            <span>سعر الخدمة</span>
                            <span class="text-slate-900">{{ number_format($price) }} ريال</span>
                        </div>
                        <div class="flex justify-between">
                            <span>عمولة المنصة</span>
                            <span class="text-slate-900">{{ number_format($commission, 2) }} ريال</span>
                        </div>
                        <hr class="border-slate-100">
                        <div class="flex justify-between text-base font-black text-[#1C1C1C]">
                            <span>المبلغ الإجمالي</span>
                            <span class="text-primary text-xl font-black">{{ number_format($totalPrice) }} ريال</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let activePaymentMethod = 'mada';

    function selectPaymentMethod(method) {
        activePaymentMethod = method;
        document.getElementById('payment_method_input').value = method;

        const btnMada = document.getElementById('pm-mada');
        const btnCard = document.getElementById('pm-card');

        if (method === 'mada') {
            btnMada.className = "flex items-center justify-between p-4 rounded-2xl border-2 border-primary bg-blue-50/50 text-primary transition-all font-bold text-sm";
            btnMada.querySelector('i').className = "fa-solid fa-circle-dot";

            btnCard.className = "flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-all font-bold text-sm";
            btnCard.querySelector('i').className = "fa-regular fa-circle";
        } else {
            btnCard.className = "flex items-center justify-between p-4 rounded-2xl border-2 border-primary bg-blue-50/50 text-primary transition-all font-bold text-sm";
            btnCard.querySelector('i').className = "fa-solid fa-circle-dot";

            btnMada.className = "flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-all font-bold text-sm";
            btnMada.querySelector('i').className = "fa-regular fa-circle";
        }
    }

    // Auto format expiry date
    document.getElementById('expiry').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length > 2) {
            val = val.substring(0, 2) + '/' + val.substring(2, 4);
        }
        e.target.value = val;
    });

    // Auto format card number spaces
    document.getElementById('card_number').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += val[i];
        }
        e.target.value = formatted;
    });

    function submitPayment(e) {
        e.preventDefault();

        const cardName = document.getElementById('card_name').value.trim();
        const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        const expiry = document.getElementById('expiry').value.trim();
        const cvv = document.getElementById('cvv').value.trim();

        if (!cardName || cardNumber.length < 15 || expiry.length < 5 || cvv.length < 3) {
            Swal.fire({
                icon: 'error',
                title: 'بيانات غير مكتملة',
                text: 'يرجى مراجعة بيانات البطاقة البنكية المدخلة والتأكد من صحتها.',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#1A73E8',
                customClass: { popup: 'rounded-[32px]' }
            });
            return;
        }

        Swal.fire({
            title: 'جاري معالجة الدفع الآمن...',
            html: 'يرجى عدم إغلاق الصفحة أو الضغط على زر التراجع.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: { popup: 'rounded-[32px]' }
        });

        // Send POST request
        fetch("{{ route('services.checkout.process', $service->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                card_name: cardName,
                card_number: cardNumber,
                expiry: expiry,
                cvv: cvv,
                payment_method: activePaymentMethod
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'تم الدفع بنجاح!',
                    text: data.message,
                    confirmButtonText: 'الذهاب لمشترياتي',
                    confirmButtonColor: '#1A73E8',
                    customClass: {
                        popup: 'rounded-[32px]',
                        confirmButton: 'rounded-2xl px-6 py-3 font-bold text-sm bg-primary border-0'
                    }
                }).then(() => {
                    window.location.href = data.redirect_url;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'فشل الدفع',
                    text: data.message || 'حدث خطأ غير متوقع أثناء معالجة الدفع.',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#1A73E8',
                    customClass: { popup: 'rounded-[32px]' }
                });
            }
        })
        .catch(error => {
            console.error('Checkout error:', error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ اتصال بالشبكة',
                text: 'حدث خطأ في الاتصال بالخادم، يرجى المحاولة لاحقاً.',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#1A73E8',
                customClass: { popup: 'rounded-[32px]' }
            });
        });
    }
</script>
@endsection
