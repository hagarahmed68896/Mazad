@extends('layouts.admin')

@section('title', $policy->type === 'terms' ? 'الشروط والأحكام' : 'سياسة الخصوصية')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.policies.index', ['type' => $policy->type]) }}" class="text-slate-400 hover:text-[#1A73E8] transition-colors">
            <svg class="w-8 h-8 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-3xl font-black text-[#1C1C1C]">{{ $policy->type === 'terms' ? 'الشروط والأحكام' : 'سياسة الخصوصية' }}</h2>
    </div>

    <!-- Content Container -->
    <div class="bg-[#F8FAFC] rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-100 flex flex-col gap-6 w-full">
        <h3 class="text-2xl font-black text-slate-800">{{ $policy->title }}</h3>
        
        <div class="text-[15px] font-bold text-slate-500 leading-loose text-justify w-full">
            {!! nl2br(e($policy->content)) !!}
        </div>
    </div>
</div>
@endsection
