@extends('layouts.admin')

@section('title', 'إدارة المدفوعات - إدارة العمولات')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($commissions->pluck('id')->toArray()) }},
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
        <h2 class="text-2xl font-black text-[#1C1C1C]">إدارة العمولات</h2>
    </div>

    <!-- Data Table Wrapper -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
        
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4 h-14">
            <div class="flex items-center gap-4">
                <!-- Filter Button -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-3 bg-white text-slate-600 rounded-xl hover:bg-slate-50 border border-slate-200 transition-all active:scale-95 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 z-50 p-8 space-y-6">
                        <form method="GET" action="{{ route('admin.payments.commissions.index') }}">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            
                            <div class="space-y-6 text-right">
                                <!-- Sort Section -->
                                <div class="space-y-4">
                                    <h3 class="text-[14px] font-black text-[#1C1C1C] leading-none mb-4">الترتيب حسب:</h3>
                                    <div class="grid gap-3">
                                        @foreach(['all' => 'الكل', 'name' => 'الاسم', 'newest' => 'الأحدث', 'oldest' => 'الأقدم'] as $key => $label)
                                        <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                                                        <input type="radio" name="sort" value="{{ $key }}" {{ request('sort') == $key ? 'checked' : '' }} class="hidden peer">
                                            <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                <div class="w-2.5 h-2.5 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                            </div>
                                            <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors font-bold">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="border-t border-slate-100"></div>

                                <!-- Category Section -->
                                <div class="space-y-4">
                                    <h3 class="text-[14px] font-black text-[#1C1C1C] leading-none mb-4">فئة الإعلان:</h3>
                                    <div class="grid gap-3">
                                        @foreach(['all' => 'الكل', 'cars' => 'سيارات', 'real_estate' => 'عقارات', 'electronics' => 'الكترونيات'] as $key => $label)
                                        <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                                                        <input type="radio" name="category" value="{{ $key }}" {{ request('category') == $key ? 'checked' : '' }} class="hidden peer">
                                            <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                <div class="w-2.5 h-2.5 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                            </div>
                                            <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors font-bold">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="border-t border-slate-100"></div>

                                <!-- Commission Type Section -->
                                <div class="space-y-4">
                                    <h3 class="text-[14px] font-black text-[#1C1C1C] leading-none mb-4">نوع العمولة:</h3>
                                    <div class="grid gap-3">
                                        @foreach(['all' => 'الكل', 'auctions' => 'مزادات', 'direct_purchase' => 'شراء فوري', 'tenders' => 'مناقصات', 'services' => 'خدمات'] as $key => $label)
                                        <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                                                        <input type="radio" name="type" value="{{ $key }}" {{ ($type ?? 'all') == $key ? 'checked' : '' }} class="hidden peer">
                                            <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                <div class="w-2.5 h-2.5 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                            </div>
                                            <span class="text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors font-bold">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-4 pt-6 border-t border-slate-100">
                                    <a href="{{ route('admin.payments.commissions.index') }}" class="flex-1 bg-slate-200 px-4 py-4 text-[#6F6F6F] rounded-2xl text-[15px] font-black text-center hover:bg-slate-300 transition-all font-bold">إعادة تعيين</a>
                                    <button type="submit" class="flex-[1.5] px-4 py-4 bg-[#1A73E8] text-white rounded-2xl text-[15px] font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95 font-bold">تطبيق</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Search -->
                <form method="GET" action="{{ route('admin.payments.commissions.index') }}" class="relative max-w-md w-72">
                    <input type="text" name="search" value="{{ $search }}" placeholder="ابحث" class="w-full pr-12 pl-4 py-3 border border-slate-200 rounded-xl font-bold text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-slate-400 hover:text-blue-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>

            <a href="{{ route('admin.payments.commissions.create') }}" class="px-6 py-3 bg-[#1A73E8] text-white rounded-xl text-sm font-black flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                إضافة عمولة
            </a>
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
                        <th class="px-4 py-4">نوع العمولة</th>
                        <th class="px-4 py-4">الفئة</th>
                        <th class="px-4 py-4">القيمة / النسبة</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4 text-center">التاريخ</th>
                        <th class="px-4 py-4 rounded-l-2xl text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($commissions as $commission)
                    <tr class="bg-white shadow-sm ring-1 ring-slate-100 hover:bg-slate-50 transition-all rounded-2xl group relative overflow-hidden">
                        <td class="px-6 py-4 rounded-r-2xl text-center" @click.stop>
                            <input type="checkbox" value="{{ $commission->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-center">{{ $loop->iteration + ($commissions->currentPage() - 1) * $commissions->perPage() }}</td>
                        <td class="px-4 py-4">
                            @php
                                $typeLabels = [
                                    'auctions' => 'مزادات',
                                    'direct_purchase' => 'شراء فوري',
                                    'tenders' => 'مناقصات',
                                    'services' => 'خدمات'
                                ];
                            @endphp
                            {{ $typeLabels[$commission->type] ?? $commission->type }}
                        </td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C]">{{ $commission->category->name ?? 'الكل' }}</td>
                        <td class="px-4 py-4 font-black text-[#1C1C1C]">
                            @if($commission->is_percentage)
                                {{ (float)$commission->value }}%
                            @else
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-black">{{ number_format($commission->value) }}</span>
                                    <x-riyal-icon class="w-4 h-4 opacity-70 inline-block" />
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($commission->status === 'active')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[11px] font-black border border-emerald-100 font-bold">نشطة</span>
                            @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[11px] font-black border border-slate-200 font-bold">غير نشطة</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-slate-400 text-xs font-bold font-bold" dir="ltr">
                            {{ $commission->created_at->format('Y/m/d') }}
                        </td>
                        <td class="px-4 py-4 rounded-l-2xl text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.payments.commissions.edit', $commission->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.payments.commissions.destroy', $commission->id) }}" method="POST" class="inline" id="delete-form-{{ $commission->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete({{ $commission->id }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-24 border border-dashed rounded-2xl border-slate-200 bg-slate-50/50">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-lg font-bold font-bold">لا يوجد عمولات مضافة حالياً</p>
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
                <span>{{ $commissions->firstItem() ?? 0 }}-{{ $commissions->lastItem() ?? 0 }} من {{ $commissions->total() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ $commissions->previousPageUrl() }}" 
                       class="p-1.5 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 transition-all {{ $commissions->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <span class="px-3 min-w-[3rem] text-center">{{ $commissions->currentPage() }} / {{ $commissions->lastPage() }}</span>
                    <a href="{{ $commissions->nextPageUrl() }}" 
                       class="p-1.5 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 transition-all {{ !$commissions->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد من الحذف؟',
            text: "لن تتمكن من استرجاع هذه البيانات!",
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




