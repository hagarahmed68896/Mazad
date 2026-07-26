@extends('layouts.main')

@section('title', 'الدعم والمساعدة | تمت')

@push('styles')
<style>
    .contact-card {
        border-radius: 32px;
        transition: all 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10" dir="rtl">
    <div class="container mx-auto px-4 lg:px-24">

        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-center gap-2 text-sm font-bold text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span class="text-white bg-primary px-4 py-1.5 rounded-full text-xs">الدعم والمساعدة</span>
        </div>

        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">الدعم والمساعدة</h1>
            <p class="text-gray-500 font-bold text-base">هل لديك استفسار أو تواجه مشكلة؟ فريقنا جاهز لخدمتك.</p>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="max-w-5xl mx-auto mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-right font-bold text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8 max-w-5xl mx-auto">
            
            <!-- Left Side: Form ("كيف نقدر نساعدك؟") -->
            <div class="w-full lg:w-3/5 order-2 lg:order-1">
                <div class="bg-white contact-card p-8 shadow-sm border border-gray-100 text-right">
                    <h2 class="text-xl font-black text-gray-900 mb-2">كيف نقدر نساعدك؟</h2>
                    <p class="text-gray-400 font-bold text-sm mb-6">فريقنا جاهز لمساعدتك في كل وقت</p>

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">رقم الجوال</label>
                            <div class="relative flex items-center">
                                <div class="absolute right-0 top-0 bottom-0 px-4 bg-gray-50 border-l border-gray-200 flex items-center justify-center rounded-r-2xl font-bold text-gray-500" dir="ltr">
                                    +966
                                </div>
                                <input type="text" name="phone" id="phone-input" value="{{ old('phone', Auth::check() ? Auth::user()->phone : '') }}" placeholder="5XXXXXXXX" class="w-full bg-white border border-gray-200 rounded-2xl py-3.5 pr-20 pl-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm" dir="ltr" maxlength="9" inputmode="numeric" pattern="5[0-9]{8}" oninput="let val = this.value.replace(/[^0-9]/g, ''); if(val.startsWith('0')) { val = val.substring(1); } this.value = val.slice(0,9);" required>
                            </div>
                            @error('phone') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">وصف المشكلة</label>
                            <textarea name="description" rows="5" placeholder="اكتب تفاصيل المشكلة هنا..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm resize-none" required>{{ old('description') }}</textarea>
                            @error('description') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-black py-4 rounded-2xl transition-all text-sm shadow-md shadow-blue-100">
                            إرسال الطلب
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side: Contact info ("وسائل التواصل") -->
            <div class="w-full lg:w-2/5 order-1 lg:order-2">
                <div class="bg-[#EBF3FC] contact-card p-8 text-right flex flex-col justify-between h-full">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 mb-6">وسائل التواصل</h2>
                        
                        <div class="space-y-6">
                            <!-- Customer Service -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-primary shadow-sm">
                                    <i class="fa-solid fa-phone" style="transform: scaleX(-1);"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold text-xs">خدمة العملاء</p>
                                    <a href="tel:{{ $settings['phone'] ?? '+966 50 123 4567' }}" class="text-gray-900 font-black text-sm hover:text-primary transition-colors" dir="ltr">
                                        {{ $settings['phone'] ?? '+966 50 123 4567' }}
                                    </a>
                                </div>
                            </div>

                            <!-- Email Support -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-primary shadow-sm">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold text-xs">البريد الإلكتروني</p>
                                    <a href="mailto:{{ $settings['email'] ?? 'support@yourapp.sa' }}" class="text-gray-900 font-black text-sm hover:text-primary transition-colors">
                                        {{ $settings['email'] ?? 'support@yourapp.sa' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-blue-200/50">
                        <h4 class="text-sm font-black text-gray-900 mb-4">تابعنا على مواقع التواصل</h4>
                        <div class="flex gap-3 justify-start">
                            <a href="#" class="w-10 h-10 bg-white hover:bg-primary hover:text-white transition-all text-gray-600 rounded-full flex items-center justify-center shadow-sm">
                                <i class="fa-brands fa-linkedin-in text-sm"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-white hover:bg-primary hover:text-white transition-all text-gray-600 rounded-full flex items-center justify-center shadow-sm">
                                <i class="fa-brands fa-twitter text-sm"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-white hover:bg-primary hover:text-white transition-all text-gray-600 rounded-full flex items-center justify-center shadow-sm">
                                <i class="fa-brands fa-instagram text-sm"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-white hover:bg-primary hover:text-white transition-all text-gray-600 rounded-full flex items-center justify-center shadow-sm">
                                <i class="fa-brands fa-facebook-f text-sm"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
