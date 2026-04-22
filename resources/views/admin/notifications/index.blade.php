@extends('layouts.admin')

@section('title', 'إدارة الإشعارات')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($notifications->pluck('id')->toArray()) }},
        get isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length },
        toggleAll() {
            if (this.isAllSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.allIds];
            }
        },
        showDeleteModal: false,
        deleteId: null,
    }" 
    class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-6 relative">
    
    <!-- Title -->
    <div>
        <h2 class="text-2xl font-black text-[#1C1C1C]">إدارة الإشعارات</h2>
    </div>

    <!-- Top Metrics Row -->
    <x-stats-cards :stats="$cardStats" />

    <!-- Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
            
            <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-slate-100">
                <div class="flex flex-col items-center text-center gap-6">
                    <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center text-rose-500 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="text-2xl font-black text-slate-800">تأكيد الحذف</h3>
                        <p class="text-slate-500 font-bold leading-relaxed">هل أنت متأكد من رغبتك في حذف هذا الإشعار؟ لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>

                    <div class="flex items-center gap-4 w-full pt-4">
                        <button @click="showDeleteModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95">إلغاء</button>
                        <form :action="`{{ url('admin/notifications') }}/${deleteId}`" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-6 py-4 bg-rose-500 text-white rounded-2xl font-black shadow-xl shadow-rose-500/20 hover:scale-[1.02] active:scale-95 transition-all">نعم، حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Data Table Wrapper -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
        
        <!-- Toolbar -->
        <div class="h-14">
            <div x-show="selected.length === 0" x-transition class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-3 bg-white text-slate-600 rounded-xl hover:bg-slate-50 border border-slate-200 transition-all active:scale-95 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-4 w-[320px] bg-white rounded-[2rem] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 z-50 p-8 text-right">
                            <form method="GET" action="{{ route('admin.notifications.index') }}" class="flex flex-col gap-8">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                
                                <!-- Sort Section -->
                                <div class="flex flex-col gap-4">
                                    <h3 class="text-lg font-black text-slate-800">الترتيب حسب:</h3>
                                    <div class="flex flex-col gap-4">
                                        @foreach(['all' => 'الكل', 'name' => 'الاسم', 'newest' => 'الأحدث', 'oldest' => 'الأقدم'] as $val => $label)
                                        <label class="flex items-center justify-start gap-3 cursor-pointer group">
                                            <div class="relative flex items-center justify-center">
                                                <input type="radio" name="sort" value="{{ $val }}" {{ request('sort', 'newest') == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all">
                                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
                                            </div>
                                            <span class="text-[17px] font-bold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="w-full h-px bg-slate-100/50"></div>

                                <!-- Type Section -->
                                <div class="flex flex-col gap-4">
                                    <h3 class="text-lg font-black text-slate-800">نوع الاشعار:</h3>
                                    <div class="flex flex-col gap-4">
                                        @foreach(['all' => 'الكل', 'warning' => 'تنبيه', 'notification' => 'الاشعار'] as $val => $label)
                                        <label class="flex items-center justify-start gap-3 cursor-pointer group">
                                            <div class="relative flex items-center justify-center">
                                                <input type="radio" name="type" value="{{ $val }}" {{ request('type', 'all') == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all">
                                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
                                            </div>
                                            <span class="text-[17px] font-bold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="w-full h-px bg-slate-100/50"></div>

                                <!-- Status Section -->
                                <div class="flex flex-col gap-4">
                                    <h3 class="text-lg font-black text-slate-800">الحالة:</h3>
                                    <div class="flex flex-col gap-4">
                                        @foreach(['all' => 'الكل', 'sent' => 'مرسل', 'scheduled' => 'مجدول', 'unsent' => 'غير مرسل'] as $val => $label)
                                        <label class="flex items-center justify-start gap-3 cursor-pointer group">
                                            <div class="relative flex items-center justify-center">
                                                <input type="radio" name="status" value="{{ $val }}" {{ request('status', 'all') == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all">
                                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
                                            </div>
                                            <span class="text-[17px] font-bold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 pt-4">
                                    <a href="{{ route('admin.notifications.index') }}" class="flex-1 h-14 bg-slate-100 text-[#6F6F6F] rounded-2xl font-black flex items-center justify-center hover:bg-slate-200 transition-all text-lg">إعادة تعيين</a>
                                    <button type="submit" class="flex-1 h-14 bg-[#1A73E8] text-white rounded-2xl font-black shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all text-lg">تطبيق</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form action="{{ route('admin.notifications.index') }}" class="relative max-w-sm w-full group">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث" 
                               class="w-full h-11 pr-12 pl-4 border border-slate-200 rounded-xl font-semibold text-sm outline-none focus:ring-2 focus:ring-blue-100 transition-all bg-white">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-[#94A3B8] hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <a href="{{ route('admin.notifications.create') }}" class="h-12 bg-[#1A73E8] hover:bg-blue-600 text-white px-6 rounded-xl flex items-center gap-2 text-sm font-black transition-all shadow-lg shadow-blue-200 active:scale-95 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    إضافة إشعار جديد
                </a>
            </div>

            <!-- Selection Toolbar -->
            <div x-show="selected.length > 0" x-cloak x-transition class="flex items-center justify-between bg-[#F8FAFC] p-2 rounded-xl border border-slate-100 h-14">
                <div class="flex items-center gap-4">
                    <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-3">
                        <div class="p-1.5 bg-slate-50 text-slate-500 rounded-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </div>
                        <span class="text-sm font-black text-[#1C1C1C]"><span x-text="selected.length"></span> محدد</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="if(confirm('هل أنت متأكد من حذف الإشعارات المحددة؟')) $refs.bulkDeleteForm.submit()" class="p-2.5 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="حذف جماعي">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    <button @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
                </div>

                <form x-ref="bulkDeleteForm" action="{{ route('admin.notifications.bulk-destroy') }}" method="POST" class="hidden">
                    @csrf
                    <template x-for="id in selected">
                        <input type="hidden" name="selected[]" :value="id">
                    </template>
                </form>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-sm text-right align-middle whitespace-nowrap border-separate border-spacing-y-3">
                <thead class="text-[#6F6F6F] font-bold text-[13px] bg-[#F8FAFC]">
                    <tr>
                        <th class="px-6 py-4 w-10 rounded-r-2xl">
                            <input type="checkbox" @click="toggleAll" :checked="isAllSelected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </th>
                        <th class="px-4 py-4 w-12 lowercase"># <span class="text-[#1A73E8] ml-1">↑</span></th>
                        <th class="px-4 py-4">نوع الإشعار</th>
                        <th class="px-4 py-4">عنوان الإشعار</th>
                        <th class="px-4 py-4">محتوى الإشعار</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4">التاريخ</th>
                        <th class="px-4 py-4 text-center rounded-l-2xl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($notifications as $notify)
                    <tr :class="selected.includes({{ $notify->id }}) ? 'bg-blue-50/50 ring-[#1A73E8]' : 'bg-white shadow-sm ring-slate-100'" 
                        class="hover:bg-slate-50 transition-all rounded-2xl group cursor-pointer overflow-hidden ring-1 relative h-16">
                        <td class="px-6 py-4 rounded-r-2xl" @click.stop>
                            <input type="checkbox" value="{{ $notify->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">#{{ $notify->id }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[#1A73E8]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </div>
                                <span class="text-[14px]">{{ $notify->type }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black italic">{{ $notify->title }}</td>
                        <td class="px-4 py-4 text-[13px] opacity-70">{{ Str::limit($notify->content, 40) }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($notify->status == 'sent')
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-bold ring-1 ring-emerald-100">مرسل</span>
                            @elseif($notify->status == 'scheduled')
                                <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-[11px] font-bold ring-1 ring-blue-100">مجدول</span>
                            @else
                                <span class="px-2 py-1 bg-rose-50 text-rose-500 rounded-full text-[11px] font-bold ring-1 ring-rose-100">غير مرسل</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm font-bold text-slate-500" dir="ltr">{{ $notify->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-4 text-center rounded-l-2xl">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Send Now -->
                                <form action="{{ route('admin.notifications.send', $notify->id) }}" method="POST" class="m-0 p-0 inline-block">
                                    @csrf
                                    <button type="submit" class="p-2.5 bg-slate-50 text-slate-400 rounded-xl hover:bg-[#1A73E8] hover:!text-white transition-all group-hover:shadow-sm" title="إرسال الآن">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    </button>
                                </form>
                                <!-- Edit -->
                                <a href="{{ route('admin.notifications.edit', $notify->id) }}" class="p-2.5 bg-slate-50 text-slate-400 rounded-xl hover:bg-emerald-500 hover:text-white transition-all group-hover:shadow-sm" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <!-- Delete -->
                                <button type="button" @click="deleteId = {{ $notify->id }}; showDeleteModal = true" class="p-2.5 bg-slate-50 text-slate-400 rounded-xl hover:bg-rose-500 hover:text-white transition-all group-hover:shadow-sm" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-20 text-[#6F6F6F] font-bold italic border ring-1 ring-slate-100 rounded-2xl bg-white shadow-sm">لا توجد إشعارات حالية تتطابق مع البحث.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t border-slate-100 pt-6">
            <div class="flex items-center gap-2 text-sm text-[#6F6F6F] font-bold">
                <span>الصفوف لكل صفحة:</span>
                <select class="border-transparent bg-transparent outline-none cursor-pointer focus:ring-0">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>
            
            <div class="flex items-center gap-4 text-sm font-bold text-[#6F6F6F]">
                <span>{{ $notifications->firstItem() ?? 0 }}-{{ $notifications->lastItem() ?? 0 }} من {{ $notifications->total() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ $notifications->nextPageUrl() }}" 
                       class="p-1 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 transition-all {{ !$notifications->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    
                    <span>{{ $notifications->currentPage() }}/{{ $notifications->lastPage() ?: 1 }}</span>
                    
                    <a href="{{ $notifications->previousPageUrl() }}" 
                       class="p-1 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 transition-all {{ $notifications->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
