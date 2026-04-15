@extends('layouts.admin')

@section('title', 'إدارة المشتريات')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($orders->pluck('id')->toArray()) }},
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

    <!-- Title & Top Action -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-[#1C1C1C]">إدارة المشتريات</h2>
    </div>

    <!-- Stats Review -->
    <x-stats-cards :stats="$cardStats" />

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
                            <form method="GET" action="{{ route('admin.purchases.index') }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                
                                <div class="space-y-6 text-right">
                                    <!-- Sort Section -->
                                    <div class="space-y-3">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest leading-none mb-4">الترتيب حسب:</h3>
                                        <div class="grid gap-2">
                                            @foreach(['all' => 'الكل', 'name' => 'الاسم', 'newest' => 'الأحدث', 'oldest' => 'الأقدم'] as $key => $label)
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
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest leading-none mb-4">فئة الإعلان:</h3>
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

                                    <!-- Ad Type Section -->
                                    <div class="space-y-3">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest leading-none mb-4">نوع الإعلان:</h3>
                                        <div class="grid gap-2">
                                            @foreach(['all' => 'الكل', 'electronic_auction' => 'مزاد إلكتروني', 'hybrid_auction' => 'مزاد هجين', 'direct_purchase' => 'شراء فوري', 'tender' => 'مناقصة'] as $key => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="ad_type" value="{{ $key }}" {{ $ad_type == $key ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- User Type Section -->
                                    <div class="space-y-3">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest leading-none mb-4">نوع المعلن:</h3>
                                        <div class="grid gap-2">
                                            @foreach(['all' => 'الكل', 'individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب'] as $key => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="user_type" value="{{ $key }}" {{ $user_type == $key ? 'checked' : '' }} class="hidden peer">
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
                                        <a href="{{ route('admin.purchases.index') }}" class="flex-1 bg-slate-100 px-4 py-3 text-slate-500 rounded-2xl text-[13px] font-black text-center hover:bg-slate-200 transition-all">إعادة تعيين</a>
                                        <button type="submit" class="flex-[1.5] px-4 py-3 bg-[#1A73E8] text-white rounded-2xl text-[13px] font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95">تطبيق</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form method="GET" action="{{ route('admin.purchases.index') }}" class="relative max-w-md w-full">
                        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث" class="w-full pr-12 pl-4 py-3 border border-slate-200 rounded-xl font-bold text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-slate-400 hover:text-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="px-6 py-3 bg-[#1A73E8] text-white rounded-xl text-sm font-black flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-95">
                            @php
                                $statusLabels = [
                                    'all' => 'كل المشتريات',
                                    'received' => 'تم الاستلام',
                                    'receiving' => 'قيد التسليم',
                                    'pending_payment' => 'قيد الدفع',
                                    'paid' => 'تم الدفع',
                                ];
                            @endphp
                            {{ $statusLabels[$status] ?? 'كل المشتريات' }}
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-3 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-2 z-50 overflow-hidden font-bold">
                            @foreach($statusLabels as $key => $label)
                                <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}" class="block px-6 py-3 text-sm font-bold text-[#6F6F6F] hover:bg-blue-50 hover:text-blue-600 transition-colors">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('admin.purchases.download', request()->all()) }}" class="px-6 py-3 border border-slate-200 text-[#EAB308] rounded-xl text-sm font-black flex items-center gap-2 hover:bg-yellow-50 transition-all active:scale-95">
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
                <button @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
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
                        <th class="px-4 py-4">رقم الطلب</th>
                        <th class="px-4 py-4">اسم الإعلان</th>
                        <th class="px-4 py-4">فئة الإعلان</th>
                        <th class="px-4 py-4">اسم المستخدم</th>
                        <th class="px-4 py-4">اسم المعلن</th>
                        <th class="px-4 py-4">نوع المعلن</th>
                        <th class="px-4 py-4">السعر النهائي</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4 text-center">التاريخ</th>
                        <th class="px-4 py-4 rounded-l-2xl">كود التسليم</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($orders as $order)
                    <tr class="bg-white shadow-sm ring-1 ring-slate-100 hover:bg-slate-50 transition-all rounded-2xl group relative overflow-hidden">
                        <td class="px-6 py-4 rounded-r-2xl text-center" @click.stop>
                            <input type="checkbox" value="{{ $order->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-center">{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                        <td class="px-4 py-4 font-black">#{{ $order->order_number }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black underline">{{ $order->ad->title ?? '-' }}</td>
                        <td class="px-4 py-4">{{ $order->ad->category->name ?? '-' }}</td>
                        <td class="px-4 py-4 font-bold">{{ $order->buyer->name ?? '-' }}</td>
                        <td class="px-4 py-4 font-bold">{{ $order->seller->name ?? '-' }}</td>
                        <td class="px-4 py-4">
                            @php
                                $userTypeLabels = ['individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب'];
                            @endphp
                            {{ $userTypeLabels[$order->seller->account_type] ?? 'غير محدد' }}
                        </td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C]">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-black">{{ number_format($order->final_price) }}</span>
                                <img src="{{ asset('images/Vector.svg') }}" class="w-4 h-4 opacity-70 inline-block" alt="ريال">
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($order->status === 'received')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[11px] font-black border border-emerald-100">تم الاستلام</span>
                            @elseif($order->status === 'receiving')
                                <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[11px] font-black border border-amber-100">قيد التسليم</span>
                            @elseif($order->status === 'pending_payment')
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[11px] font-black border border-blue-100">قيد الدفع</span>
                            @elseif($order->status === 'paid')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[11px] font-black border border-emerald-100">تم الدفع</span>
                            @else
                                <span class="px-3 py-1 bg-slate-50 text-slate-600 rounded-lg text-[11px] font-black border border-slate-100">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-slate-400 text-xs font-bold" dir="ltr">
                            {{ $order->created_at->format('Y/m/d') }}
                        </td>
                        <td class="px-4 py-4 rounded-l-2xl text-center">
                            <span class="px-2 py-1 bg-slate-50 border border-slate-100 rounded text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ $order->delivery_code ?? '-------' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-24">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <p class="text-lg font-bold">لا يوجد مشتريات حالياً</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer (Premium Style) -->
        <div class="flex items-center justify-between border-t border-slate-100 pt-6 mt-2">
            <div class="flex items-center gap-2 text-sm text-[#6F6F6F] font-bold">
                <span>الصفوف لكل صفحة:</span>
                <select class="border-transparent bg-transparent outline-none cursor-pointer focus:ring-0">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>
            
            <div class="flex items-center gap-4 text-sm font-bold text-[#6F6F6F]">
                <span>{{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} من {{ $orders->total() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ $orders->previousPageUrl() }}" 
                       class="p-1.5 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 transition-all {{ $orders->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <span class="px-3 min-w-[3rem] text-center">{{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                    <a href="{{ $orders->nextPageUrl() }}" 
                       class="p-1.5 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 transition-all {{ !$orders->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @keyframes sparkline {
        from { stroke-dashoffset: 100; }
        to { stroke-dashoffset: 0; }
    }
    .animate-sparkline {
        stroke-dasharray: 100;
        animation: sparkline 2s ease-out forwards;
    }
</style>
@endsection



