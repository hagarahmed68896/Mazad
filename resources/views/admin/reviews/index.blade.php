@extends('layouts.admin')

@section('title', 'التقييمات')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($reviews->pluck('id')->toArray()) }},
        get isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length },
        toggleAll() {
            if (this.isAllSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.allIds];
            }
        },
        submitBulkAction(status) {
            // Placeholder for bulk actions if needed for reviews (e.g. bulk status change)
        }
    }" 
    class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-6 relative">
    
    <!-- Title -->
    <div>
        <h2 class="text-2xl font-black text-[#1C1C1C]">التقييمات</h2>
    </div>

    <!-- Top Metrics Row -->
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
                          class="absolute right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 z-50 p-8 text-right">
                            <form method="GET" action="{{ route('admin.reviews.index') }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                
                                <div class="flex flex-col gap-8">
                                    <!-- Sort Section -->
                                    <div class="flex flex-col gap-4">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الترتيب حسب:</h3>
                                        <div class="grid gap-3">
                                            @foreach(['newest' => 'الأحدث', 'oldest' => 'الأقدم', 'name' => 'الاسم'] as $val => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="sort" value="{{ $val }}" {{ $sort == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Status Section -->
                                    <div class="flex flex-col gap-4">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الحالة:</h3>
                                        <div class="grid gap-3">
                                            @foreach(['all' => 'الكل', 'positive' => 'تقييم إيجابي', 'negative' => 'تقييم سلبي'] as $val => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="status" value="{{ $val }}" {{ $status == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Account Type Section -->
                                    <div class="flex flex-col gap-4">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">نوع الحساب:</h3>
                                        <div class="grid gap-3">
                                            @foreach(['all' => 'الكل', 'individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب', 'partner' => 'شريك'] as $val => $label)
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="account_type" value="{{ $val }}" {{ $accountType == $val ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-4 pt-6">
                                        <a href="{{ route('admin.reviews.index') }}" class="flex-1 bg-slate-100 px-4 py-3 text-slate-500 rounded-2xl text-[11px] font-black text-center border border-slate-100">إعادة تعيين</a>
                                        <button type="submit" class="flex-[1.5] px-4 py-3 bg-[#1A73E8] text-white rounded-2xl text-[11px] font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95">تطبيق</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form method="GET" class="relative max-w-sm w-full">
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('account_type')) <input type="hidden" name="account_type" value="{{ request('account_type') }}"> @endif
                        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث" class="w-full pr-12 pl-4 py-3 border border-slate-200 rounded-xl font-bold text-sm outline-none focus:border-[#1A73E8] transition-all bg-white">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-[#94A3B8] hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.reviews.download', request()->query()) }}" class="px-6 py-2.5 border border-slate-200 text-[#EAB308] rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-yellow-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل
                    </a>
                </div>
            </div>

            <!-- Selection Bar -->
            <div x-show="selected.length > 0" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="flex items-center justify-between w-full bg-[#F8FAFC] p-2 rounded-xl border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-3">
                        <div class="p-1.5 bg-slate-50 text-slate-500 rounded-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-sm font-black text-[#1C1C1C]"><span x-text="selected.length"></span> محدد</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <button @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-sm text-right align-middle whitespace-nowrap border-separate border-spacing-y-3">
                <thead class="text-[#6F6F6F] font-bold text-[13px] bg-[#F8FAFC]">
                    <tr>
                        <th class="px-6 py-4 w-10 rounded-r-2xl">
                            <input type="checkbox" @click="toggleAll" :checked="isAllSelected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </th>
                        <th class="px-4 py-4 w-12 text-center">#</th>
                        <th class="px-4 py-4">اسم المستخدم</th>
                        <th class="px-4 py-4">نوع الحساب</th>
                        <th class="px-4 py-4 text-center">رقم الطلب</th>
                        <th class="px-4 py-4">اسم الإعلان</th>
                        <th class="px-4 py-4">اسم المعلن</th>
                        <th class="px-4 py-4">نوع المعلن</th>
                        <th class="px-4 py-4 text-center">التقييم</th>
                        <th class="px-4 py-4">التعليق</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4 rounded-l-2xl">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($reviews as $review)
                    <tr :class="selected.includes({{ $review->id }}) ? 'bg-blue-50/50 ring-[#1A73E8]' : 'bg-white shadow-sm ring-slate-100'" 
                        class="hover:bg-slate-50 transition-all rounded-2xl group cursor-pointer overflow-hidden ring-1 relative">
                        <td class="px-6 py-4 rounded-r-2xl" @click.stop>
                            <input type="checkbox" value="{{ $review->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-center">{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $review->reviewer->name ?? '---' }}</td>
                        <td class="px-4 py-4">
                            @php
                                $types = ['individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب', 'partner' => 'شريك'];
                                $accType = $review->reviewer->account_type ?? 'individual';
                            @endphp
                            <span>{{ $types[$accType] ?? $accType }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="bg-slate-100 px-3 py-1 rounded-lg text-slate-600 font-bold font-mono">#{{ $review->order_id ?? '---' }}</span>
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-bold">{{ $review->ad->title ?? '---' }}</td>
                        <td class="px-4 py-4 text-[#1A73E8] font-black">{{ $review->ratedUser->name ?? '---' }}</td>
                        <td class="px-4 py-4">
                            @php
                                $advType = $review->ratedUser->account_type ?? 'individual';
                            @endphp
                            <span>{{ $types[$advType] ?? $advType }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-0.5 text-amber-400">
                                @for($i=1; $i<=5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-4 max-w-[200px] truncate" title="{{ $review->comment }}">
                            {{ $review->comment ?? 'بدون تعليق' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($review->status == 'positive')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-bold ring-1 ring-emerald-100">تقييم إيجابي</span>
                            @else
                                <span class="px-3 py-1 bg-rose-50 text-rose-500 rounded-full text-[11px] font-bold ring-1 ring-rose-100">تقييم سلبي</span>
                            @endif
                        </td>
                        <td class="px-4 py-4" dir="ltr">{{ $review->created_at->format('Y/m/d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-16 text-[#6F6F6F] font-bold italic">لا يوجد تقييمات مطابقة للبحث.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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
                <span>{{ $reviews->firstItem() ?? 0 }}-{{ $reviews->lastItem() ?? 0 }} من {{ $reviews->total() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ $reviews->previousPageUrl() }}" class="p-1 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 {{ $reviews->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <span>{{ $reviews->currentPage() }}/{{ $reviews->lastPage() }}</span>
                    <a href="{{ $reviews->nextPageUrl() }}" class="p-1 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 {{ !$reviews->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sparkline Charts for Cards
        const cardStats = @json($cardStats);
        cardStats.forEach((stat, index) => {
            const ctx = document.getElementById(`sparkline-${index}`)?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: stat.sparkline.map((_, i) => i),
                        datasets: [{
                            data: stat.sparkline,
                            borderColor: stat.color,
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: stat.color + '10',
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: { x: { display: false }, y: { display: false } }
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection
