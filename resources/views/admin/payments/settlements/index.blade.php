@extends('layouts.admin')

@section('title', 'التسوية المالية - لوحة التحكم')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($settlements->pluck('id')->toArray()) }},
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
        <h2 class="text-2xl font-black text-[#1C1C1C]">التسوية المالية</h2>
    </div>

    <!-- Stats Review -->
    <x-stats-cards :stats="$cardStats" />

    <!-- Data Table Wrapper -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
        
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-4">
            
            <!-- Filter Button -->
            <div x-data="{ open: false }" class="relative z-50">
                <button @click="open = !open" class="p-3 bg-white text-slate-600 rounded-xl hover:bg-slate-50 border border-slate-200 transition-all active:scale-95 shadow-sm h-[48px] flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                    </svg>
                </button>
                
                <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 p-8 space-y-6">
                    <form method="GET" action="{{ route('admin.payments.settlements.index') }}">
                        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                        
                        <div class="space-y-6 text-right">
                            <!-- Sort -->
                            <div class="space-y-4">
                                <h3 class="text-[14px] font-black text-[#1C1C1C] leading-none mb-4">الترتيب حسب:</h3>
                                <div class="grid gap-3">
                                    @foreach(['all' => 'الكل', 'name' => 'الاسم', 'newest' => 'الأحدث', 'oldest' => 'الأقدم'] as $key => $label)
                                    <label class="flex items-center group cursor-pointer transition-all gap-2">
                                        <input type="radio" name="sort" value="{{ $key }}" {{ request('sort', 'all') == $key ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                        </div>
                                        <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- Advertiser Type -->
                            <div class="space-y-4">
                                <h3 class="text-[14px] font-black text-[#1C1C1C] leading-none mb-4">نوع المعلن:</h3>
                                <div class="grid gap-3">
                                    @foreach(['all' => 'الكل', 'individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب', 'associate' => 'شريك'] as $key => $label)
                                    <label class="flex items-center group cursor-pointer transition-all gap-2">
                                        <input type="radio" name="advertiser_type" value="{{ $key }}" {{ request('advertiser_type', 'all') == $key ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                        </div>
                                        <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="border-t border-slate-100"></div>

                            <!-- Payment Method -->
                            <div class="space-y-4">
                                <h3 class="text-[14px] font-black text-[#1C1C1C] leading-none mb-4">طريقة الدفع:</h3>
                                <div class="grid gap-3">
                                    @foreach(['all' => 'الكل', 'credit' => 'بطاقة الإئتمان', 'mada' => 'بطاقة مدى'] as $key => $label)
                                    <label class="flex items-center group cursor-pointer transition-all gap-2">
                                        <input type="radio" name="payment_method" value="{{ $key }}" {{ request('payment_method', 'all') == $key ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                        </div>
                                        <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-4 pt-6 border-t border-slate-100">
                                <a href="{{ route('admin.payments.settlements.index') }}" class="flex-1 bg-slate-200 px-4 py-4 text-[#6F6F6F] rounded-2xl text-[15px] font-black text-center hover:bg-slate-300 transition-all">إعادة تعيين</a>
                                <button type="submit" class="flex-[2] bg-[#1A73E8] px-4 py-4 text-white rounded-2xl text-[15px] font-black hover:bg-[#1558B0] shadow-lg shadow-blue-500/30 transition-all">تطبيق</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search -->
            <form action="{{ route('admin.payments.settlements.index') }}" method="GET" class="relative max-w-sm flex-1">
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if(request('advertiser_type')) <input type="hidden" name="advertiser_type" value="{{ request('advertiser_type') }}"> @endif
                @if(request('payment_method')) <input type="hidden" name="payment_method" value="{{ request('payment_method') }}"> @endif
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                        class="block w-full pl-4 pr-10 py-3 text-[14px] font-bold border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] sm:text-sm transition-all h-[48px]" 
                        placeholder="ابحث برقم التسوية أو الاسم...">
            </form>
            
            <div class="flex-1"></div>

            <!-- Download Button -->
            <a href="{{ route('admin.payments.settlements.download', request()->all()) }}" class="px-6 py-3 border border-slate-200 text-[#EAB308] rounded-xl text-sm font-black flex items-center gap-2 hover:bg-yellow-50 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                تحميل
            </a>

            <!-- Blue Buttons: Select all / Dropdown -->
            <div x-data="{ open: false }" class="relative z-40">
                <button @click="open = !open" class="px-5 py-3 h-[48px] bg-[#1A73E8] text-white font-black rounded-xl hover:bg-[#1558B0] shadow-lg shadow-blue-500/30 transition-all flex items-center gap-3">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                    <span>كل التسويات المالية</span>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 py-2">
                    <a href="#" class="block px-4 py-2 text-sm font-bold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">كل التسويات المالية</a>
                    <a href="#" class="block px-4 py-2 text-sm font-bold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">تسويات محولة</a>
                    <a href="#" class="block px-4 py-2 text-sm font-bold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">تسويات معلقة</a>
                </div>
            </div>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-sm text-right align-middle whitespace-nowrap border-separate border-spacing-y-3" style="min-width: 1100px;">
                <thead class="text-[#6F6F6F] font-bold text-[13px] bg-[#F8FAFC]">
                    <tr>
                        <th class="px-6 py-4 w-10 rounded-r-2xl text-center">
                            <input type="checkbox" @click="toggleAll" :checked="isAllSelected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </th>
                        <th class="px-4 py-4 text-center text-[#1A73E8]"># ↑</th>
                        <th class="px-4 py-4">رقم التسوية</th>
                        <th class="px-4 py-4">اسم الإعلان</th>
                        <th class="px-4 py-4">اسم المعلن</th>
                        <th class="px-4 py-4">نوع المعلن</th>
                        <th class="px-4 py-4">إجمالي القيمة</th>
                        <th class="px-4 py-4">طريقة الدفع</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4">التاريخ</th>
                        <th class="px-4 py-4 rounded-l-2xl text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($settlements as $settlement)
                    <tr class="bg-white hover:bg-slate-50 transition-colors shadow-sm rounded-2xl group">
                        <td class="px-6 py-4 rounded-r-2xl border-y border-r border-slate-100 group-hover:border-slate-200 transition-colors text-center">
                            <input type="checkbox" x-model="selected" value="{{ $settlement->id }}" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-center border-y border-slate-100 group-hover:border-slate-200" dir="ltr">{{ $loop->iteration }}</td>
                        <td class="px-4 py-4 font-black border-y border-slate-100 group-hover:border-slate-200" dir="ltr">#{{ $settlement->settlement_number ?? '2041' }}</td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C] border-y border-slate-100 group-hover:border-slate-200">{{ $settlement->ad->title ?? 'اسم الإعلان' }}</td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C] border-y border-slate-100 group-hover:border-slate-200">{{ $settlement->user->name ?? 'أحمد محمد' }}</td>
                        <td class="px-4 py-4 font-bold border-y border-slate-100 group-hover:border-slate-200">
                            @php
                                $typeMap = [
                                    'individual' => 'فرد',
                                    'company' => 'شركة',
                                    'representative' => 'مندوب',
                                ];
                            @endphp
                            {{ $typeMap[$settlement->user->account_type ?? 'individual'] ?? 'فرد' }}
                        </td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C] border-y border-slate-100 group-hover:border-slate-200">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm">{{ number_format($settlement->total_amount ?? 85000) }}</span>
                                <x-riyal-icon class="w-4 h-4 opacity-70 inline-block" />
                            </div>
                        </td>
                        <td class="px-4 py-4 border-y border-slate-100 group-hover:border-slate-200">طريقة الدفع</td>
                        <td class="px-4 py-4 text-center border-y border-slate-100 group-hover:border-slate-200">
                            @if($settlement->status === 'transferred' || $settlement->status === 'completed')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[11px] font-black border border-emerald-100">محولة</span>
                            @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[11px] font-black border border-slate-200">معلقة</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-bold border-y border-slate-100 group-hover:border-slate-200" dir="ltr">
                            {{ $settlement->created_at ? $settlement->created_at->format('Y/m/d') : '2025/09/04' }}
                        </td>
                        <td class="px-4 py-4 rounded-l-2xl border-y border-l border-slate-100 group-hover:border-slate-200 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Transfer Button -->
                                @if($settlement->status !== 'transferred' && $settlement->status !== 'completed')
                                <form action="{{ route('admin.payments.settlements.transfer', $settlement->id) }}" method="POST" class="inline" id="transfer-form-{{ $settlement->id }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="button" onclick="confirmTransfer({{ $settlement->id }})" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all border border-slate-200 hover:border-emerald-200 shadow-sm bg-white" title="تغيير الحالة إلى محولة">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span class="p-2 text-slate-300 border border-slate-100 rounded-lg bg-slate-50 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-slate-500 font-bold text-lg">
                            لا توجد تسويات مالية متاحة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
        <!-- Pagination -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100" dir="ltr">
            <div class="flex items-center gap-2">
                @if($settlements->onFirstPage())
                    <button disabled class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                @else
                    <a href="{{ $settlements->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-[#1A73E8] hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                @endif
                
                <span class="text-sm font-bold text-[#1C1C1C] mx-2">
                    {{ $settlements->currentPage() }} / {{ $settlements->lastPage() }}
                </span>

                @if($settlements->hasMorePages())
                    <a href="{{ $settlements->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#1A73E8] text-white hover:bg-[#1558B0] transition-all shadow-md shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <button disabled class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @endif
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-500">{{ $settlements->firstItem() ?? 0 }}-{{ $settlements->lastItem() ?? 0 }} من {{ $settlements->total() }}</span>
                <span class="text-sm font-bold text-slate-500">الصفوف لكل صفحة: {{ $settlements->perPage() }}</span>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmTransfer(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "هل تريد تحويل حالة التسوية المالية إلى (محولة)؟",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'نعم، قم بالتحويل',
            cancelButtonText: 'إلغاء',
            customClass: {
                title: 'font-black font-cairo',
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold font-cairo',
                cancelButton: 'rounded-xl font-bold font-cairo mr-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('transfer-form-' + id).submit();
            }
        });
    }
</script>
@endpush
