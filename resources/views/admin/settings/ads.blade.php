@extends('layouts.admin')

@section('title', 'إعدادات الإعلانات')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div>
        <h2 class="text-[32px] font-black text-[#1C1C1C]">إعدادات الإعلانات</h2>
    </div>

    <form action="{{ route('admin.settings.ads.update') }}" method="POST" class="space-y-8">
        @csrf
        
        <div class="bg-white rounded-[2.5rem] p-10 shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 space-y-10">

            <!-- Max Ad Duration -->
            <div class="space-y-3 text-right mt-4">
                <label class="text-sm font-black text-slate-900 pr-2">الحد الأقصى لمدة عرض الإعلان</label>
                <div class="relative mt-2">
                    <input type="number" name="max_ad_duration" value="{{ $settings['max_ad_duration'] ?? '' }}" placeholder="أدخل الحد الأقصى لمدة عرض الإعلان"
                           class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl pr-4 pl-20 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] transition-all text-right">
                    <div class="absolute inset-y-0 left-0 px-6 flex items-center border-l border-slate-200 bg-slate-100/50 rounded-l-2xl text-slate-400 font-black text-sm">
                        أيام
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 text-[15px]">
                <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-slate-100 text-[#6F6F6F] rounded-2xl font-black hover:bg-slate-200 transition-all text-center">
                    إلغاء
                </a>
                <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                    حفظ
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
