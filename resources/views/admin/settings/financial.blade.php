@extends('layouts.admin')

@section('title', 'إعدادات النظام المالي')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div>
        <h2 class="text-[32px] font-black text-[#1C1C1C]">إعدادات النظام المالي</h2>
    </div>

    <form action="{{ route('admin.settings.financial.update') }}" method="POST" class="space-y-8">
        @csrf
        
        <div class="bg-white rounded-[2.5rem] p-10 shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 space-y-8">
            
            <!-- Commission Rate -->
            <div class="space-y-3 text-right">
                <label class="text-sm font-black text-slate-900 pr-2">نسبة السعي</label>
                <div class="relative mt-2">
                    <input type="number" step="0.01" name="commission_rate" value="{{ $settings['commission_rate'] ?? '' }}" placeholder="أدخل نسبة السعي"
                           class="w-full h-14 bg-white border border-slate-200 rounded-2xl pr-4 pl-16 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-right">
                    <div class="absolute inset-y-0 left-0 px-6 flex items-center border-l border-slate-200 bg-slate-50 rounded-l-2xl text-slate-400 font-black text-sm">
                        %
                    </div>
                </div>
            </div>

            <!-- VAT Rate -->
            <div class="space-y-3 text-right">
                <label class="text-sm font-black text-slate-900 pr-2">ضريبة قيمة مضافة</label>
                <input type="number" step="0.01" name="vat_rate" value="{{ $settings['vat_rate'] ?? '' }}" placeholder="أدخل ضريبة قيمة مضافة"
                       class="w-full mt-2 h-14 bg-white border border-slate-200 rounded-2xl px-4 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-right">
            </div>

            <!-- Admin Fees -->
            <div class="space-y-3 text-right">
                <label class="text-sm font-black text-slate-900 pr-2">مصاريف ادارية</label>
                <div class="relative mt-2">
                    <input type="number" step="0.01" name="admin_fees" value="{{ $settings['admin_fees'] ?? '' }}" placeholder="أدخل مصاريف ادارية"
                           class="w-full h-14 bg-white border border-slate-200 rounded-2xl pr-4 pl-16 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-right">
                    <div class="absolute inset-y-0 left-0 px-4 flex items-center justify-center border-l border-slate-200 bg-slate-50 rounded-l-2xl text-slate-400 font-black text-lg w-16">
                        <img src="{{ asset('images/Vector.svg') }}" class="w-6 h-6 opacity-60" alt="ر.س">
                    </div>
                </div>
            </div>

            <!-- Transfer Fees -->
            <div class="space-y-3 text-right">
                <label class="text-sm font-black text-slate-900 pr-2">مصاريف نقل ملكية</label>
                <div class="relative mt-2">
                    <input type="number" step="0.01" name="transfer_fees" value="{{ $settings['transfer_fees'] ?? '' }}" placeholder="أدخل مصاريف نقل ملكية"
                           class="w-full h-14 bg-white border border-slate-200 rounded-2xl pr-4 pl-16 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-right">
                    <div class="absolute inset-y-0 left-0 px-4 flex items-center justify-center border-l border-slate-200 bg-slate-50 rounded-l-2xl text-slate-400 font-black text-lg w-16">
                        <img src="{{ asset('images/Vector.svg') }}" class="w-6 h-6 opacity-60" alt="ر.س">
                    </div>
                </div>
            </div>

            <!-- Promotion Price -->
            <div class="space-y-3 text-right">
                <label class="text-sm font-black text-slate-900 pr-2">سعر تميز الإعلان</label>
                <div class="relative mt-2">
                    <input type="number" step="0.01" name="promotion_price" value="{{ $settings['promotion_price'] ?? '' }}" placeholder="أدخل سعر تميز الإعلان"
                           class="w-full h-14 bg-white border border-slate-200 rounded-2xl pr-4 pl-16 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-right">
                    <div class="absolute inset-y-0 left-0 px-4 flex items-center justify-center border-l border-slate-200 bg-slate-50 rounded-l-2xl text-slate-400 font-black text-lg w-16">
                        <img src="{{ asset('images/Vector.svg') }}" class="w-6 h-6 opacity-60" alt="ر.س">
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6">
                <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-slate-100 text-[#6F6F6F] rounded-2xl font-black text-lg hover:bg-slate-200 transition-all text-center">
                    إلغاء
                </a>
                <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-lg shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                    حفظ
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
