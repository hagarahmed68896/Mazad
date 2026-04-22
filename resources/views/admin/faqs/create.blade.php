@extends('layouts.admin')

@section('title', 'إضافة سؤال شائع')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.faqs.index') }}" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-[#1A73E8] hover:border-[#1A73E8] transition-all active:scale-95 shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-black text-[#1C1C1C]">إضافة سؤال جديد</h2>
            <p class="text-slate-500 font-bold text-sm">قم بتعبئة البيانات أدناه لإضافة سؤال جديد للأسئلة الشائعة</p>
        </div>
    </div>

    <!-- Form Core -->
    <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-6 pb-10">
        @csrf
        
        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Question -->
                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-700 flex items-center gap-2">
                        السؤال
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="question" required value="{{ old('question') }}"
                           placeholder="مثال: كيف يمكنني استرداد أموالي؟"
                           class="w-full h-14 px-6 bg-[#F8FAFC] border-none rounded-2xl font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    @error('question') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Target Group -->
                <div class="space-y-3" x-data="{ 
                    open: false, 
                    selected: [],
                    options: [
                        { id: 'individuals', label: 'الأفراد' },
                        { id: 'companies', label: 'الشركات' },
                        { id: 'delegates', label: 'للمندوبين' },
                        { id: 'partners', label: 'الشركاء' }
                    ],
                    get displayText() {
                        if (this.selected.length === 0) return 'اختر الفئة المستهدفة';
                        if (this.selected.length === 4) return 'الكل';
                        return this.options.filter(o => this.selected.includes(o.id)).map(o => o.label).join('، ');
                    }
                }">
                    <label class="text-sm font-black text-slate-700">الفئة المستهدفة</label>
                    <div class="relative">
                        <button type="button" @click="open = !open" 
                                class="w-full h-14 px-6 bg-[#F8FAFC] rounded-2xl flex items-center justify-between text-slate-600 font-bold hover:bg-slate-50 transition-all border-none">
                            <span x-text="displayText" class="truncate max-w-[250px]"></span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-4 space-y-1">
                            <template x-for="option in options" :key="option.id">
                                <label class="flex items-center p-3 hover:bg-slate-50 rounded-xl cursor-pointer group transition-all">
                                    <input type="checkbox" name="target_group[]" :value="option.id" x-model="selected" class="hidden peer">
                                    <div class="w-6 h-6 border-2 border-slate-200 rounded-lg flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                        <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="mr-3 text-[15px] font-bold text-slate-600 group-hover:text-[#1A73E8] transition-colors" x-text="option.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    @error('target_group') <p class="text-rose-500 text-xs font-bold mt-1 text-right">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Answer -->
            <div class="space-y-3">
                <label class="text-sm font-black text-slate-700 flex items-center gap-2">
                    الإجابة
                    <span class="text-rose-500">*</span>
                </label>
                <textarea name="answer" required rows="8"
                          placeholder="اكتب الإجابة التفصيلية هنا..."
                          class="w-full p-6 bg-[#F8FAFC] border-none rounded-[2rem] font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 transition-all resize-none leading-relaxed leading-loose text-justify">{{ old('answer') }}</textarea>
                @error('answer') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Status Dropdown -->
            <div class="space-y-3" x-data="{ 
                open: false, 
                status: '1',
                options: [
                    { value: '1', label: 'نشط', color: 'text-emerald-500', bg: 'bg-emerald-50' },
                    { value: '0', label: 'غير نشط', color: 'text-slate-400', bg: 'bg-slate-50' }
                ],
                get current() { return this.options.find(o => o.value == this.status) }
            }">
                <label class="text-sm font-black text-slate-700">الحالة</label>
                <div class="relative">
                    <input type="hidden" name="status" :value="status">
                    <button type="button" @click="open = !open" 
                            class="w-full h-14 px-6 bg-[#F8FAFC] rounded-2xl flex items-center justify-between text-slate-600 font-bold hover:bg-slate-50 transition-all border-none">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full" :class="current.color.replace('text-', 'bg-')"></div>
                            <span x-text="current.label"></span>
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 space-y-1">
                        <template x-for="option in options" :key="option.value">
                            <button type="button" @click="status = option.value; open = false" 
                                    class="w-full flex items-center justify-between p-4 hover:bg-slate-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full scale-125 transition-transform group-hover:scale-150" :class="option.color.replace('text-', 'bg-')"></div>
                                    <span class="font-black text-sm" :class="status == option.value ? 'text-[#1A73E8]' : 'text-slate-500'" x-text="option.label"></span>
                                </div>
                                <div x-show="status == option.value" class="w-5 h-5 rounded-full bg-blue-50 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-[#1A73E8]"></div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 flex justify-end">
                <button type="submit" class="h-14 px-8 bg-[#1A73E8] text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 active:scale-95">
                    إضافة السؤال
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
