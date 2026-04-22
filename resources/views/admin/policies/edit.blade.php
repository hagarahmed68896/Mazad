@extends('layouts.admin')

@section('title', 'تعديل ' . ($policy->type === 'terms' ? 'شروط وأحكام' : 'سياسة خصوصية'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.policies.index', ['type' => $policy->type]) }}" class="text-slate-400 hover:text-[#1A73E8] transition-colors">
            <svg class="w-8 h-8 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-3xl font-black text-[#1C1C1C]">تعديل بيانات {{ $policy->type === 'terms' ? 'الشروط و احكام' : 'السياسة' }}</h2>
    </div>

    <!-- Form Container -->
    <div class="bg-[#F8FAFC] rounded-[2rem] p-8 md:p-12 shadow-sm border border-slate-100">
        
        <div class="mb-8 border-b border-slate-200/60 pb-6">
            <h3 class="text-xl font-black text-slate-800">بيانات {{ $policy->type === 'terms' ? 'الشروط و احكام' : 'السياسة' }}</h3>
        </div>

        <form action="{{ route('admin.policies.update', $policy->id) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Title Field -->
                <div class="flex flex-col gap-3">
                    <label class="text-[15px] font-bold text-slate-700">العنوان</label>
                    <input type="text" name="title" value="{{ old('title', $policy->title) }}" placeholder="أدخل عنوان" required
                           class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-5 text-sm font-semibold outline-none focus:border-[#1A73E8] focus:ring-1 focus:ring-[#1A73E8] transition-all">
                    @error('title') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                <!-- Target Audience Field -->
                @php 
                    $selectedTargets = is_array(json_decode($policy->target_audience, true)) 
                                        ? json_decode($policy->target_audience, true) 
                                        : [$policy->target_audience];
                    
                    if(in_array('all', $selectedTargets)) {
                        $selectedTargets = ['individuals', 'companies', 'delegates', 'partners'];
                    }
                @endphp
                <div class="flex flex-col gap-3" x-data="{ 
                    open: false, 
                    selected: {{ json_encode($selectedTargets) }},
                    options: {
                        'individuals': 'الأفراد',
                        'companies': 'الشركات',
                        'delegates': 'للمندوبين',
                        'partners': 'الشركاء'
                    },
                    get displayValue() {
                        if (this.selected.length === 0) return 'حدد الفئة المستهدفة';
                        if (this.selected.length === 4) return 'الكل';
                        return this.selected.map(k => this.options[k]).join(', ');
                    }
                }">
                    <label class="text-[15px] font-bold text-slate-700">الفئة المستهدفة</label>
                    <div class="relative relative-target-audience">
                        <div @click="open = !open" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-5 flex items-center justify-between cursor-pointer hover:border-slate-300 transition-colors">
                            <span class="text-sm font-semibold text-slate-500 truncate" x-text="displayValue"></span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{'rotate-180': open}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        
                        <div x-show="open" @click.away="open = false" x-cloak x-transition
                             class="absolute top-full right-0 left-0 mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 p-4 py-2">
                             
                            <template x-for="(label, key) in options" :key="key">
                                <label class="flex items-center justify-between py-3 cursor-pointer group">
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-[#1A73E8]" x-text="label"></span>
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" name="target_audience[]" :value="key" x-model="selected" class="hidden peer">
                                        <div class="w-5 h-5 border-[1.5px] border-slate-300 rounded flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all">
                                            <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Field -->
            <div class="flex flex-col gap-3">
                <label class="text-[15px] font-bold text-slate-700">المحتوي</label>
                <textarea name="content" rows="6" placeholder="أدخل محتوى" required
                          class="w-full bg-white border border-slate-200 rounded-2xl p-5 text-sm font-semibold outline-none focus:border-[#1A73E8] focus:ring-1 focus:ring-[#1A73E8] transition-all resize-none">{{ old('content', $policy->content) }}</textarea>
                @error('content') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
            </div>

            <!-- Status Field -->
            <div class="flex flex-col gap-3" x-data="{ open: false, value: {{ $policy->is_active ? 1 : 0 }} }">
                <label class="text-[15px] font-bold text-slate-700">الحالة</label>
                <div class="relative">
                    <div @click="open = !open" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-5 flex items-center justify-between cursor-pointer hover:border-slate-300 transition-colors">
                        <span class="text-sm font-semibold text-slate-700" x-text="value == 1 ? 'نشط' : 'غير نشط'"></span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{'rotate-180': open}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                         class="absolute top-full right-0 left-0 mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 p-4 py-2">
                        
                        <label class="flex items-center justify-between py-3 cursor-pointer group">
                            <span class="text-sm font-bold text-slate-700 group-hover:text-[#1A73E8]">نشط</span>
                            <div class="relative flex items-center justify-center">
                                <input type="radio" name="is_active" value="1" x-model="value" class="hidden peer">
                                <div class="w-5 h-5 border-[1.5px] border-slate-300 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all">
                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                </div>
                            </div>
                        </label>

                        <div class="w-full h-px bg-slate-100"></div>

                        <label class="flex items-center justify-between py-3 cursor-pointer group">
                            <span class="text-sm font-bold text-slate-700 group-hover:text-[#1A73E8]">غير نشط</span>
                            <div class="relative flex items-center justify-center">
                                <input type="radio" name="is_active" value="0" x-model="value" class="hidden peer">
                                <div class="w-5 h-5 border-[1.5px] border-slate-300 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all">
                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 flex gap-4 w-fit mr-auto">
                <a href="{{ route('admin.policies.index', ['type' => $policy->type]) }}" 
                   class="h-12 w-24 bg-[#E5E7EB] text-[#4B5563] rounded-xl font-bold flex items-center justify-center hover:bg-slate-300 transition-all outline-none">
                    إلغاء
                </a>
                <button type="submit" class="h-12 w-24 bg-[#1A73E8] text-white rounded-xl font-bold flex items-center justify-center hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 active:scale-95 outline-none">
                    حفظ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
