@extends('layouts.admin')

@section('title', 'إدارة البانرات الإعلانية')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">
    
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-[#1C1C1C] mb-2 ">البانرات الإعلانية</h1>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="flex items-center justify-between gap-4 mb-10 w-full  p-4 rounded-2xl border border-slate-100">
        <!-- Search Bar (Right visually in RTL) -->
        <form action="{{ route('admin.banners.index') }}" method="GET" class="relative group w-full max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث" 
                   class="w-full pl-10 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] focus:bg-white focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all placeholder:text-slate-400">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400 group-focus-within:text-[#1A73E8] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>

        <!-- Add Button (Left visually in RTL) -->
        <a href="{{ route('admin.banners.create') }}" class="px-6 sm:px-8 py-3.5 bg-[#1A73E8] text-white rounded-xl font-black text-sm hover:translate-y-[-2px] hover:shadow-xl hover:shadow-[#1A73E8]/20 transition-all flex items-center justify-center gap-2 shrink-0">
            <svg class="w-5 h-5 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>إضافة بانر إعلاني</span>
        </a>
    </div>

    <!-- Banners List -->
    <div class="space-y-6">
        @forelse($banners as $banner)
            <!-- Banner Card -->
            <div class="bg-white border border-slate-100 rounded-[2rem] p-6 lg:p-8 relative">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-[#1C1C1C]">{{ $banner->title ?? 'الاسم' }}</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-500 font-bold">{{ $banner->created_at ? $banner->created_at->format('Y/m/d') : '3/12/2025' }}</span>
                        @if($banner->status)
                            <span class="bg-green-100 text-green-600 px-5 py-1.5 rounded-xl text-xs font-black">نشط</span>
                        @else
                            <span class="bg-slate-200 text-slate-500 px-5 py-1.5 rounded-xl text-xs font-black">معطل</span>
                        @endif
                    </div>
                </div>

                <!-- Banners Previews (3 Copies as requested by UI design) -->
                <!-- The blue banner component -->
                <div class="flex items-center gap-4 overflow-x-auto custom-scrollbar pb-4 -mx-2 px-2">
                    @for($i = 0; $i < 3; $i++)
                        <div class="relative w-[320px] h-[160px] rounded-2xl flex-shrink-0 overflow-hidden shadow-sm flex items-center bg-gradient-to-l from-[#196BE8] to-[#2582F0]">
                            <!-- Content Right -->
                            <div class="relative z-10 px-6 py-4 w-2/3 h-full flex flex-col justify-center">
                                <h4 class="text-white font-black text-[15px] mb-2 leading-tight">{{ $banner->title ?? 'ابحث، بع، أو زايد' }}</h4>
                                <p class="text-white/90 text-[10px] font-semibold leading-snug mb-3">
                                    {{ $banner->description ?? 'منصة موثوقة تمكنك من تصفح الإعلانات أو إضافة إعلانك بسهولة.' }}
                                </p>
                                <div>
                                    <span class="bg-white text-[#1A73E8] text-[9px] font-black px-4 py-1.5 rounded-lg inline-block whitespace-nowrap shadow-sm">
                                        {{ $banner->button_text ?? 'أضف إعلانك' }}
                                    </span>
                                </div>
                            </div>
                            <!-- Image Left -->
                            <div class="absolute left-0 bottom-0 top-0 w-[45%] h-full flex items-center justify-start overflow-hidden">
                                @if($banner->image)
                                    <img src="{{ asset('storage/' . $banner->image) }}" class="h-full w-auto object-cover object-left" alt="">
                                @else
                                    <!-- Placeholder Car matching the image -->
                                    <div class="w-full h-full bg-slate-200 opacity-20"></div>
                                @endif
                                <!-- Add fading effect to blend image with background -->
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-[#196BE8]/90"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Slider Indicators (Visual only) -->
                <div class="flex items-center justify-center gap-1.5 mt-2 mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                    <div class="w-4 h-1.5 rounded-full bg-[#1A73E8]"></div>
                </div>

                <!-- Card Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا البانر؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2.5 bg-[#E2E8F0] text-slate-500 rounded-xl font-black text-sm flex items-center gap-2 hover:bg-slate-300 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>حذف</span>
                        </button>
                    </form>
                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="px-8 py-2.5 bg-[#1A73E8] text-white rounded-xl font-black text-sm flex items-center gap-2 hover:shadow-lg hover:shadow-[#1A73E8]/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>تعديل</span>
                    </a>
                </div>

            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center p-10 md:p-20 bg-white rounded-[3rem] border border-slate-100 shadow-xl max-w-3xl mx-auto w-full">
                <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-8 text-slate-300">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-3xl font-black text-[#1C1C1C] mb-4">لا توجد بانرات إعلانية</h3>
                <p class="text-slate-500 font-bold mb-8">قم بإضافة أول بانر إعلاني لك ليظهر في التطبيق!</p>
                <button class="px-8 py-3 bg-[#1A73E8] text-white rounded-xl font-black text-sm hover:shadow-xl hover:shadow-[#1A73E8]/20 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>إضافة بانر</span>
                </button>
            </div>
        @endforelse
    </div>

    @if($banners->hasPages())
        <div class="mt-12 flex justify-center">
            {{ $banners->links() }}
        </div>
    @endif
</div>
@endsection
