@extends('layouts.admin')

@section('title', 'إدارة الإعلانات - الخدمات')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($services->pluck('id')->toArray()) }},
        showDeleteModal: false,
        showBulkDeleteModal: false,
        deleteId: null,
        deleteTitle: '',
        get isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length },
        toggleAll() {
            if (this.isAllSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.allIds];
            }
        }
    }" 
    class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-6 relative">

    <!-- Confirmation Modals -->
    <template x-teleport="body">
        <div>
            <!-- Single Delete Modal -->
            <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                
                <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-slate-100 text-center">
                    <div class="flex flex-col items-center text-center gap-6">
                        <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center text-rose-500 shadow-inner" style="background-color: #FFF1F2; color: #F43F5E;">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        
                        <div class="space-y-2">
                            <h3 class="text-2xl font-black text-slate-800">تأكيد حذف الخدمة</h3>
                            <p class="text-slate-500 font-bold leading-relaxed">هل أنت متأكد من رغبتك في حذف الخدمة <span class="text-rose-500 font-black" x-text="`(${deleteTitle})`"></span>؟ لا يمكن التراجع عن هذا الإجراء.</p>
                        </div>

                        <div class="flex items-center gap-4 w-full pt-4">
                            <button type="button" @click="showDeleteModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95 text-sm" style="background-color: #F1F5F9; color: #475569;">إلغاء</button>
                            <form :action="`{{ url('admin/ads/services') }}/${deleteId}`" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-6 py-4 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl font-black shadow-xl shadow-rose-500/20 hover:scale-[1.02] active:scale-95 transition-all text-sm" style="background-color: #EF4444; color: #ffffff;">نعم، حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Delete Modal -->
            <div x-show="showBulkDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div x-show="showBulkDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showBulkDeleteModal = false"></div>
                
                <div x-show="showBulkDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-slate-100 text-center">
                    <div class="flex flex-col items-center text-center gap-6">
                        <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center text-rose-500 shadow-inner" style="background-color: #FFF1F2; color: #F43F5E;">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        
                        <div class="space-y-2">
                            <h3 class="text-2xl font-black text-slate-800">حذف الخدمات المحددة</h3>
                            <p class="text-slate-500 font-bold leading-relaxed">هل أنت متأكد من رغبتك في حذف <span class="text-rose-500 font-black" x-text="`${selected.length} خدمة`"></span>؟ سيتم حذفها نهائياً من النظام.</p>
                        </div>

                        <div class="flex items-center gap-4 w-full pt-4">
                            <button type="button" @click="showBulkDeleteModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95 text-sm" style="background-color: #F1F5F9; color: #475569;">إلغاء</button>
                            <form action="{{ route('admin.ads.services.bulk-destroy') }}" method="POST" class="flex-1">
                                @csrf
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="selected[]" :value="id">
                                </template>
                                <button type="submit" class="w-full px-6 py-4 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl font-black shadow-xl shadow-rose-500/20 hover:scale-[1.02] active:scale-95 transition-all text-sm" style="background-color: #EF4444; color: #ffffff;">نعم، حذف الكل</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Title & Top Action -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-[#1C1C1C]">الخدمات</h2>
    </div>

    <!-- Data Table Wrapper -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
        
        <!-- Toolbar & Selection Bar -->
        <div class="h-14">
            <!-- Standard Toolbar -->
            <div x-show="selected.length === 0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Advanced Filter Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-3 bg-white text-slate-600 rounded-xl hover:bg-slate-50 border border-slate-200 transition-all active:scale-95 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 z-50 p-8 space-y-6">
                            <form method="GET" action="{{ route('admin.ads.services') }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                
                                <div class="space-y-6 text-right">
                                    <!-- Sort Section -->
                                    <div class="space-y-3">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الترتيب حسب:</h3>
                                        <div class="grid gap-2">
                                            @foreach(['newest' => 'الأحدث', 'oldest' => 'الأقدم', 'name' => 'الاسم'] as $key => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="sort" value="{{ $key }}" {{ $sort == $key ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Category Section -->
                                    <div class="space-y-3">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">فئة الخدمة:</h3>
                                        <div class="grid gap-2 max-h-40 overflow-y-auto">
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="category_id" value="all" {{ $category_id == 'all' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">الكل</span>
                                            </label>
                                            @foreach($categories as $category)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="category_id" value="{{ $category->id }}" {{ $category_id == $category->id ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $category->name }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Status Section -->
                                    <div class="space-y-3">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الحالة:</h3>
                                        <div class="grid gap-2">
                                            @foreach(['all' => 'الكل', 'active' => 'نشط', 'pending' => 'قيد الانتظار', 'rejected' => 'مرفوض'] as $key => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="status" value="{{ $key }}" {{ $status == $key ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-4 pt-6 border-t border-slate-100">
                                        <a href="{{ route('admin.ads.services') }}" class="flex-1 bg-slate-100 px-4 py-3 text-slate-500 rounded-2xl text-[13px] font-black text-center hover:bg-slate-200 transition-all">إعادة تعيين</a>
                                        <button type="submit" class="flex-[1.5] px-4 py-3 bg-[#1A73E8] text-white rounded-2xl text-[13px] font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95">تطبيق</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form method="GET" action="{{ route('admin.ads.services') }}" class="relative max-w-md w-full">
                        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث" class="w-full pr-12 pl-4 py-3 border border-slate-200 rounded-xl font-bold text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-slate-400 hover:text-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.ads.services.download', request()->all()) }}" class="px-6 py-3 border border-slate-200 text-[#EAB308] rounded-xl text-sm font-black flex items-center gap-2 hover:bg-yellow-50 transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل
                    </a>
                </div>
            </div>

            <!-- Selection Bar -->
            <div x-show="selected.length > 0" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="flex items-center justify-between w-full bg-[#F8FAFC] p-2 rounded-xl border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-3">
                        <span class="text-sm font-black text-[#1C1C1C]"><span x-text="selected.length"></span> محدد</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showBulkDeleteModal = true" class="px-4 py-2 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors flex items-center gap-2 font-black text-xs" style="color: #EF4444;" title="حذف جماعي">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>حذف المحدد</span>
                    </button>
                    <button type="button" @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-sm text-right align-middle whitespace-nowrap border-separate border-spacing-y-3">
                <thead class="text-[#6F6F6F] font-bold text-[13px] bg-[#F8FAFC]">
                    <tr>
                        <th class="px-6 py-4 w-10 rounded-r-2xl text-center">
                            <input type="checkbox" @click="toggleAll" :checked="isAllSelected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </th>
                        <th class="px-4 py-4 w-12 text-center text-[#1A73E8]"># ↑</th>
                        <th class="px-4 py-4">صورة الخدمة</th>
                        <th class="px-4 py-4">اسم الخدمة</th>
                        <th class="px-4 py-4">فئة الخدمة</th>
                        <th class="px-4 py-4">الوصف</th>
                        <th class="px-4 py-4">اسم المعلن</th>
                        <th class="px-4 py-4">السعر</th>
                        <th class="px-4 py-4">حالة الخدمة</th>
                        <th class="px-4 py-4">التاريخ</th>
                        <th class="px-6 py-4 rounded-l-2xl text-center w-36">العمليات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($services as $service)
                    <tr class="bg-white shadow-sm ring-1 ring-slate-100 hover:bg-slate-50 transition-all rounded-2xl group relative overflow-hidden">
                        <td class="px-6 py-4 rounded-r-2xl text-center" @click.stop>
                            <input type="checkbox" value="{{ $service->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-center">{{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                        <td class="px-4 py-4">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-100">
                                <img src="{{ $service->image ? (Str::startsWith($service->image, 'http') ? $service->image : asset($service->image)) : 'https://ui-avatars.com/api/?name=' . urlencode($service->title) . '&background=F1F5F9&color=64748B' }}" class="w-full h-full object-cover" alt="">
                            </div>
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $service->title }}</td>
                        <td class="px-4 py-4">{{ $service->category->name ?? 'غير مصنف' }}</td>
                        <td class="px-4 py-4 max-w-[150px] truncate" title="{{ $service->description }}">{{ $service->description }}</td>
                        <td class="px-4 py-4 font-bold">{{ $service->user->name ?? 'مستخدم غير معروف' }}</td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C]">
                            <div class="flex items-center gap-1.5">
                                <span>{{ number_format($service->price) }}</span>
                                <x-riyal-icon class="w-4 h-4 opacity-70 inline-block" />
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($service->status == 'active')
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1 rounded-full text-xs font-black">نشط</span>
                            @elseif($service->status == 'pending')
                                <span class="bg-amber-50 text-amber-600 border border-amber-100 px-3 py-1 rounded-full text-xs font-black">قيد الانتظار</span>
                            @else
                                <span class="bg-rose-50 text-rose-600 border border-rose-100 px-3 py-1 rounded-full text-xs font-black">مرفوض</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-400 text-xs font-bold" dir="ltr">
                            {{ $service->created_at->format('Y/m/d') }}
                        </td>
                        <td class="px-6 py-4 rounded-l-2xl text-center" @click.stop>
                            <div class="flex items-center justify-center gap-1.5">
                                @if($service->status == 'pending')
                                    <form action="{{ route('admin.ads.services.approve', $service->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-[#10B981] hover:bg-emerald-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-black transition-all active:scale-95 shadow-sm" style="background-color: #10B981;" title="قبول الخدمة">
                                            قبول
                                        </button>
                                    </form>
                                    <button type="button" onclick="rejectService(event, '{{ $service->id }}')" class="bg-[#F59E0B] hover:bg-amber-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-black transition-all active:scale-95 shadow-sm" style="background-color: #F59E0B;" title="رفض الخدمة">
                                        رفض
                                    </button>
                                @endif
                                <button type="button" @click.stop="deleteId = {{ $service->id }}; deleteTitle = '{{ addslashes($service->title) }}'; showDeleteModal = true" class="p-1.5 bg-slate-50 text-slate-400 rounded-lg hover:bg-rose-500 hover:text-white transition-all group-hover:shadow-sm" title="حذف الخدمة">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-24">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-4 4m0 0l-4-4m4 4V13"></path></svg>
                                <p class="text-lg font-bold">لا يوجد خدمات</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($services->hasPages())
        <div class="mt-6">
            <x-custom-pagination :paginator="$services" />
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function rejectService(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'سبب الرفض',
            text: 'يرجى إدخال سبب رفض الخدمة لإرساله للشريك:',
            input: 'textarea',
            inputPlaceholder: 'أدخل سبب الرفض هنا...',
            showCancelButton: true,
            confirmButtonText: 'إرسال الرفض',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            customClass: {
                title: 'font-black font-cairo',
                popup: 'rounded-[20px]',
                confirmButton: 'rounded-xl font-bold font-cairo',
                cancelButton: 'rounded-xl font-bold font-cairo mr-2'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'يجب إدخال سبب الرفض!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a form dynamically and submit it
                const form = document.createElement('form');
                form.action = `/admin/ads/services/${id}/reject`;
                form.method = 'POST';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reject_reason';
                reasonInput.value = result.value;
                form.appendChild(reasonInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
