@extends('layouts.admin')

@section('title', 'الاستفسارات و الشكاوى')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($tickets->pluck('id')->map(fn($id) => (string)$id)->toArray()) }},
        get isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length },
        toggleAll() {
            if (this.isAllSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.allIds];
            }
        },
        showDeleteModal: false,
        deleteId: null
    }" 
    class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-6 relative">
    
    <!-- Title -->
    <div>
        <h2 class="text-2xl font-black text-[#1C1C1C]">الاستفسارات و الشكاوى</h2>
    </div>

    <!-- Data Table Wrapper -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
        
        <!-- Toolbar -->
        <div class="h-14">
            <div x-show="selected.length === 0" x-transition class="flex items-center justify-between gap-4">
                <!-- Right Side: Filter & Search -->
                <div class="flex items-center gap-3 flex-1">
                    <!-- Advanced Filter Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="h-11 w-11 flex items-center justify-center bg-[#F1F3F5] text-slate-600 rounded-xl hover:bg-slate-200 transition-all active:scale-95 border-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 z-50 p-8">
                            <form method="GET" action="{{ route('admin.tickets.index') }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                                
                                <div class="flex flex-col" style="gap: 20px;">
                                    <!-- Sort Section -->
                                    <div class="flex flex-col" style="gap: 10px;">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الترتيب حسب:</h3>
                                        <div class="grid" style="gap: 10px;">
                                            @foreach(['all' => 'الكل', 'name' => 'الاسم', 'newest' => 'الأحدث', 'oldest' => 'الأقدم'] as $val => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="sort" value="{{ $val }}" {{ request('sort', 'all') == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Ticket Type Section -->
                                    <div class="flex flex-col" style="gap: 10px;">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">نوع التذكرة:</h3>
                                        <div class="grid" style="gap: 10px;">
                                            @foreach(['all' => 'الكل', 'report' => 'بلاغ', 'inquiry' => 'استفسار'] as $val => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="type" value="{{ $val }}" {{ request('type', 'all') == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Account Type Section -->
                                    <div class="flex flex-col" style="gap: 10px;">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">نوع الحساب:</h3>
                                        <div class="grid" style="gap: 10px;">
                                            @foreach(['all' => 'الكل', 'individual' => 'الأفراد', 'company' => 'الشركات', 'representative' => 'المندوبين', 'partner' => 'الشركاء'] as $val => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="account_type" value="{{ $val }}" {{ request('account_type', 'all') == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-4 pt-6 border-t border-slate-100">
                                        <a href="{{ route('admin.tickets.index') }}" class="flex-1 bg-[#D8D8D8] px-4 py-3 text-[#6F6F6F] rounded-2xl text-[13px] font-black text-center">إعادة تعيين</a>
                                        <button type="submit" class="flex-[1.5] px-4 py-3 bg-[#1A73E8] text-white rounded-2xl text-[13px] font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95">تطبيق</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <form action="{{ route('admin.tickets.index') }}" class="relative max-w-sm w-full group">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث" 
                               class="w-full h-11 pr-12 pl-4 border border-slate-200 rounded-xl font-semibold text-sm outline-none focus:ring-2 focus:ring-blue-100 transition-all bg-[#F8FAFC]/50 text-right">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-[#94A3B8] hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <!-- Left Side: Download & Quick Status -->
                <div class="flex items-center gap-3">
                    <!-- Download -->
                    <a href="{{ route('admin.tickets.download', request()->all()) }}" class="h-11 bg-white text-amber-600 px-6 rounded-xl border border-amber-200 flex items-center gap-2 text-sm font-black transition-all hover:bg-amber-50 active:scale-95 whitespace-nowrap shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل
                    </a>

                    <!-- Status Quick Filter -->
                    <div x-data="{ open: false, status: '{{ request('status', 'all') }}' }" class="relative">
                        <button @click="open = !open" class="h-11 px-6 bg-[#1A73E8] text-white rounded-xl flex items-center gap-3 text-sm font-black transition-all hover:bg-blue-600 active:scale-95 shadow-lg shadow-blue-200">
                            <span x-text="status == 'all' ? 'الكل' : (status == 'resolved' ? 'تم الحل' : (status == 'under_review' ? 'قيد المراجعة' : 'مرفوضة'))"></span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-2 overflow-hidden">
                            @foreach(['all' => 'الكل', 'under_review' => 'قيد المراجعة', 'resolved' => 'تم الحل', 'rejected' => 'مرفوضة'] as $val => $label)
                                <a href="{{ route('admin.tickets.index', array_merge(request()->all(), ['status' => $val])) }}" class="block px-4 py-3 text-sm font-bold {{ request('status', 'all') == $val ? 'bg-blue-50 text-[#1A73E8]' : 'text-slate-600 hover:bg-slate-50' }} rounded-xl transition-all">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selection Toolbar -->
            <div x-show="selected.length > 0" x-cloak x-transition class="flex items-center justify-between w-full bg-[#F8FAFC] p-2 rounded-xl border border-slate-100 h-14">
                <div class="flex items-center gap-4">
                    <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-3">
                        <span class="text-sm font-black text-[#1C1C1C]"><span x-text="selected.length"></span> محدد</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="if(confirm('هل أنت متأكد من الحذف الجماعي؟')) $refs.bulkDeleteForm.submit()" class="p-2.5 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="حذف جماعي">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    <button @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
                </div>
                
                <form x-ref="bulkDeleteForm" action="{{ route('admin.tickets.bulk-destroy') }}" method="POST" class="hidden">
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
                        <th class="px-4 py-4 w-12 lowercase">#</th>
                        <th class="px-4 py-4">رقم التذكرة</th>
                        <th class="px-4 py-4">نوع التذكرة</th>
                        <th class="px-4 py-4">اسم المستخدم</th>
                        <th class="px-4 py-4">نوع الحساب</th>
                        <th class="px-4 py-4">رقم الجوال</th>
                        <th class="px-4 py-4">المُبلغ عنه</th>
                        <th class="px-4 py-4 max-w-[150px]">وصف المشكلة</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-6 py-4 rounded-l-2xl">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @foreach($tickets as $ticket)
                    <tr :class="selected.includes('{{ $ticket->id }}') ? 'bg-blue-50/50 ring-[#1A73E8]' : 'bg-white shadow-sm ring-slate-100'" 
                        class="hover:bg-slate-50 transition-all rounded-2xl group cursor-pointer overflow-hidden ring-1 relative h-16 border-slate-100"
                        @click="window.location.href = '{{ route('admin.tickets.show', $ticket->id) }}'">
                        <td class="px-6 py-4 rounded-r-2xl" @click.stop>
                            <input type="checkbox" value="{{ $ticket->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $loop->iteration + ($tickets->currentPage()-1) * $tickets->perPage() }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $ticket->ticket_number }}</td>
                        <td class="px-4 py-4">
                            @if($ticket->type == 'report')
                                <span class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg text-[11px] font-bold">بلاغ</span>
                            @else
                                <span class="px-2 py-1 bg-blue-50 text-[#1A73E8] rounded-lg text-[11px] font-bold">استفسار</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $ticket->user?->name ?? 'مستخدم محذوف' }}</td>
                        <td class="px-4 py-4 text-[13px]">
                            @php
                                $accTypes = ['individual' => 'الأفراد', 'company' => 'الشركات', 'representative' => 'مندوبين', 'partner' => 'الشركاء'];
                                $accType = $ticket->user?->account_type ?? 'غير معروف';
                            @endphp
                            {{ $accTypes[$accType] ?? $accType }}
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black" dir="ltr">{{ $ticket->phone ?? $ticket->user?->phone }}</td>
                        <td class="px-4 py-4">
                            @if($ticket->reported_about)
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                    <span class="text-rose-600 font-bold">{{ $ticket->reported_about }}</span>
                                </div>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-[13px] opacity-70 max-w-[150px] truncate" title="{{ $ticket->description }}">{{ Str::limit($ticket->description, 30) }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($ticket->status == 'resolved')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-black ring-1 ring-emerald-100">تم الحل</span>
                            @elseif($ticket->status == 'under_review')
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[11px] font-black ring-1 ring-slate-200">قيد المراجعة</span>
                            @else
                                <span class="px-3 py-1 bg-rose-50 text-rose-500 rounded-full text-[11px] font-black ring-1 ring-rose-100">مرفوضة</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-500 rounded-l-2xl" dir="ltr">{{ $ticket->created_at->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                    @if($tickets->isEmpty())
                    <tr>
                        <td colspan="12" class="text-center py-20 text-[#6F6F6F] font-bold border ring-1 ring-slate-100 rounded-2xl bg-white shadow-sm">لا توجد حالات دعم فني مضافة.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t border-slate-100 pt-6">
            <div class="flex items-center gap-2 text-sm text-[#6F6F6F] font-bold">
                <span>الصفوف لكل صفحة:</span>
                <form action="{{ route('admin.tickets.index') }}" method="GET" x-ref="perPageForm">
                    <select name="per_page" @change="$refs.perPageForm.submit()" class="border-transparent bg-transparent outline-none cursor-pointer focus:ring-0">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </div>
            
            <div class="flex items-center gap-4 text-sm font-bold text-[#6F6F6F]">
                <span>{{ $tickets->firstItem() ?? 0 }}-{{ $tickets->lastItem() ?? 0 }} من {{ $tickets->total() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ $tickets->nextPageUrl() }}" class="p-1 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 transition-all {{ !$tickets->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <span>{{ $tickets->currentPage() }}/{{ $tickets->lastPage() ?: 1 }}</span>
                    <a href="{{ $tickets->previousPageUrl() }}" class="p-1 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 transition-all {{ $tickets->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
