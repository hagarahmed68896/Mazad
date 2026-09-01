@extends('layouts.admin')

@section('title', 'التقييمات')

@section('content')
@php
    $types = [
        'individual' => 'فرد',
        'company' => 'شركة',
        'representative' => 'مندوب',
        'partner' => 'شريك'
    ];
    $reviewsMap = [];
    foreach ($reviews as $rev) {
        $reviewsMap[$rev->id] = [
            'id' => $rev->id,
            'reviewer_name' => $rev->reviewer->name ?? '---',
            'reviewer_type' => $types[$rev->reviewer->account_type ?? ''] ?? ($rev->reviewer->account_type ?? 'فرد'),
            'reviewer_phone' => $rev->reviewer->phone ?? null,
            'reviewer_email' => $rev->reviewer->email ?? null,
            'rated_user_name' => $rev->ratedUser->name ?? '---',
            'rated_user_type' => $types[$rev->ratedUser->account_type ?? ''] ?? ($rev->ratedUser->account_type ?? 'فرد'),
            'rated_user_phone' => $rev->ratedUser->phone ?? null,
            'rated_user_email' => $rev->ratedUser->email ?? null,
            'ad_title' => $rev->ad->title ?? '---',
            'ad_id' => $rev->ad_id ?? null,
            'order_id' => $rev->order_id ?? null,
            'rating' => (int) $rev->rating,
            'comment' => $rev->comment ?: 'بدون تعليق مضاف',
            'reply' => $rev->reply ?: null,
            'status' => $rev->status,
            'status_text' => $rev->status == 'positive' ? 'تقييم إيجابي' : 'تقييم سلبي',
            'created_at' => $rev->created_at->format('Y/m/d h:i A'),
            'delete_url' => route('admin.reviews.destroy', $rev->id),
        ];
    }
@endphp

<script>
    function reviewsManager() {
        return {
            selected: [],
            allIds: @json($reviews->pluck('id')->toArray()),
            reviewsData: @json($reviewsMap),
            activeReview: null,
            detailsModalOpen: false,
            deleteModalOpen: false,
            deleteUrl: '',
            isBulkDelete: false,

            get isAllSelected() { 
                return this.selected.length > 0 && this.selected.length === this.allIds.length;
            },
            toggleAll() {
                if (this.isAllSelected) {
                    this.selected = [];
                } else {
                    this.selected = [...this.allIds];
                }
            },
            openDetails(id) {
                this.activeReview = this.reviewsData[id] || null;
                if (this.activeReview) {
                    this.detailsModalOpen = true;
                }
            },
            confirmDeleteSingle(url) {
                this.isBulkDelete = false;
                this.deleteUrl = url;
                this.deleteModalOpen = true;
            },
            confirmDeleteBulk() {
                if (this.selected.length === 0) return;
                this.isBulkDelete = true;
                this.deleteModalOpen = true;
            },
            executeDelete() {
                if (this.isBulkDelete) {
                    this.$refs.bulkDeleteForm.submit();
                } else if (this.deleteUrl) {
                    this.$refs.singleDeleteForm.action = this.deleteUrl;
                    this.$refs.singleDeleteForm.submit();
                }
            }
        };
    }
</script>

<div x-data="reviewsManager()" class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-6 relative">
    
    <!-- Title -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-[#1C1C1C]">التقييمات</h2>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl font-bold text-sm flex items-center gap-3 shadow-sm animate-in fade-in duration-300">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

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
                        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث عن مستخدم، معلن، إعلان أو تعليق" class="w-full pr-12 pl-4 py-3 border border-slate-200 rounded-xl font-bold text-sm outline-none focus:border-[#1A73E8] transition-all bg-white">
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
                
                <div class="flex items-center gap-3">
                    <!-- Bulk Delete Button -->
                    <button @click="confirmDeleteBulk()" class="flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl font-bold text-sm transition-all border border-rose-200 shadow-sm active:scale-95" title="حذف التقييمات المحددة">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>حذف التقييمات المحددة</span>
                    </button>
                    <button @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
                </div>

                <!-- Hidden Bulk Delete Form -->
                <form x-ref="bulkDeleteForm" action="{{ route('admin.reviews.bulk-destroy') }}" method="POST" class="hidden">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="selected[]" :value="id">
                    </template>
                </form>
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
                        <th class="px-4 py-4">اسم الإعلان</th>
                        <th class="px-4 py-4">اسم المعلن</th>
                        <th class="px-4 py-4">نوع المعلن</th>
                        <th class="px-4 py-4 text-center">التقييم</th>
                        <th class="px-4 py-4">التعليق</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4">التاريخ</th>
                        <th class="px-4 py-4 rounded-l-2xl text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($reviews as $review)
                    @php
                        $reviewerType = $types[$review->reviewer->account_type ?? ''] ?? ($review->reviewer->account_type ?? 'فرد');
                        $ratedUserType = $types[$review->ratedUser->account_type ?? ''] ?? ($review->ratedUser->account_type ?? 'فرد');
                    @endphp
                    <tr @click="openDetails({{ $review->id }})"
                        :class="selected.includes({{ $review->id }}) ? 'bg-blue-50/50 ring-2 ring-[#1A73E8]' : 'bg-white shadow-sm ring-1 ring-slate-100'" 
                        class="hover:bg-blue-50/40 hover:shadow-md transition-all rounded-2xl group cursor-pointer overflow-hidden relative">
                        <td class="px-6 py-4 rounded-r-2xl" @click.stop>
                            <input type="checkbox" value="{{ $review->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4 text-center text-slate-400 font-bold">{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black group-hover:text-[#1A73E8] transition-colors">{{ $review->reviewer->name ?? '---' }}</td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">{{ $reviewerType }}</span>
                        </td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-bold max-w-[150px] truncate" title="{{ $review->ad->title ?? '---' }}">{{ $review->ad->title ?? '---' }}</td>
                        <td class="px-4 py-4 text-[#1A73E8] font-black">{{ $review->ratedUser->name ?? '---' }}</td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">{{ $ratedUserType }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-0.5 text-amber-400">
                                @for($i=1; $i<=5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-4 max-w-[200px] truncate font-medium text-slate-600" title="{{ $review->comment }}">
                            {{ $review->comment ?? 'بدون تعليق' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($review->status == 'positive')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-bold ring-1 ring-emerald-100">تقييم إيجابي</span>
                            @else
                                <span class="px-3 py-1 bg-rose-50 text-rose-500 rounded-full text-[11px] font-bold ring-1 ring-rose-100">تقييم سلبي</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-xs font-semibold text-slate-500" dir="ltr">{{ $review->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-4 rounded-l-2xl text-center" @click.stop>
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openDetails({{ $review->id }})" class="p-2 text-slate-400 hover:text-[#1A73E8] hover:bg-blue-50 rounded-xl transition-all" title="عرض التفاصيل كاملة">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button @click="confirmDeleteSingle('{{ route('admin.reviews.destroy', $review->id) }}')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="حذف التقييم">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
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
                <span>إجمالي السجلات:</span>
                <span class="font-black text-[#1C1C1C]">{{ $reviews->total() }}</span>
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

    <!-- REVIEW DETAILS POPUP MODAL (نافذة عرض تفاصيل التقييم والتعليق كاملة) -->
    <div x-show="detailsModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="detailsModalOpen = false"
         @keydown.escape.window="detailsModalOpen = false">
        
        <div class="bg-white w-full max-w-2xl rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 transform transition-all text-right relative my-8"
             x-show="detailsModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-6"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-6">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1A73E8] flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-[#1C1C1C]">تفاصيل التقييم الكاملة</h3>
                        <p class="text-xs font-bold text-slate-400 mt-0.5">معاينة تفاصيل التقييم والتعليق وبيانات الأطراف</p>
                    </div>
                </div>
                <button @click="detailsModalOpen = false" class="p-2.5 text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Body (Detailed content) -->
            <template x-if="activeReview">
                <div class="space-y-6 max-h-[68vh] overflow-y-auto pl-1 pr-1 custom-scrollbar">
                    
                    <!-- Rating Summary Card -->
                    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-50 to-blue-50/40 border border-slate-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1 text-amber-400 text-lg">
                                <template x-for="i in 5" :key="i">
                                    <svg class="w-6 h-6" :class="i <= activeReview.rating ? 'fill-current text-amber-400' : 'fill-current text-slate-200'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </template>
                            </div>
                            <span class="text-base font-black text-[#1C1C1C]" x-text="activeReview.rating + ' من 5 نجوم'"></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span x-show="activeReview.status === 'positive'" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-black ring-1 ring-emerald-100 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                تقييم إيجابي
                            </span>
                            <span x-show="activeReview.status !== 'positive'" class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-xl text-xs font-black ring-1 ring-rose-100 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                تقييم سلبي
                            </span>
                            <span class="text-xs font-bold text-slate-400" x-text="activeReview.created_at"></span>
                        </div>
                    </div>

                    <!-- 2 Column Profile Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Reviewer Box -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                            <div class="flex items-center gap-2 text-slate-400 font-bold text-xs">
                                <svg class="w-4 h-4 text-[#1A73E8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>صاحب التقييم (المقيّم)</span>
                            </div>
                            <div>
                                <p class="text-base font-black text-[#1C1C1C]" x-text="activeReview.reviewer_name"></p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-md" x-text="'نوع الحساب: ' + activeReview.reviewer_type"></span>
                                    <span x-show="activeReview.reviewer_phone" class="px-2 py-0.5 bg-slate-100 rounded-md" x-text="activeReview.reviewer_phone" dir="ltr"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rated User Box -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                            <div class="flex items-center gap-2 text-slate-400 font-bold text-xs">
                                <svg class="w-4 h-4 text-[#1A73E8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>المعلن (المُقيَّم)</span>
                            </div>
                            <div>
                                <p class="text-base font-black text-[#1A73E8]" x-text="activeReview.rated_user_name"></p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-md" x-text="'نوع المعلن: ' + activeReview.rated_user_type"></span>
                                    <span x-show="activeReview.rated_user_phone" class="px-2 py-0.5 bg-slate-100 rounded-md" x-text="activeReview.rated_user_phone" dir="ltr"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ad / Order Info -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 flex flex-wrap items-center justify-between gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400">الإعلان المرتبط:</span>
                            <span class="font-black text-[#1C1C1C]" x-text="activeReview.ad_title"></span>
                        </div>
                        <div x-show="activeReview.order_id" class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400">رقم الطلب:</span>
                            <span class="font-black text-[#1C1C1C]" x-text="'#' + activeReview.order_id"></span>
                        </div>
                    </div>

                    <!-- Full Comment Section -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-black text-slate-600">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <span>نص التعليق / التقييم بالكامل:</span>
                        </div>
                        <div class="p-5 bg-white border-2 border-slate-100 rounded-2xl text-[#1C1C1C] font-bold text-[15px] leading-relaxed shadow-sm whitespace-pre-wrap break-words min-h-[90px] flex items-center">
                            <span x-text="activeReview.comment"></span>
                        </div>
                    </div>

                    <!-- Reply Section if Available -->
                    <div x-show="activeReview.reply" class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-black text-blue-600">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            <span>رد المعلن على التقييم:</span>
                        </div>
                        <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-2xl text-blue-900 font-bold text-sm leading-relaxed whitespace-pre-wrap break-words">
                            <span x-text="activeReview.reply"></span>
                        </div>
                    </div>

                </div>
            </template>

            <!-- Modal Footer Actions -->
            <div class="flex items-center justify-between gap-3 pt-6 border-t border-slate-100 mt-6">
                <button @click="confirmDeleteSingle(activeReview.delete_url); detailsModalOpen = false;" 
                        type="button" 
                        class="px-6 py-3 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl font-black text-sm transition-all flex items-center gap-2 border border-rose-200 active:scale-95 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>حذف هذا التقييم</span>
                </button>

                <button @click="detailsModalOpen = false" 
                        type="button" 
                        class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-black text-sm transition-all active:scale-95">
                    إغلاق
                </button>
            </div>

        </div>
    </div>

    <!-- CUSTOM DELETE CONFIRMATION MODAL (نافذة تأكيد الحذف الموحدة) -->
    <div x-show="deleteModalOpen" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="deleteModalOpen = false"
         @keydown.escape.window="deleteModalOpen = false">
        
        <div class="bg-white w-full max-w-md rounded-3xl p-8 text-center shadow-2xl transform transition-all border border-slate-100"
             x-show="deleteModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-8">
            
            <div class="w-20 h-20 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-rose-500 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>

            <h3 class="text-2xl font-black text-[#1C1C1C] mb-3" x-text="isBulkDelete ? 'تأكيد الحذف الجماعي' : 'تأكيد حذف التقييم'"></h3>
            <p class="text-slate-500 font-bold mb-8 leading-relaxed text-sm" 
               x-text="isBulkDelete ? 'هل أنت متأكد من رغبتك في حذف (' + selected.length + ') تقييمات محددة؟ سيتم حذفها نهائياً ولا يمكن التراجع عن هذا الإجراء.' : 'هل أنت متأكد من حذف هذا التقييم نهائياً؟ لا يمكن التراجع عن هذا الإجراء.'">
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                <button @click="executeDelete()" 
                        type="button" 
                        class="w-full py-3.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-sm transition-all shadow-lg shadow-rose-600/20 active:scale-95">
                    تأكيد الحذف
                </button>
                <button @click="deleteModalOpen = false" 
                        type="button" 
                        class="w-full py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-black text-sm transition-all active:scale-95">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden Single Delete Form -->
    <form x-ref="singleDeleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

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
