@extends('layouts.admin')

@section('title', 'إدارة البانرات الإعلانية')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    .banners-slider {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding-bottom: 1.25rem;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }
    
    .banners-slider > div {
        flex: 0 0 calc(33.33% - 1rem);
        scroll-snap-align: start;
    }
    
    @media (max-width: 1024px) {
        .banners-slider > div { flex: 0 0 calc(50% - 0.75rem); }
    }
    
    @media (max-width: 640px) {
        .banners-slider > div { flex: 0 0 100%; }
    }
    
    .status-active {
        background-color: #E8F5E9 !important;
        color: #2E7D32 !important;
        border: 1px solid rgba(46, 125, 50, 0.1) !important;
    }
    .status-disabled {
        background-color: #FFEBEE !important;
        color: #C62828 !important;
        border: 1px solid rgba(198, 40, 40, 0.1) !important;
    }
    [x-cloak] { display: none !important; }
</style>

<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl" x-data="{ deleteModalOpen: false, deleteUrl: '' }">
    
    <!-- Header Section -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-[#1C1C1C] mb-2 ">البانرات الإعلانية</h1>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="flex items-center justify-between gap-4 mb-4 w-full  p-4 rounded-2xl border border-slate-100">
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
            <div class="bg-white border border-slate-100 rounded-md p-6 lg:p-8 relative">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-[#1C1C1C]">{{ $banner->name ?? 'الاسم' }}</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-500 font-bold">{{ $banner->created_at ? $banner->created_at->format('Y/m/d') : '3/12/2025' }}</span>
                        <span class="px-5 py-1.5 rounded-xl text-xs font-black shadow-sm {{ $banner->for_user_type === 'partner' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-50 text-slate-600 border border-slate-100' }}">
                            {{ $banner->for_user_type === 'partner' ? 'لوحة الشركاء' : 'رئيسية العامة' }}
                        </span>
                        @if($banner->status)
                            <span class="status-active px-5 py-1.5 rounded-xl text-xs font-black shadow-sm">نشط</span>
                        @else
                            <span class="status-disabled px-5 py-1.5 rounded-xl text-xs font-black shadow-sm">معطل</span>
                        @endif
                    </div>
                </div>

                <!-- Banners Previews (3 Copies as requested by UI design) -->
                <!-- The blue banner component -->
                <div class="banners-slider custom-scrollbar">
                    @foreach($banner->items ?? [] as $item)
                        <div class="relative w-full h-[140px] rounded-md overflow-hidden shadow-sm flex items-center" style="background: linear-gradient(135deg, #1D4ED8 0%, #102A6B 100%) !important;">
                            <!-- Content Right (60%) -->
                            <div class="relative z-10 px-5 py-3 w-[60%] h-full flex flex-col justify-center">
                                <h4 class="text-white font-black text-[14px] mb-1 leading-tight">{{ $item['title'] ?? 'عنوان البانر' }}</h4>
                                <p class="text-blue-100 text-[10px] font-medium leading-snug mb-2 line-clamp-2">
                                    {{ $item['description'] ?? '' }}
                                </p>
                                @if(isset($item['show_button']) && $item['show_button'] == '1')
                                <div>
                                    <span class="bg-white text-[9px] font-black px-4 py-1.5 rounded-lg inline-block whitespace-nowrap shadow-sm" style="color: #1D4ED8 !important;">
                                        {{ $item['button_text'] ?? 'أضف إعلانك' }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <!-- Image Left (40%) -->
                            <div class="absolute left-0 bottom-0 top-0 w-[40%] h-full flex items-center justify-center p-2 overflow-hidden">
                                @if(isset($item['image']) && $item['image'])
                                    <img src="{{ asset($item['image']) }}" class="h-full w-full object-contain" alt="">
                                @else
                                    <div class="w-full h-full bg-white/10 rounded-xl"></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Slider Indicators (Visual only) -->
                <div class="flex items-center justify-center gap-1.5 mt-2 mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                    <div class="w-4 h-1.5 rounded-full bg-[#1A73E8]"></div>
                </div>

                <!-- Card Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="deleteUrl = '{{ route('admin.banners.destroy', $banner->id) }}'; deleteModalOpen = true" class="px-6 py-2.5 bg-[#E2E8F0] text-slate-500 rounded-xl font-black text-sm flex items-center gap-2 hover:bg-slate-300 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>حذف</span>
                    </button>
                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="px-8 py-2.5 bg-[#1A73E8] text-white rounded-xl font-black text-sm flex items-center gap-2 hover:shadow-lg hover:shadow-[#1A73E8]/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>تعديل</span>
                    </a>
                </div>

            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center p-10 md:p-20 bg-white rounded-md border border-slate-100 shadow-xl max-w-3xl mx-auto w-full">
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
            <x-custom-pagination :paginator="$banners" />
        </div>
    @endif

    <!-- CUSTOM Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-md rounded-md p-10 text-center shadow-2xl transform transition-all"
             x-show="deleteModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-8 text-red-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>

            <h3 class="text-2xl font-black text-[#1C1C1C] mb-4">هل أنت متأكد؟</h3>
            <p class="text-slate-500 font-bold mb-10 leading-relaxed text-sm">سيتم حذف هذا البانر بشكل نهائي. لا يمكن التراجع عن هذا الإجراء.</p>

            <div class="flex flex-col gap-3">
                <form :action="deleteUrl" method="POST" class="w-full">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-500 text-white rounded-2xl font-black text-sm hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">تأكيد الحذف</button>
                </form>
                <button @click="deleteModalOpen = false" class="w-full py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-200 transition-all">إلغاء</button>
            </div>
        </div>
    </div>
</div>
@endsection
