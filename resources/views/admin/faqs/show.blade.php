@extends('layouts.admin')

@section('title', 'عرض السؤال الشائع')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.faqs.index') }}" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-[#1A73E8] hover:border-[#1A73E8] transition-all active:scale-95 shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-3xl font-black text-[#1C1C1C]">الأسئلة الشائعة</h2>
    </div>

    <!-- Content Container -->
    <div class="bg-[#F8FAFC] rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-100 flex flex-col gap-6 w-full">
        <div class="flex items-center justify-between">
            <h3 class="text-2xl font-black text-slate-800">{{ $faq->question }}</h3>
            @if($faq->status)
                <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-sm font-bold ring-1 ring-emerald-100">نشط</span>
            @else
                <span class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-full text-sm font-bold ring-1 ring-slate-200">غير نشط</span>
            @endif
        </div>
        
        <div class="text-[15px] font-bold text-slate-500 leading-loose text-justify w-full">
            {!! nl2br(e($faq->answer)) !!}
        </div>

        <div class="pt-6 border-t border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 text-sm font-bold">الفئة المستهدفة:</span>
                @php
                    $targets = is_array(json_decode($faq->target_group, true)) 
                                ? json_decode($faq->target_group, true) 
                                : [$faq->target_group];
                    
                    $labelsMap = ['all' => 'الكل', 'individuals' => 'الأفراد', 'companies' => 'الشركات', 'delegates' => 'مندوبين', 'partners' => 'الشركاء'];
                @endphp
                <div class="flex gap-2">
                    @foreach($targets as $t)
                        <span class="px-3 py-1 bg-white text-slate-600 rounded-lg text-xs font-black border border-slate-100 shadow-sm">{{ $labelsMap[$t] ?? $t }}</span>
                    @endforeach
                </div>
            </div>
            <div class="text-slate-400 text-sm font-bold">تاريخ الإضافة: {{ $faq->created_at->format('Y/m/d') }}</div>
        </div>
    </div>
</div>
@endsection
