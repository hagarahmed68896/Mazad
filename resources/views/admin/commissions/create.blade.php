@extends('layouts.admin')

@section('title', 'إضافة عمولة - إدارة العمولات')

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">

    {{-- Page Header --}}
    <div class="flex items-center">
        <h1 class="text-2xl font-black text-[#1C1C1C] flex items-center gap-3">
          <a href="{{ route('admin.payments.commissions.index') }}" class="text-[#1A73E8] hover:text-[#1558B0] transition-colors flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>   
        إضافة عمولة
        </h1>
    </div>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3 rounded-xl font-bold text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 p-8">

        <h2 class="text-lg font-black text-[#1C1C1C] mb-7 pb-4 border-b border-slate-100 text-right">
            بيانات العمولة
        </h2>

        <form method="POST" action="{{ route('admin.payments.commissions.store') }}" x-data="{ valueType: 'value' }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" dir="rtl">

                {{-- Commission Type --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-black text-[#1C1C1C]">نوع العمولة</label>
                    <div class="relative">
                        <select name="type" required.
                        +++++++++
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#1C1C1C] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all cursor-pointer">
                            <option value="" disabled selected>حدد نوع العمولة</option>
                            <option value="auctions" {{ old('type') == 'auctions' ? 'selected' : '' }}>مزادات</option>
                            <option value="direct_purchase" {{ old('type') == 'direct_purchase' ? 'selected' : '' }}>شراء فوري</option>
                            <option value="tenders" {{ old('type') == 'tenders' ? 'selected' : '' }}>مناقصات</option>
                            <option value="services" {{ old('type') == 'services' ? 'selected' : '' }}>خدمات</option>
                        </select>
                    </div>
                    @error('type') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-black text-[#1C1C1C]">الفئة</label>
                    <div class="relative">
                        <select name="category_id"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#1C1C1C] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all cursor-pointer">
                            <option value="">حدد الفئة (اختياري)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('category_id') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Commission Value --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-black text-[#1C1C1C]">قيمة العمولة</label>
                    <div class="flex items-stretch gap-2">
                        {{-- Type Toggle --}}
                        <div class="relative">
                            <select name="is_percentage" x-model="valueType"
                                    class="h-full border border-slate-200 rounded-xl px-3 py-3 text-sm font-bold text-[#1C1C1C] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all cursor-pointer">
                                <option value="0">قيمة</option>
                                <option value="1">نسبة (%)</option>
                            </select>
                        </div>
                        {{-- Value Input --}}
                        <input type="number" name="value" value="{{ old('value') }}" min="0" step="0.01" required
                               :placeholder="valueType == '1' ? 'أدخل النسبة (%)' : 'أدخل قيمة العمولة'"
                               class="flex-1 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#1C1C1C] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all placeholder:font-normal placeholder:text-slate-400">
                    </div>
                    @error('value') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-black text-[#1C1C1C]">الحالة</label>
                    <div class="relative">
                        <select name="status" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#1C1C1C] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all cursor-pointer">
                            <option value="" disabled selected>اختر الحالة</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                    @error('status') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-start gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="submit"
                        class="px-8 py-3 bg-[#1A73E8] hover:bg-[#1558B0] text-white text-sm font-black rounded-xl shadow-[0_4px_15px_rgba(26,115,232,0.3)] hover:shadow-[0_4px_20px_rgba(26,115,232,0.4)] transition-all duration-200">
                    إضافة العمولة
                </button>
                <a href="{{ route('admin.payments.commissions.index') }}"
                   class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-[#6F6F6F] text-sm font-black rounded-xl transition-all duration-200">
                    إلغاء
                </a>
            </div>

        </form>
    </div>

</div>
@endsection
