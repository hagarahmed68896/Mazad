@extends('layouts.admin')

@section('title', 'معاينة الفئة | ' . $category->name)

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Top Navigation Header -->
    <div class="flex items-center gap-4 mb-10">
        <a href="{{ route('admin.categories.index') }}" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] hover:border-[#1A73E8] transition-all shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h1 class="text-3xl font-black text-[#1C1C1C] flex items-center gap-3">
            {{ $category->name }}
        </h1>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-5xl mx-auto space-y-4">
        
        <!-- Accordion Steps Section -->
        <div class="bg-[#F1F3F5] rounded-[2.5rem] p-8 space-y-4 shadow-inner">
            @forelse($pages as $index => $page)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" 
                     x-data="{ expanded: {{ $index === 0 ? 'true' : 'false' }} }">
                    
                    <!-- Accordion Header -->
                    <button @click="expanded = !expanded" 
                            class="w-full flex items-center justify-between px-8 py-6 bg-[#F8F9FA] hover:bg-white transition-all focus:outline-none group">
                     
                        <!-- Page Name Right -->
                        <span class="text-lg font-black text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors">{{ $page->name }}</span>
                      <!-- Arrow Left -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all" 
                             :class="expanded ? 'bg-slate-100 text-[#1A73E8]' : 'text-slate-300'">
                            <svg class="w-5 h-5 transform transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>

                    <!-- Accordion Body -->
                    <div x-show="expanded" x-collapse x-cloak>
                        <div class="px-8 py-10 space-y-12 bg-white border-t border-slate-100">
                            @if($page->properties->isEmpty())
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-slate-400 font-bold text-sm">لم يتم إضافة حقول لهذه الصفحة بعد</p>
                                </div>
                            @else
                                <div class="space-y-10">
                                    @foreach($page->properties as $prop)
                                        <div class="max-w-3xl mx-auto">
                                            <!-- Field Label -->
                                            <div class="flex items-center justify-between mb-4 px-1">
                                                <label class="text-sm font-black text-[#1C1C1C]">
                                                    {{ $prop->label }}
                                                    @if($prop->is_required) <span class="text-red-500 mr-1">*</span> @endif
                                                </label>
                                                @if($prop->help_text)
                                                    <span class="text-[10px] text-slate-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">{{ $prop->help_text }}</span>
                                                @endif
                                            </div>

                                            <!-- Field Input Mockup -->
                                            @if(in_array($prop->input_type, ['text', 'number', 'price', 'date']))
                                                <div class="w-full px-5 py-4 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-slate-400 flex items-center justify-between">
                                                    <span>{{ $prop->placeholder ?: 'أدخل ' . $prop->label }}</span>
                                                    @if($prop->input_type == 'date')
                                                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    @endif
                                                </div>
                                            
                                            @elseif($prop->input_type == 'select')
                                                <div class="w-full px-5 py-4.5 bg-[#F8F9FA] border border-slate-200 rounded-xl flex items-center justify-between">
                                                    <span class="text-sm font-bold text-slate-400">{{ $prop->placeholder ?: 'حدد ' . $prop->label }}</span>
                                                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </div>

                                            @elseif(in_array($prop->input_type, ['checkbox', 'radio']))
                                                @php $opts = is_array($prop->options) ? $prop->options : json_decode($prop->options, true) ?? []; @endphp
                                                <div class="flex flex-wrap gap-5 p-2">
                                                    @foreach($opts as $opt)
                                                        @if(!empty(trim($opt)))
                                                            <div class="flex items-center gap-3 px-5 py-3 bg-[#F8F9FA] border border-slate-100 rounded-2xl">
                                                                <div class="w-5 h-5 rounded-full border-2 border-slate-200" :class="{{ $prop->input_type == 'checkbox' ? '\'rounded-lg\'' : '' }}"></div>
                                                                <span class="text-sm font-bold text-slate-600">{{ $opt }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                            @elseif($prop->input_type == 'file')
                                                <div class="w-full border-2 border-dashed border-slate-200 rounded-[2.5rem] p-12 text-center bg-white hover:border-[#1A73E8]/50 transition-colors group">
                                                    <div class="w-20 h-20 bg-[#F8F9FA] text-[#1A73E8] rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-sm">
                                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-base font-black text-[#1C1C1C] mb-2">اسحب الملف هنا</p>
                                                    <p class="text-xs text-slate-400 font-bold mb-8">أو</p>
                                                    <div class="inline-block px-10 py-4 border-2 border-[#1A73E8] text-[#1A73E8] rounded-2xl text-sm font-black hover:bg-[#1A73E8] hover:text-white transition-all cursor-pointer shadow-sm">
                                                        تصفح الملفات
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-20 text-center bg-white rounded-[2.5rem] border border-slate-200 shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-xl font-black text-slate-500">لا توجد حقول معاينة لهذه الفئة</p>
                    <p class="text-slate-400 mt-2 font-bold">ابدأ بإضافة صفحات وخصائص من لوحة التحكم</p>
                </div>
            @endforelse
        </div>



    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
