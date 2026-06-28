@extends('layouts.admin')

@section('title', 'إدارة الحسابات - الشركات')

@section('content')
<div x-data="{ 
        selected: [], 
        allIds: {{ json_encode($users->pluck('id')->toArray()) }},
        showBlockModal: false,
        userToBlock: null,
        userNameToBlock: '',
        get isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length },
        toggleAll() {
            if (this.isAllSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.allIds];
            }
        },
        openBlockModal(id, name) {
            this.userToBlock = id;
            this.userNameToBlock = name;
            this.showBlockModal = true;
        },
        submitBulkAction(status) {
            if (this.selected.length === 0) return;
            const form = document.getElementById('bulk-status-form');
            document.getElementById('bulk-action-status').value = status;
            form.submit();
        }
    }" 
    class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-6 relative">

    <!-- Modal Backdrop & Container -->
    <template x-teleport="body">
        <div x-show="showBlockModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div x-show="showBlockModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showBlockModal = false"></div>
            
            <div x-show="showBlockModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-slate-100">
                <div class="flex flex-col items-center text-center gap-6">
                    <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center text-rose-500 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="text-2xl font-black text-slate-800">تأكيد الحظر</h3>
                        <p class="text-slate-500 font-bold leading-relaxed">هل أنت متأكد من رغبتك في حظر الشركة <span class="text-rose-500" x-text="userNameToBlock"></span>؟ لن يتمكنوا من الوصول إلى حسابهم بعد الآن.</p>
                    </div>

                    <div class="flex items-center gap-4 w-full pt-4">
                        <button @click="showBlockModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95">إلغاء</button>
                        <form :action="`{{ url('admin/accounts/companies') }}/${userToBlock}/toggle-block`" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-6 py-4 bg-rose-500 text-white rounded-2xl font-black shadow-xl shadow-rose-500/20 hover:scale-[1.02] active:scale-95 transition-all">نعم، حظر</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Title -->
    <div>
        <h2 class="text-2xl font-black text-[#1C1C1C]">الشركات</h2>
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
                          class="absolute right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 z-50 p-8">
                            <form method="GET" action="{{ route('admin.accounts.companies') }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                
                                <div class="flex flex-col" style="gap: 20px;">
                                    <!-- Sort Section -->
                                    <div class="flex flex-col" style="gap: 10px;">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الترتيب حسب:</h3>
                                        <div class="grid" style="gap: 10px;">
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="sort" value="newest" {{ $sort == 'newest' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">الأحدث</span>
                                            </label>
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="sort" value="oldest" {{ $sort == 'oldest' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">الأقدم</span>
                                            </label>
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="sort" value="name" {{ $sort == 'name' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">الاسم</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Status Section -->
                                    <div class="flex flex-col" style="gap: 20px;">
                                        <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-widest">الحالة:</h3>
                                        <div class="grid" style="gap: 10px;">
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="status" value="all" {{ $status == 'all' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">الكل</span>
                                            </label>
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="status" value="active" {{ $status == 'active' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px]  font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">نشط</span>
                                            </label>
                                            <label class="flex items-center group cursor-pointer transition-all gap-2">
                                                <input type="radio" name="status" value="banned" {{ $status == 'banned' ? 'checked' : '' }} class="hidden peer">
                                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center peer-checked:border-[#1A73E8] peer-checked:bg-[#1A73E8] transition-all duration-300">
                                                    <div class="w-2 h-2 bg-white rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300"></div>
                                                </div>
                                                <span class="mr-2 text-[15px] font-bold text-[#6F6F6F] group-hover:text-[#1A73E8] transition-colors">غير نشط</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-4 pt-6">
                                        <a href="{{ route('admin.accounts.companies') }}" class="flex-1 bg-[#D8D8D8] px-4 py-2 text-[#6F6F6F] rounded-2xl text-[11px] font-black text-center border border-[#D8D8D8]" style="white-space: nowrap;">إعادة تعيين</a>
                                        <button type="submit" class="flex-[1.5] px-4 py-2 bg-[#1A73E8] text-white rounded-2xl text-[11px] font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95" style="white-space: nowrap;">تطبيق</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form method="GET" style="position: relative; max-width: 384px; width: 100%;">
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث بالاسم، اسم الشركة أو السجل التجاري" style="width: 100%; padding-right: 45px; padding-left: 15px; padding-top: 12px; padding-bottom: 12px; border: 1px solid #E2E8F0; border-radius: 12px; font-weight: 600; font-size: 14px; outline: none; background: white;">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-[#94A3B8] hover:text-[#1A73E8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.accounts.companies.download', ['search' => $search, 'sort' => $sort, 'status' => $status]) }}" class="px-6 py-2.5 border border-slate-200 text-[#EAB308] rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-yellow-50 transition-colors">
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 012-2h2a2 2 0 012 2v16M17 21v-8a2 2 0 00-2-2h-3a2 2 0 00-2 2v8M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4M7 21V5a2 2 0 00-2-2H3a2 2 0 00-2 2v16"></path></svg>
                        </div>
                        <span class="text-sm font-black text-[#1C1C1C]"><span x-text="selected.length"></span> محدد</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <button @click="submitBulkAction('banned')" class="p-2.5 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="حظر جماعي">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    </button>
                    <button @click="submitBulkAction('active')" class="p-2.5 text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors" title="إلغاء حظر جماعي">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <button @click="selected = []" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">إلغاء</button>
                </div>

                <!-- Hidden Bulk Action Form -->
                <form id="bulk-status-form" action="{{ route('admin.accounts.companies.bulk-toggle-block') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="status" id="bulk-action-status">
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
                        <th class="px-4 py-4 w-12 cursor-pointer hover:text-[#1A73E8]"># <span class="text-[#1A73E8] ml-1">↑</span></th>
                        <th class="px-4 py-4">الاسم</th>
                        <th class="px-4 py-4">اسم الشركة</th>
                        <th class="px-4 py-4">السجل التجاري</th>
                        <th class="px-4 py-4">رقم الجوال</th>
                        <th class="px-4 py-4">البريد الالكتروني</th>
                        <th class="px-4 py-4 text-center">الاعلانات</th>
                        <th class="px-4 py-4 text-center">المبيعات</th>
                        <th class="px-4 py-4 text-center text-rose-500">المشتريات</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4">التاريخ</th>
                        <th class="px-4 py-4 text-center rounded-l-2xl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($users as $user)
                    <tr :class="selected.includes({{ $user->id }}) ? 'bg-blue-50/50 ring-[#1A73E8]' : 'bg-white shadow-sm ring-slate-100'" 
                        class="hover:bg-slate-50 transition-all rounded-2xl group cursor-pointer overflow-hidden ring-1 relative"
                        @click="window.location.href = '{{ route('admin.accounts.companies.show', $user->id) }}'">
                        <td class="px-6 py-4 rounded-r-2xl" @click.stop>
                            <input type="checkbox" value="{{ $user->id }}" x-model="selected" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                        </td>
                        <td class="px-4 py-4">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $user->name }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $user->company_name ?? '-' }}</td>
                        <td class="px-4 py-4 font-bold text-[#1A73E8]">{{ $user->commercial_register ?? '-' }}</td>
                        <td class="px-4 py-4" dir="ltr">{{ ltrim($user->phone, '0') }}</td>
                        <td class="px-4 py-4">{{ $user->email }}</td>
                        <td class="px-4 py-4 text-center font-bold text-[#1C1C1C]">{{ $user->ads()->count() }}</td>
                        <td class="px-4 py-4 text-center font-bold text-[#1C1C1C]">{{ $user->sales()->count() }}</td>
                        <td class="px-4 py-4 text-center font-bold text-rose-500">{{ $user->purchases()->count() }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($user->status == 'active')
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-bold ring-1 ring-emerald-100">نشط</span>
                            @else
                                <span class="px-2 py-1 bg-rose-50 text-rose-500 rounded-full text-[11px] font-bold ring-1 ring-rose-100">محظور</span>
                            @endif
                        </td>
                        <td class="px-4 py-4" dir="ltr">{{ $user->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-4 text-center rounded-l-2xl" @click.stop>
                            <div class="flex items-center justify-center gap-2">
                                @if($user->status == 'active')
                                <button @click="openBlockModal({{ $user->id }}, '{{ $user->company_name ?? $user->name }}')" class="p-2.5 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-colors" title="حظر">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                </button>
                                @else
                                <form action="{{ route('admin.accounts.companies.toggle-block', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2.5 bg-emerald-50 text-emerald-500 rounded-xl hover:bg-emerald-500 hover:text-white transition-colors" title="إلغاء الحظر">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-16 text-[#6F6F6F] font-bold">لا يوجد شركات مطابقة للبحث.</td>
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
                <span>{{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} من {{ $users->total() }}</span>
                <div class="flex items-center gap-2">
                    <button class="p-1 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @if($users->onFirstPage()) disabled @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <span>{{ $users->currentPage() }}/{{ $users->lastPage() }}</span>
                    <button class="p-1 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 disabled:opacity-50" @if(!$users->hasMorePages()) disabled @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
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

