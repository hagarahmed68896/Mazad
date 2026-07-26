@extends('layouts.admin')

@section('title', 'لوحة التحكم - تَمَّت')

@section('content')
<div id="dashboardChartsData" data-charts="{{ json_encode($charts) }}"></div>
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">

    <!-- Top Title area -->
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-black text-[#1C1C1C]">لوحة التحكم</h2>
    </div>

    <!-- Top Metrics Row -->
    <x-stats-cards :stats="$cardStats" />

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Total Users Bar Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col hidden lg:flex">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C] mb-1">إجمالي المستخدمين</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-[#1A73E8]">{{ number_format($totalUsers) }}</span>
                    </div>
                    <!-- Legend -->
                    <div class="flex items-center gap-4 mt-2 text-xs font-bold text-[#6F6F6F]">
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1A73E8]"></span> الأفراد</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#60A5FA]"></span> الشركات</div>
                    </div>
                </div>
                
                <!-- Modern Period Dropdown -->
                <div x-data="{ open: false }" class="relative z-10">
                    <button @click="open = !open" class="flex items-center justify-between min-w-[110px] gap-2 px-5 py-2 border border-[#1A73E8] rounded-xl text-sm font-bold text-[#1A73E8] bg-white shadow-sm hover:shadow-md transition-all">
                        <span>{{ $period == 'yearly' ? 'سنوي' : ($period == 'monthly' ? 'شهري' : 'أسبوعي') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition.opacity class="absolute left-0 mt-2 w-[130px] bg-white rounded-2xl shadow-[0_10px_30px_-10px_rgba(0,0,0,0.1)] border border-slate-100 p-2 space-y-1" x-cloak>
                        <a href="?period=weekly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'weekly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">أسبوعي</a>
                        <a href="?period=monthly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'monthly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">شهري</a>
                        <a href="?period=yearly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'yearly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">سنوي</a>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 w-full relative min-h-[220px]">
                <canvas id="usersChart"></canvas>
            </div>
        </div>

        <!-- Total Ads Line Chart -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C] mb-1">إجمالي الإعلانات</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-[#1C1C1C]">{{ number_format($totalAds) }}</span>
                    </div>
                    <!-- Legend -->
                    <div class="flex items-center gap-4 mt-2 text-xs font-bold text-[#6F6F6F]">
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1A73E8]"></span> مزادات</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#93C5FD]"></span> شراء فوري</div>
                    </div>
                </div>
                
                <!-- Modern Period Dropdown -->
                <div x-data="{ open: false }" class="relative z-10">
                    <button @click="open = !open" class="flex items-center justify-between min-w-[110px] gap-2 px-5 py-2 border border-[#1A73E8] rounded-xl text-sm font-bold text-[#1A73E8] bg-white shadow-sm hover:shadow-md transition-all">
                        <span>{{ $period == 'yearly' ? 'سنوي' : ($period == 'monthly' ? 'شهري' : 'أسبوعي') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition.opacity class="absolute left-0 mt-2 w-[130px] bg-white rounded-2xl shadow-[0_10px_30px_-10px_rgba(0,0,0,0.1)] border border-slate-100 p-2 space-y-1" x-cloak>
                        <a href="?period=weekly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'weekly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">أسبوعي</a>
                        <a href="?period=monthly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'monthly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">شهري</a>
                        <a href="?period=yearly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'yearly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">سنوي</a>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 w-full relative min-h-[220px]">
                <canvas id="adsLineChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Ad Status Donut Chart -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C] mb-1">حالة الإعلانات</h3>
                </div>
                <!-- Modern Donut Ad Type Dropdown -->
                <div x-data="{ open: false }" class="relative z-10">
                    <button @click="open = !open" class="flex items-center justify-between min-w-[130px] gap-2 px-5 py-2 border border-[#1A73E8] rounded-xl text-sm font-bold text-[#1A73E8] bg-white shadow-sm hover:shadow-md transition-all">
                        <span>{{ request('ad_type') == 'auctions' ? 'المزادات' : (request('ad_type') == 'direct' ? 'الشراء الفوري' : (request('ad_type') == 'tenders' ? 'المناقصات' : 'الكل')) }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition.opacity class="absolute left-0 mt-2 w-[140px] bg-white rounded-2xl shadow-[0_10px_30px_-10px_rgba(0,0,0,0.1)] border border-slate-100 p-2 space-y-1" x-cloak>
                        <a href="?period={{ $period }}&ad_type=all" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ request('ad_type', 'all') == 'all' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">الكل</a>
                        <a href="?period={{ $period }}&ad_type=auctions" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ request('ad_type') == 'auctions' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">المزادات</a>
                        <a href="?period={{ $period }}&ad_type=direct" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ request('ad_type') == 'direct' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">الشراء الفوري</a>
                        <a href="?period={{ $period }}&ad_type=tenders" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ request('ad_type') == 'tenders' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">المناقصات</a>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 w-full relative min-h-[250px] flex items-center justify-center">
                <div class="w-64 h-64 relative">
                    <canvas id="statusChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xs font-bold text-[#6F6F6F]">إجمالي</span>
                        <span class="text-xl font-black text-[#1C1C1C]">{{ array_sum($charts['statusChart']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Custom Legend -->
            <div class="flex items-center justify-center gap-6 mt-4 text-xs font-bold text-[#6F6F6F]">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#10B981]"></span> قائم</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#F59E0B]"></span> قيد المراجعة</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#9CA3AF]"></span> مستقبلي</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#EF4444]"></span> منتهي</div>
            </div>
        </div>

        <!-- Total Revenue Bar Chart -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C] mb-1">إجمالي الإيرادات</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-[#1A73E8]">{{ number_format($totalRevenue) }}</span>
                        <div class="relative flex items-center justify-center">
                            <x-riyal-icon class="w-6 h-6" />
                        </div>
                    </div>
                </div>
                <!-- Modern Period Dropdown -->
                <div x-data="{ open: false }" class="relative z-10">
                    <button @click="open = !open" class="flex items-center justify-between min-w-[110px] gap-2 px-5 py-2 border border-[#1A73E8] rounded-xl text-sm font-bold text-[#1A73E8] bg-white shadow-sm hover:shadow-md transition-all">
                        <span>{{ $period == 'yearly' ? 'سنوي' : ($period == 'monthly' ? 'شهري' : 'أسبوعي') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition.opacity class="absolute left-0 mt-2 w-[130px] bg-white rounded-2xl shadow-[0_10px_30px_-10px_rgba(0,0,0,0.1)] border border-slate-100 p-2 space-y-1" x-cloak>
                        <a href="?period=weekly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'weekly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">أسبوعي</a>
                        <a href="?period=monthly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'monthly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">شهري</a>
                        <a href="?period=yearly&ad_type={{ request('ad_type', 'all') }}" class="block px-4 py-2 text-sm text-right font-bold rounded-lg transition-colors {{ $period == 'yearly' ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#1C1C1C] hover:bg-[#F8FAFC]' }}">سنوي</a>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 w-full relative min-h-[220px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Recent Transactions Table -->
    <div>
        <h3 class="text-xl font-black text-[#1C1C1C] mb-4 mt-6">أحدث المعاملات</h3>
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6">
            <div class="overflow-x-auto min-h-[300px]">
                <table class="w-full text-sm text-right align-middle whitespace-nowrap border-separate border-spacing-y-3">
                    <thead class="text-[#6F6F6F] font-bold text-[13px] bg-[#F8FAFC]">
                        <tr>
                            <th class="px-6 py-4 rounded-r-2xl">#</th>
                            <th class="px-6 py-4">اسم المستخدم</th>
                            <th class="px-6 py-4">نوع الحساب</th>
                            <th class="px-6 py-4">اسم الإعلان</th>
                            <th class="px-6 py-4">نوع الإعلان</th>
                            <th class="px-6 py-4">نوع المعاملة</th>
                            <th class="px-6 py-4">المبلغ</th>
                            <th class="px-6 py-4 rounded-l-2xl">الوقت والتاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="font-semibold text-[#1C1C1C]">
                        @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-50 transition-colors bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl group overflow-hidden">
                            <td class="px-6 py-4 rounded-r-2xl text-[#6F6F6F]">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $tx->user->name ?? 'غير معروف' }}</td>
                            <td class="px-6 py-4">
                                @if($tx->user)
                                    @php
                                        $typeTranslations = [
                                            'individual' => 'فرد',
                                            'company' => 'شركة',
                                            'representative' => 'مندوب',
                                            'partner' => 'شريك'
                                        ];
                                    @endphp
                                    {{ $typeTranslations[$tx->user->account_type] ?? 'فرد' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $tx->ad->title ?? 'إعلان عام' }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-500">{{ $tx->ad->type ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $tx->type ?? 'عملية مالية' }}</td>
                            <td class="px-6 py-4 text-[#1A73E8]">
                                <div class="flex items-center gap-1.5 justify-end">
                                    <span class="font-black">{{ number_format($tx->amount) }}</span>
                                    <div class="relative flex items-center justify-center">
                                        <x-riyal-icon class="w-4 h-4" />
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[#6F6F6F] text-xs font-bold rounded-l-2xl" dir="ltr">{{ $tx->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-[#6F6F6F] font-bold bg-white rounded-2xl">لا توجد معاملات حديثة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.initDashboardCharts = function() {
        const dataEl = document.getElementById('dashboardChartsData');
        if (!dataEl) return;
        const chartVars = JSON.parse(dataEl.getAttribute('data-charts'));

        Chart.defaults.font.family = "'Cairo', sans-serif";
        Chart.defaults.color = "#a1a1aa";
        
        // Destroy existing chart instances first to avoid overlay bugs
        const ids = ['usersChart', 'adsLineChart', 'statusChart', 'revenueChart'];
        ids.forEach(id => {
            const chartStatus = Chart.getChart(id);
            if (chartStatus) chartStatus.destroy();
        });

        // 1. Users Bar Chart
        const usersEl = document.getElementById('usersChart');
        if (usersEl) {
            new Chart(usersEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartVars.usersChart.labels,
                    datasets: [
                        {
                            label: 'الأفراد',
                            data: chartVars.usersChart.individuals,
                            backgroundColor: '#1A73E8',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.4
                        },
                        {
                            label: 'الشركات',
                            data: chartVars.usersChart.companies,
                            backgroundColor: '#93C5FD',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, border: { display: false }, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        x: { border: { display: false }, grid: { display: false } }
                    }
                }
            });
        }

        // 2. Ads Line Chart
        const adsEl = document.getElementById('adsLineChart');
        if (adsEl) {
            new Chart(adsEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartVars.adsChart.labels,
                    datasets: [
                        {
                            label: 'مزادات',
                            data: chartVars.adsChart.auctions,
                            borderColor: '#1A73E8',
                            backgroundColor: '#1A73E8',
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#1A73E8'
                        },
                        {
                            label: 'شراء فوري',
                            data: chartVars.adsChart.direct_purchase,
                            borderColor: '#93C5FD',
                            backgroundColor: '#93C5FD',
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#93C5FD'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, border: { display: false }, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        x: { border: { display: false }, grid: { display: false } }
                    }
                }
            });
        }

        // 3. Ad Status Donut Chart
        const statusEl = document.getElementById('statusChart');
        if (statusEl) {
            new Chart(statusEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['قائم', 'قيد المراجعة', 'مستقبلي', 'منتهي'],
                    datasets: [{
                        data: [
                            chartVars.statusChart.active, 
                            chartVars.statusChart.pending, 
                            chartVars.statusChart.upcoming, 
                            chartVars.statusChart.finished
                        ],
                        backgroundColor: ['#10B981', '#F59E0B', '#9CA3AF', '#EF4444'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 4. Revenue Bar Chart
        const revEl = document.getElementById('revenueChart');
        if (revEl) {
            new Chart(revEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartVars.revenueChart.labels,
                    datasets: [{
                        data: chartVars.revenueChart.revenue,
                        backgroundColor: '#BFDBFE',
                        hoverBackgroundColor: '#1A73E8',
                        borderRadius: 4,
                        barPercentage: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, border: { display: false }, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        x: { border: { display: false }, grid: { display: false } }
                    }
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.initDashboardCharts();
    });
</script>
@endpush
@endsection

