@extends('layouts.admin')

@section('title', 'إدارة الحسابات - الأفراد')

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">

    <!-- Title -->
    <div>
        <h2 class="text-2xl font-black text-[#1C1C1C]">الأفراد</h2>
    </div>

    <!-- Top Metrics Row -->
    <x-stats-cards :stats="$cardStats" />

    <!-- Data Table Wrapper -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
        
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Search -->
            <form method="GET" class="relative max-w-sm w-full pt-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="ابحث" class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-[#1A73E8] focus:ring-1 focus:ring-[#1A73E8]">
                <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#1A73E8]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>

            <div class="flex items-center gap-3">
                <button class="px-6 py-2.5 border-2 border-[#EAB308] text-[#EAB308] rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-yellow-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    تحميل
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-sm text-right align-middle whitespace-nowrap border-separate border-spacing-y-3">
                <thead class="text-[#6F6F6F] font-bold text-[13px] bg-[#F8FAFC]">
                    <tr>
                        <th class="px-6 py-4 w-10 rounded-r-2xl"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8]"></th>
                        <th class="px-4 py-4 w-12 cursor-pointer hover:text-[#1A73E8]"># <span class="text-[#1A73E8] ml-1">↑</span></th>
                        <th class="px-4 py-4">اسم المستخدم</th>
                        <th class="px-4 py-4">رقم الجوال</th>
                        <th class="px-4 py-4">البريد الالكتروني</th>
                        <th class="px-4 py-4 text-center">الاعلانات</th>
                        <th class="px-4 py-4 text-center">المبيعات</th>
                        <th class="px-4 py-4 text-center">المشتريات</th>
                        <th class="px-4 py-4 text-center">الحالة</th>
                        <th class="px-4 py-4">التاريخ</th>
                        <th class="px-4 py-4 text-center rounded-l-2xl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="font-semibold text-[#6F6F6F]">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl group cursor-pointer overflow-hidden">
                        <td class="px-6 py-4 rounded-r-2xl"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8]"></td>
                        <td class="px-4 py-4">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td class="px-4 py-4 text-[#1C1C1C] font-black">{{ $user->name }}</td>
                        <td class="px-4 py-4">{{ $user->phone }}</td>
                        <td class="px-4 py-4">{{ $user->email }}</td>
                        <td class="px-4 py-4 text-center font-bold text-[#1C1C1C]">{{ $user->ads_count ?? 0 }}</td>
                        <td class="px-4 py-4 text-center font-bold text-[#1C1C1C]">{{ $user->sales_count ?? 0 }}</td>
                        <td class="px-4 py-4 text-center font-bold text-[#1C1C1C]">{{ $user->purchases_count ?? 0 }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($user->status == 'active')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-bold ring-1 ring-emerald-100">نشط</span>
                            @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[11px] font-bold ring-1 ring-slate-200">غير نشط</span>
                            @endif
                        </td>
                        <td class="px-4 py-4" dir="ltr">{{ $user->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-4 text-center rounded-l-2xl">
                            <button class="p-2.5 bg-slate-100 text-[#1C1C1C] rounded-xl hover:bg-[#1A73E8] hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-16 text-[#6F6F6F] font-bold">لا يوجد أفراد مطابقين للبحث.</td>
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
