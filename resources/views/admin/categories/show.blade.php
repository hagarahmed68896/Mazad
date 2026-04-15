@extends('layouts.admin')

@section('title', 'عرض الفئة | ' . $category->name)

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Header & Title -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.categories.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h1 class="text-2xl font-black text-[#1C1C1C]">{{ $category->name }}</h1>
    </div>

    <!-- Main Container -->
    <div class="max-w-4xl bg-[#F8F9FA] border border-slate-100 rounded-3xl p-6 shadow-sm">
        
        <div class="space-y-4">
            @forelse($pages as $index => $page)
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden" x-data="{ expanded: {{ $index === 0 ? 'true' : 'false' }} }">
                    
                    <!-- Accordion Header -->
                    <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-6 py-4 bg-[#F8F9FA] hover:bg-slate-100 transition-colors focus:outline-none">
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <span class="text-sm font-black text-[#1C1C1C]">{{ $page->name }}</span>
                    </button>

                    <!-- Accordion Body -->
                    <div x-show="expanded" x-collapse x-cloak>
                        <div class="px-6 py-6 border-t border-slate-200 space-y-6 bg-white">
                            @if($page->properties->isEmpty())
                                <div class="text-center py-6 text-slate-400 font-bold text-sm">
                                    لا توجد حقول في هذه الصفحة
                                </div>
                            @else
                                @foreach($page->properties as $prop)
                                    <div>
                                        <label class="block text-sm font-black text-[#6F6F6F] mb-3 text-right">
                                            {{ $prop->label }}
                                            @if($prop->is_required) <span class="text-red-500">*</span> @endif
                                        </label>

                                        @if(in_array($prop->input_type, ['text', 'number', 'price']))
                                            <input type="{{ $prop->input_type == 'text' ? 'text' : 'number' }}" 
                                                   placeholder="{{ $prop->placeholder }}"
                                                   class="w-full px-5 py-3.5 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all cursor-not-allowed" disabled>
                                        
                                        @elseif($prop->input_type == 'select')
                                            <div class="relative">
                                                <select class="w-full px-5 py-3.5 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-slate-400 appearance-none focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all cursor-not-allowed" disabled>
                                                    <option>{{ $prop->placeholder ?: 'حدد ' . $prop->label }}</option>
                                                    @php
                                                        $opts = is_array($prop->options) ? $prop->options : json_decode($prop->options, true) ?? [];
                                                    @endphp
                                                    @foreach($opts as $opt)
                                                        @if(!empty(trim($opt)))
                                                            <option>{{ $opt }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <svg class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </div>

                                        @elseif(in_array($prop->input_type, ['checkbox', 'radio']))
                                            @php
                                                $opts = is_array($prop->options) ? $prop->options : json_decode($prop->options, true) ?? [];
                                            @endphp
                                            <div class="flex flex-wrap gap-4">
                                                @foreach($opts as $opt)
                                                    @if(!empty(trim($opt)))
                                                        <label class="flex items-center gap-2 cursor-not-allowed">
                                                            <input type="{{ $prop->input_type }}" disabled class="w-4 h-4 text-[#1A73E8] border-slate-300 rounded focus:ring-[#1A73E8]">
                                                            <span class="text-sm font-bold text-[#1C1C1C]">{{ $opt }}</span>
                                                        </label>
                                                    @endif
                                                @endforeach
                                            </div>

                                        @elseif($prop->input_type == 'date')
                                            <input type="date" 
                                                   class="w-full px-5 py-3.5 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all cursor-not-allowed" disabled>

                                        @elseif($prop->input_type == 'file')
                                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center bg-white">
                                                <div class="w-12 h-12 bg-blue-50 text-[#1A73E8] rounded-xl flex items-center justify-center mx-auto mb-3">
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.5 12.5h-9v-2h9v2zm0-4h-9v-2h9v2z" opacity="0.3"/>
                                                        <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-black text-[#1C1C1C] mb-2">اسحب الملف هنا</p>
                                                <p class="text-xs text-slate-400 font-bold mb-4">أو</p>
                                                <button type="button" class="px-6 py-2 bg-white border border-[#1A73E8] text-[#1A73E8] rounded-xl text-sm font-black disabled:opacity-50" disabled>
                                                    تصفح الملفات
                                                </button>
                                            </div>
                                        @endif
                                        
                                        @if($prop->help_text)
                                            <p class="mt-2 text-xs font-bold text-slate-400">{{ $prop->help_text }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    
                </div>
            @empty
                <div class="py-12 text-center text-slate-500 font-bold text-lg bg-white rounded-2xl border border-slate-100 shadow-sm">
                    لا توجد صفحات أو خصائص لهذه الفئة بعد
                </div>
            @endforelse

            <!-- Services Section -->
            @if($services->isNotEmpty())
                <div class="mt-12 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-[#F8F9FA] border-b border-slate-200">
                        <h3 class="text-sm font-black text-[#1C1C1C]">الخدمات المتاحة</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($services as $service)
                            <div class="flex items-center gap-3 px-4 py-3 bg-[#F8F9FA] rounded-xl border border-slate-100">
                                <div class="w-8 h-8 bg-[#1A73E8]/10 rounded-lg flex items-center justify-center text-[#1A73E8]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-sm font-bold text-[#1C1C1C]">{{ $service->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
    </div>
</div>
@endsection
