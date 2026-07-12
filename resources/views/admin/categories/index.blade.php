@extends('layouts.admin')

@section('title', 'إدارة الفئات')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">
    
    <!-- Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-black text-[#1C1C1C]">إدارة الفئات</h1>
    </div>

    <!-- Toolbar -->
    <div class="flex items-center justify-between gap-4 mb-8">
        <!-- Search -->
        <form method="GET" action="{{ route('admin.categories.index') }}" class="relative w-[250px] md:w-[400px]">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="block w-full pr-10 pl-4 py-3 text-[14px] font-bold border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] transition-all h-[48px]" 
                   placeholder="ابحث">
        </form>

        <!-- Add Button -->
        <a href="{{ route('admin.categories.create') }}" class="px-6 py-3 h-[48px] bg-[#1A73E8] text-white font-black rounded-xl hover:bg-blue-700 hover:shadow-lg transition-all flex shrink-0 items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            إضافة فئة
        </a>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="{ openId: null }" @click.window="openId = null">
        @forelse($categories as $category)
            <div class="bg-white rounded-2xl p-5 border border-slate-100 flex items-center justify-between hover:shadow-md transition-all shadow-sm cursor-pointer group"
                 @click="window.location.href = '{{ route('admin.categories.show', $category->id) }}'">
                
                <!-- Category Info -->
                <div class="flex items-center gap-5">
                    <!-- Image -->
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center p-2 shrink-0 overflow-hidden group-hover:bg-slate-100 transition-colors">
                        @if($category->image)
                            <img src="{{ $category->image }}" class="w-full h-full object-cover" alt="{{ $category->name }}">
                        @else
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        @endif
                    </div>
                    
                    <!-- Text Details -->
                    <div>
                        <h3 class="text-[17px] font-black text-[#1C1C1C] mb-2 text-right group-hover:text-[#1A73E8] transition-colors">{{ $category->name }}</h3>
                        <div class="flex flex-wrap items-center gap-4 text-xs font-bold text-slate-500">
                             <!-- Users Count -->
                             <div class="flex items-center gap-1.5 flex-row-reverse" title="عدد المستخدمين">
                                 <span class="text-[13px] text-[#6F6F6F]" dir="ltr">مستخدم <span class="font-black text-[#1C1C1C]">{{ number_format($category->users_count ?? 0) }}</span></span>
                                 <svg class="w-4 h-4 text-[#1C1C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                             </div>
                            <!-- Ads Count -->
                            <div class="flex items-center gap-1.5 flex-row-reverse" title="عدد الإعلانات">
                                <span class="text-[13px] text-[#6F6F6F]" dir="ltr">إعلان <span class="font-black text-[#1C1C1C]">{{ number_format($category->ads_count ?? 500) }}</span></span>
                                <img class="w-4 h-4 text-slate-400" src="{{ asset('images/legal - outline.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions (3 dots) -->
                <div class="relative" :class="openId === {{ $category->id }} ? 'z-50' : ''" @click.stop>
                    <button @click.stop="openId = (openId === {{ $category->id }}) ? null : {{ $category->id }}"
                            class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="openId === {{ $category->id }}"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         @click.stop
                         class="absolute left-0 mt-2 bg-white rounded-3xl shadow-xl border border-slate-100 py-2 z-50 origin-top-left" style="min-width: max-content;">

                        <!-- إضافة خصائص -->
                        <a href="{{ route('admin.categories.properties.create', $category->id) }}"
                           class="mx-2 mb-1 flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 font-bold text-sm hover:bg-slate-50 hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <line x1="17.5" y1="14" x2="17.5" y2="21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <line x1="14" y1="17.5" x2="21" y2="17.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <span class="whitespace-nowrap">إضافة خصائص</span>
                        </a>

                        <!-- تعديل الخصائص -->
                        <a href="{{ route('admin.categories.properties.index', $category->id) }}"
                           class="mx-2 mb-1 flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 font-bold text-sm hover:bg-slate-50 hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <path d="M14.5 18.5l3-3 2 2-3 3h-2v-2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="none"/>
                            </svg>
                            <span class="whitespace-nowrap">تعديل الخصائص</span>
                        </a>

                        <div class="h-px bg-slate-100 mx-3 my-1"></div>

                        <!-- تعديل الفئة -->
                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                           class="mx-2 mb-1 flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 font-bold text-sm hover:bg-slate-50 hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.5-6.5a2.121 2.121 0 013 3L12 14H9v-3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 19h12"/>
                            </svg>
                            <span class="whitespace-nowrap">تعديل الفئة</span>
                        </a>

                        <!-- حذف -->
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" id="delete-form-{{ $category->id }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                        <button @click="confirmDelete('{{ $category->id }}')"
                                class="mx-2 w-[calc(100%-16px)] flex items-center gap-3 px-4 py-3 rounded-2xl text-red-500 font-bold text-sm hover:bg-red-50 transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                            </svg>
                            <span class="whitespace-nowrap">حذف</span>
                        </button>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-1 lg:col-span-2 py-12 text-center text-slate-500 font-bold text-lg bg-white rounded-2xl border border-slate-100 shadow-sm">
                لا توجد فئات مطابقة للبحث
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-slate-200 sm:px-6 rounded-2xl shadow-sm">
            <x-custom-pagination :paginator="$categories" />
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم حذف الفئة نهائياً من النظام!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            customClass: {
                title: 'font-black font-cairo',
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold font-cairo',
                cancelButton: 'rounded-xl font-bold font-cairo mr-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush
