@extends('layouts.admin')

@section('title', $item->name)

@section('content')
<div x-data="{ 
    activeTab: 'overview',
    adSubTab: 'electronic_auction',
    searchQuery: '',
    orderSubTab: 'all',
    chartType: '{{ $chartType }}',
    performanceData: @js($performanceData),
    chart: null,
    updateChart(type) {
        this.chartType = type;
        const chartRoute = `{{ $item->account_type === 'company' ? route('admin.accounts.companies.show', $item->id) : route('admin.accounts.individuals.show', $item->id) }}`;
        fetch(`${chartRoute}?chart_type=${type}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            this.performanceData = data.performanceData;
            this.renderChart();
        });
    },
    renderChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        if (this.chart) this.chart.destroy();
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.performanceData.map(d => d.label),
                datasets: [
                    {
                        label: 'مزادات',
                        data: this.performanceData.map(d => d.count * 0.45),
                        borderColor: '#1A73E8',
                        backgroundColor: 'rgba(26, 115, 232, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'شراء فوري',
                        data: this.performanceData.map(d => d.count * 0.7),
                        borderColor: '#60A5FA',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(241, 245, 249, 0.5)', borderDash: [8, 4] },
                        ticks: { font: { weight: 'bold', family: 'Cairo' }, padding: 10 }
                    },
                    x: { grid: { display: false }, ticks: { font: { weight: 'bold', family: 'Cairo' }, padding: 10 } }
                }
            }
        });
    }
}" x-init="renderChart" class="space-y-6">

    <!-- Header & Tabs -->
    <div class="flex flex-col gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ $item->account_type === 'company' ? route('admin.accounts.companies') : ($item->account_type === 'partner' ? route('admin.accounts.associates') : ($item->account_type === 'representative' ? route('admin.accounts.partners') : route('admin.accounts.individuals'))) }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 hover:text-[#1A73E8] transition-all border border-slate-100 shadow-sm">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
</svg>            </a>
            <h2 class="text-3xl font-black text-[#1C1C1C]">{{ $item->account_type === 'company' ? ($item->company_name ?? $item->name) : $item->name }}</h2>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
            @php
                $tabs = [
                    'overview' => 'نظرة عامة',
                    'ads' => 'الإعلانات',
                ];
                if ($item->account_type !== 'partner') {
                    $tabs['orders'] = 'المشتريات';
                    $tabs['payments'] = 'المدفوعات';
                }
                $tabs['settlements'] = 'التسوية المالية';
            @endphp
            @foreach($tabs as $key => $label)
            <button @click="activeTab = '{{ $key }}'" 
                :class="activeTab === '{{ $key }}' ? 'bg-[#1A73E8] text-white shadow-lg shadow-[#1A73E8]/20' : 'bg-white text-[#6F6F6F] border border-slate-100'"
                class="px-8 py-3 rounded-xl text-lg font-black transition-all whitespace-nowrap min-w-[140px]">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- Sidebar (Profile Card) -->
        <div class="lg:col-span-4" x-show="activeTab === 'overview'" x-transition.opacity x-cloak>
            <div class="bg-white rounded-[2.5rem] shadow-[0_15px_50px_-15px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
                <div class="h-40 bg-[#1A73E8] relative flex justify-center pt-8">
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                </div>

                <div class="px-8 pb-6 flex flex-col items-center -mt-16 relative">
                    <div class="w-32 h-32 rounded-full bg-white p-2 shadow-xl ring-1 ring-slate-100 mb-6 transition-transform hover:scale-105 duration-500">
                        <div class="w-full h-full rounded-full bg-slate-50 flex items-center justify-center text-4xl font-black text-[#1A73E8]">
                            {{ mb_substr($item->name, 0, 1) }}
                        </div>
                    </div>

                    <div class="w-full space-y-2">
                        @php
                            $details = [
                                ['label' => 'الاسم:', 'value' => $item->name, 'icon' => 'user'],
                            ];
                            
                            if($item->account_type === 'company') {
                                array_push($details, ['label' => 'اسم الشركة:', 'value' => $item->company_name ?? '-', 'icon' => 'bank']);
                                array_push($details, ['label' => 'السجل التجاري:', 'value' => $item->commercial_register ?? '-', 'icon' => 'shield']);
                            } elseif($item->account_type === 'representative') {
                                array_push($details, ['label' => 'اسم الشركة:', 'value' => $item->company_name ?? '-', 'icon' => 'bank']);
                            }

                            $commonDetails = [
                                ['label' => 'رقم الجوال:', 'value' => ltrim($item->phone, '0'), 'icon' => 'phone', 'ltr' => true],
                                ['label' => 'البريد الالكتروني:', 'value' => $item->email, 'icon' => 'mail'],
                                ['label' => 'العنوان:', 'value' => $item->address ?? '-', 'icon' => 'map-pin'],
                            ];

                            if($item->account_type === 'partner') {
                                array_push($commonDetails, ['label' => 'الخدمات:', 'value' => $item->services()->count(), 'icon' => 'hammer']);
                            } else {
                                array_push($commonDetails, ['label' => 'عدد الاعلانات:', 'value' => $stats['total_ads'], 'icon' => 'hammer']);
                                array_push($commonDetails, ['label' => 'المبيعات:', 'value' => $stats['total_sales'], 'icon' => 'bank']);
                                array_push($commonDetails, ['label' => 'المشتريات:', 'value' => $stats['total_purchases'], 'icon' => 'cart']);
                            }

                            $commonDetails = array_merge($commonDetails, [
                                ['label' => 'الحالة:', 'value' => 'نشط', 'icon' => 'shield', 'status' => 'active'],
                                ['label' => 'التاريخ:', 'value' => $item->created_at->format('Y-m-d'), 'icon' => 'calendar']
                            ]);

                            $details = array_merge($details, $commonDetails);
                        @endphp
                        @foreach($details as $detail)
                        <div class="flex items-center justify-between group py-0.5">
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-[#1A73E8] group-hover:bg-[#1A73E8] group-hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <span class="text-[11px] font-black text-slate-400">{{ $detail['label'] }}</span>
                            </div>
                            <span class="text-xs font-black text-slate-700 text-left leading-relaxed flex-1 ml-4 {{ isset($detail['ltr']) ? 'dir-ltr' : '' }}">{{ $detail['value'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="w-full mt-4 space-y-2">
                        <form action="{{ $item->account_type === 'company' ? route('admin.accounts.companies.toggle-block', $item->id) : route('admin.accounts.individuals.toggle-block', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-4 border-2 {{ $item->status == 'active' ? 'border-rose-500 text-rose-500' : 'border-emerald-500 text-emerald-500' }} rounded-2xl font-black text-xs hover:scale-[1.02] transition-all">
                                {{ $item->status == 'active' ? 'حظر المستخدم' : 'إلغاء الحظر' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div :class="activeTab === 'overview' ? 'lg:col-span-8' : 'lg:col-span-12'" class="col-span-1 flex flex-col gap-6">
            
            <div x-show="activeTab === 'overview'" class="flex flex-col gap-6" x-transition>
                <!-- Stats Cards Grid -->
                @if($item->account_type !== 'partner')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
                    @php
                        $gridStats = [
                            ['label' => 'عدد الإعلانات', 'value' => $stats['total_ads'], 'key' => 'ads', 'icon' => 'hammer', 'growth' => $stats['growths']['ads']],
                            ['label' => 'عدد المبيعات', 'value' => $stats['total_sales'], 'key' => 'sales', 'icon' => 'bank', 'growth' => $stats['growths']['sales']],
                            ['label' => 'إجمالي الإيرادات', 'value' => number_format($stats['total_revenue']), 'suffix' => '', 'key' => 'revenue', 'icon' => 'wallet', 'growth' => $stats['growths']['revenue']],
                            ['label' => 'عدد المشتريات', 'value' => $stats['total_purchases'], 'key' => 'purchases', 'icon' => 'cart', 'growth' => $stats['growths']['purchases']]
                        ];
                    @endphp
                    @foreach($gridStats as $stat)
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-4 relative z-10">
                            <span class="text-xs lg:text-sm font-black text-[#6F6F6F]">{{ $stat['label'] }}</span>
                            <div class="w-9 h-9 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                @if($stat['icon'] == 'hammer') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 12h12l3-12H3z"></path></svg>
                                @elseif($stat['icon'] == 'bank') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 10v11M19 10v11M12 10v11"></path></svg>
                                @elseif($stat['icon'] == 'cart') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                @else <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3 relative z-10 text-right">
                             <h3 class="text-2xl lg:text-3xl font-black text-[#1C1C1C]">{{ $stat['value'] }}</h3>
                             @isset($stat['suffix'])<span class="text-xs font-bold text-[#6F6F6F] ml-auto">{{ $stat['suffix'] }}</span>@endisset
                             <div class="px-2 py-0.5 {{ $stat['growth'] >= 0 ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }} rounded-full text-[10px] font-black flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 transition-transform {{ $stat['growth'] < 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 15l7-7 7 7"></path></svg>
                                {{ abs($stat['growth']) }}%
                            </div>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 pointer-events-none opacity-20 h-16">
                            <canvas id="sparkline-{{ $stat['key'] }}" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                <!-- Performance Chart -->
                <div class="bg-white p-8 mt-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col gap-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-xl font-black text-[#1C1C1C]">أداء المستخدم</h3>
                            <div class="px-2 py-0.5 {{ ($stats['growths']['sales'] + $stats['growths']['purchases']) >= 0 ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }} rounded-full text-[10px] font-black flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 {{ ($stats['growths']['sales'] + $stats['growths']['purchases']) < 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 15l7-7 7 7"></path></svg>
                                {{ abs(round(($stats['growths']['sales'] + $stats['growths']['purchases']) / 2, 1)) }}%
                            </div>
                            <span class="text-2xl font-black text-[#1C1C1C]">{{ $stats['performance_score'] }}%</span>
                        </div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-[#6F6F6F]">
                                <span>أسبوعي</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-32 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 text-right">
                                <button @click="updateChart('weekly'); open = false" class="w-full text-right px-4 py-2 text-xs font-bold hover:bg-slate-50 rounded-lg">أسبوعي</button>
                                <button @click="updateChart('monthly'); open = false" class="w-full text-right px-4 py-2 text-xs font-bold hover:bg-slate-50 rounded-lg">شهري</button>
                                <button @click="updateChart('yearly'); open = false" class="w-full text-right px-4 py-2 text-xs font-bold hover:bg-slate-50 rounded-lg">سنوي</button>
                            </div>
                        </div>
                    </div>
                    <div class="h-[300px] w-full relative">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>

            <div x-show="activeTab !== 'overview'" class="flex flex-col gap-6 w-full" x-transition.opacity x-cloak>
                        <!-- Orders Tab -->
                        <div x-show="activeTab === 'orders'" class="bg-white rounded-2xl p-6 lg:p-8 shadow-sm">
                            <div class="flex items-center justify-end mb-6">
                                <div class="relative w-full md:w-80">
                                    <input type="text" x-model="searchQuery" placeholder="ابحث" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] bg-slate-50 transition-all font-bold">
                                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full whitespace-nowrap border-separate border-spacing-y-2">
                                    <thead class="text-[#6F6F6F] font-black text-[13px] bg-[#F8FAFC]">
                                        <tr>
                                            <th class="py-4 px-4 w-10 text-center rounded-r-xl">
                                                <input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                                            </th>
                                            <th class="py-4 px-4 w-10 text-center">#</th>
                                            <th class="py-4 px-4 text-center">رقم الطلب</th>
                                            <th class="py-4 px-4 text-center">اسم الإعلان</th>
                                            <th class="py-4 px-4 text-center">فئة الإعلان</th>
                                            <th class="py-4 px-4 text-center">اسم المستخدم</th>
                                            <th class="py-4 px-4 text-center">السعر النهائي</th>
                                            <th class="py-4 px-4 text-center">مدة الترسية</th>
                                            <th class="py-4 px-4 text-center">الحالة</th>
                                            <th class="py-4 px-4 text-center">التاريخ</th>
                                            <th class="py-4 px-4 text-center rounded-l-xl">كود التسليم</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-bold text-[#6F6F6F] text-[13px]">
                                        @forelse($orders->filter(fn($o) => $o->buyer_id == $item->id) as $index => $order)
                                        <tr x-show="searchQuery === '' || '{{ $order->order_number ?? '' }}'.includes(searchQuery) || '{{ $order->ad->title ?? '' }}'.includes(searchQuery)" class="hover:bg-slate-50 transition-all border border-slate-50 rounded-xl group relative ring-1 ring-transparent hover:ring-[#1A73E8]/10 hover:shadow-sm">
                                            <td class="py-3 px-4 text-center rounded-r-xl bg-white group-hover:bg-blue-50/30 border-y border-r border-slate-100"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer"></td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">#{{ $order->order_number }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">{{ $order->ad->title ?? 'اسم الاعلان' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $order->ad->category->name ?? 'سيارات' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $order->seller->name ?? 'أحمد محمد' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span>{{ number_format($order->final_price, 0) }}</span>
                                                    <x-riyal-icon />
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $order->award_duration ?? '-' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">
                                                @if($order->status == 'received')
                                                    <span class="px-3 py-1.5 bg-[#EFF6FF] text-[#1A73E8] rounded-full text-[11px] font-black tracking-wide">تم الاستلام</span>
                                                @elseif($order->status == 'receiving')
                                                    <span class="px-3 py-1.5 bg-[#FFFBEB] text-[#F59E0B] rounded-full text-[11px] font-black tracking-wide">قيد الاستلام</span>
                                                @elseif($order->status == 'pending_payment')
                                                    <span class="px-3 py-1.5 bg-[#F8FAFC] text-[#94A3B8] rounded-full text-[11px] font-black tracking-wide">قيد الدفع</span>
                                                @else
                                                    <span class="px-3 py-1.5 bg-[#F0FDF4] text-[#22C55E] rounded-full text-[11px] font-black tracking-wide">تم الدفع</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100" dir="ltr">{{ $order->created_at->format('Y/m/d') }}</td>
                                            <td class="py-3 px-4 text-center rounded-l-xl bg-white group-hover:bg-blue-50/30 border-y border-l border-slate-100">
                                                @if($order->status == 'received' || $order->status == 'paid')
                                                <button onclick="sendDeliveryCode({{ $order->id }}, this)" class="bg-[#1A73E8] hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm flex items-center justify-center min-w-[80px]">إرسال الكود</button>
                                                @else
                                                <span class="px-3 py-1.5 bg-[#F1F5F9] text-[#64748B] rounded font-bold text-xs tracking-wider">#{{ $order->delivery_code ?? '839201' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-10 text-slate-400 font-bold">لا توجد مشتريات مطابقة</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div x-show="activeTab === 'payments'" class="bg-white rounded-2xl p-6 lg:p-8 shadow-sm">
                            <div class="flex items-center justify-end mb-6">
                                <div class="relative w-full md:w-80">
                                    <input type="text" x-model="searchQuery" placeholder="ابحث" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] bg-slate-50 transition-all font-bold">
                                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full whitespace-nowrap border-separate border-spacing-y-2">
                                    <thead class="text-[#6F6F6F] font-black text-[13px] bg-[#F8FAFC]">
                                        <tr>
                                            <th class="py-4 px-4 w-10 text-center rounded-r-xl">
                                                <input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                                            </th>
                                            <th class="py-4 px-4 w-10 text-center">#</th>
                                            <th class="py-4 px-4 text-center">رقم العملية</th>
                                            <th class="py-4 px-4 text-center">نوع العملية</th>
                                            <th class="py-4 px-4 text-center">القيمة</th>
                                            <th class="py-4 px-4 text-center">طريقة الدفع</th>
                                            <th class="py-4 px-4 text-center">الحالة</th>
                                            <th class="py-4 px-4 text-center rounded-l-xl">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-bold text-[#6F6F6F] text-[13px]">
                                        @forelse($invoices as $index => $payment)
                                        <tr x-show="searchQuery === '' || '{{ $payment->transaction_number ?? '' }}'.includes(searchQuery)" class="hover:bg-slate-50 transition-all border border-slate-50 rounded-xl group relative ring-1 ring-transparent hover:ring-[#1A73E8]/10 hover:shadow-sm">
                                            <td class="py-3 px-4 text-center rounded-r-xl bg-white group-hover:bg-blue-50/30 border-y border-r border-slate-100"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer"></td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">#{{ $payment->transaction_number }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $payment->type ?? 'عربون' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span>{{ number_format($payment->amount, 0) }}</span>
                                                    <x-riyal-icon />
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $payment->payment_method ?? 'طريقة الدفع' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">
                                                @if($payment->status == 'completed')
                                                    <span class="px-5 py-1.5 bg-[#F0FDF4] text-[#22C55E] rounded-full text-[11px] font-black tracking-wide">مكتملة</span>
                                                @elseif($payment->status == 'under_review')
                                                    <span class="px-5 py-1.5 bg-[#F1F5F9] text-[#64748B] rounded-full text-[11px] font-black tracking-wide">قيد المراجعة</span>
                                                @else
                                                    <span class="px-5 py-1.5 bg-[#FEF2F2] text-[#EF4444] rounded-full text-[11px] font-black tracking-wide">غير مكتملة</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center rounded-l-xl bg-white group-hover:bg-blue-50/30 border-y border-l border-slate-100" dir="ltr">{{ $payment->created_at->format('Y/m/d') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-10 text-slate-400 font-bold">لا توجد مدفوعات مطابقة</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Settlements Tab -->
                        @php
                            $settlements = \App\Models\FinancialSettlement::where('user_id', $item->id)->get();
                        @endphp
                        <div x-show="activeTab === 'settlements'" class="bg-white rounded-2xl p-6 lg:p-8 shadow-sm">
                            <div class="flex items-center justify-end mb-6">
                                <div class="relative w-full md:w-80">
                                    <input type="text" x-model="searchQuery" placeholder="ابحث" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/20 focus:border-[#1A73E8] bg-slate-50 transition-all font-bold">
                                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full whitespace-nowrap border-separate border-spacing-y-2">
                                    <thead class="text-[#6F6F6F] font-black text-[13px] bg-[#F8FAFC]">
                                        <tr>
                                            <th class="py-4 px-4 w-10 text-center rounded-r-xl">
                                                <input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                                            </th>
                                            <th class="py-4 px-4 w-10 text-center">#</th>
                                            <th class="py-4 px-4 text-center">رقم التسوية</th>
                                            <th class="py-4 px-4 text-center">{{ $item->account_type === 'partner' ? 'اسم الخدمة' : 'اسم الإعلان' }}</th>
                                            <th class="py-4 px-4 text-center">إجمالي القيمة</th>
                                            <th class="py-4 px-4 text-center">طريقة الدفع</th>
                                            <th class="py-4 px-4 text-center">الحالة</th>
                                            <th class="py-4 px-4 text-center">التاريخ</th>
                                            <th class="py-4 px-4 text-center rounded-l-xl">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-bold text-[#6F6F6F] text-[13px]">
                                        @forelse($settlements as $index => $settlement)
                                        <tr x-show="searchQuery === '' || '{{ $settlement->settlement_number ?? '' }}'.includes(searchQuery)" class="hover:bg-slate-50 transition-all border border-slate-50 rounded-xl group relative ring-1 ring-transparent hover:ring-[#1A73E8]/10 hover:shadow-sm">
                                            <td class="py-3 px-4 text-center rounded-r-xl bg-white group-hover:bg-blue-50/30 border-y border-r border-slate-100"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer"></td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">#{{ $settlement->settlement_number }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">{{ $item->account_type === 'partner' ? ($settlement->service->title ?? 'اسم الخدمة') : ($settlement->ad->title ?? 'اسم الإعلان') }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span>{{ number_format($settlement->total_amount, 0) }}</span>
                                                    <x-riyal-icon />
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $settlement->payment_method ?? 'طريقة الدفع' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">
                                                @if($settlement->status == 'transferred')
                                                    <span class="px-5 py-1.5 bg-[#F0FDF4] text-[#22C55E] rounded-full text-[11px] font-black tracking-wide">محولة</span>
                                                @else
                                                    <span class="px-5 py-1.5 bg-[#F1F5F9] text-[#64748B] rounded-full text-[11px] font-black tracking-wide">معلقة</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100" dir="ltr">{{ $settlement->created_at->format('Y/m/d') }}</td>
                                            <td class="py-3 px-4 text-center rounded-l-xl bg-white group-hover:bg-blue-50/30 border-y border-l border-slate-100">
                                                <button onclick="transferSettlement({{ $settlement->id }}, this)" class="w-8 h-8 flex items-center justify-center mx-auto rounded-lg text-slate-400 hover:text-[#1A73E8] hover:bg-blue-50 transition-all border border-slate-200 hover:border-blue-200 shadow-sm" title="تحويل التسوية">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-10 text-slate-400 font-bold">لا توجد تسويات مالية مطابقة</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                 
                 <!-- Ads Tab View -->
                 <template x-if="activeTab === 'ads'">
                    @if($item->account_type === 'partner')
                    <div class="bg-white p-4 md:p-6 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6 overflow-hidden animate-in fade-in duration-500">
                        <div class="flex justify-end w-full">
                            <div class="relative w-full md:w-[350px]">
                                <input x-model="searchQuery" type="text" placeholder="ابحث" class="w-full h-12 pr-12 pl-4 rounded-xl border border-slate-200 bg-white text-sm font-bold shadow-sm focus:border-[#1A73E8] outline-none transition-all text-right placeholder-slate-400">
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto min-h-[400px]">
                            <table class="w-full text-right text-sm whitespace-nowrap border-separate border-spacing-y-2">
                                <thead class="text-[#6F6F6F] font-black text-[13px] bg-[#F8FAFC]">
                                    <tr>
                                        <th class="py-4 px-4 w-10 text-center rounded-r-xl">
                                            <input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                                        </th>
                                        <th class="py-4 px-4 w-10 text-center">#</th>
                                        <th class="py-4 px-4 text-center">صورة الخدمة</th>
                                        <th class="py-4 px-4 text-center">اسم الخدمة</th>
                                        <th class="py-4 px-4 text-center">فئة الإعلان</th>
                                        <th class="py-4 px-4 text-center">الوصف</th>
                                        <th class="py-4 px-4 text-center">السعر</th>
                                        <th class="py-4 px-4 text-center rounded-l-xl">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody class="font-bold text-[#6F6F6F] text-[13px]">
                                    @forelse($item->services as $index => $service)
                                    <tr x-show="searchQuery === '' || '{{ $service->title }}'.toLowerCase().includes(searchQuery.toLowerCase())" class="hover:bg-slate-50 transition-all border border-slate-50 rounded-xl group relative ring-1 ring-transparent hover:ring-[#1A73E8]/10 hover:shadow-sm">
                                        <td class="py-3 px-4 text-center rounded-r-xl bg-white group-hover:bg-blue-50/30 border-y border-r border-slate-100"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer"></td>
                                        <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100"><div class="flex justify-center w-full"><img src="{{ $service->image_url ?? 'https://ui-avatars.com/api/?name=Service' }}" class="w-12 h-8 rounded border border-slate-100 object-cover shadow-sm bg-slate-100"></div></td>
                                        <td class="py-3 px-4 text-center text-[#1C1C1C] font-black bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $service->title }}</td>
                                        <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#6F6F6F]">{{ $service->category->name ?? 'سيارات' }}</td>
                                        <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#6F6F6F] max-w-[100px] truncate" title="{{ $service->description }}">{{ $service->description ?? 'الوصف' }}</td>
                                        <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span>{{ number_format($service->price, 0) }}</span>
                                                <x-riyal-icon />
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center rounded-l-xl bg-white group-hover:bg-blue-50/30 border-y border-l border-slate-100" dir="ltr">{{ $service->created_at->format('Y/m/d') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10 text-slate-400 font-bold">لا توجد خدمات مضافة للمستخدم</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                     <div class="flex flex-col gap-6 w-full animate-in fade-in duration-500">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-3 overflow-x-auto no-scrollbar pb-1">
                                <button @click="adSubTab = 'electronic_auction'" :class="adSubTab === 'electronic_auction' ? 'bg-[#1A73E8] text-white shadow-[0_4px_15px_rgba(26,115,232,0.25)]' : 'bg-white text-[#6F6F6F] border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl text-[14px] font-black transition-all whitespace-nowrap">للمزادات الالكترونية</button>
                                <button @click="adSubTab = 'hybrid_auction'" :class="adSubTab === 'hybrid_auction' ? 'bg-[#1A73E8] text-white shadow-[0_4px_15px_rgba(26,115,232,0.25)]' : 'bg-white text-[#6F6F6F] border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl text-[14px] font-black transition-all whitespace-nowrap">للمزادات الهجين</button>
                                <button @click="adSubTab = 'direct_purchase'" :class="adSubTab === 'direct_purchase' ? 'bg-[#1A73E8] text-white shadow-[0_4px_15px_rgba(26,115,232,0.25)]' : 'bg-white text-[#6F6F6F] border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl text-[14px] font-black transition-all whitespace-nowrap">شراء فوري</button>
                                <button @click="adSubTab = 'tender'" :class="adSubTab === 'tender' ? 'bg-[#1A73E8] text-white shadow-[0_4px_15px_rgba(26,115,232,0.25)]' : 'bg-white text-[#6F6F6F] border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl text-[14px] font-black transition-all whitespace-nowrap">المناقصات</button>
                            </div>
                        </div>

                        <div class="bg-white p-4 md:p-6 rounded-2xl shadow-[0_8px_30px_-15px_rgba(0,0,0,0.08)] border border-slate-100 flex flex-col gap-6 overflow-hidden">
                            
                            <!-- Searchbar within card -->
                            <div class="flex justify-end w-full">
                                <div class="relative w-full md:w-[350px]">
                                    <input x-model="searchQuery" type="text" placeholder="ابحث" class="w-full h-12 pr-12 pl-4 rounded-xl border border-slate-200 bg-white text-sm font-bold shadow-sm focus:border-[#1A73E8] outline-none transition-all text-right placeholder-slate-400">
                                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 pointer-events-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </span>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <div class="overflow-x-auto min-h-[400px]">
                                <table class="w-full text-right text-sm whitespace-nowrap border-separate border-spacing-y-2">
                                    <thead class="text-[#6F6F6F] font-black text-[13px] bg-[#F8FAFC]">
                                        <tr>
                                            <th class="py-4 px-4 w-10 text-center rounded-r-xl">
                                                <input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer">
                                            </th>
                                            <th class="py-4 px-4 w-10 text-center">#</th>
                                            <th class="py-4 px-4 text-center">صورة الإعلان</th>
                                            <th class="py-4 px-4 text-center">اسم الإعلان</th>
                                            <th class="py-4 px-4 text-center">فئة الإعلان</th>
                                            <th class="py-4 px-4 text-center">الوصف</th>
                                            
                                            <th class="py-4 px-4 text-center">سعر العربون</th>
                                            <th class="py-4 px-4 text-center" x-text="adSubTab === 'direct_purchase' ? 'السعر' : 'السعر الحالي'">السعر الحالي</th>
                                            
                                            <th class="py-4 px-4 text-center" x-text="adSubTab === 'tender' ? 'عدد المناقصات' : (adSubTab === 'direct_purchase' ? 'عدد السومات' : 'عدد المزايدات')">عدد المزايدات</th>
                                            
                                            <th class="py-4 px-4 text-center" x-show="adSubTab === 'hybrid_auction'">حالة المزايدة</th>
                                            
                                            <th class="py-4 px-4 text-center">الحالة</th>
                                            <th class="py-4 px-4 text-center rounded-l-xl">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-bold text-[#6F6F6F] text-[13px]">
                                        @forelse($item->ads as $index => $ad)
                                        <tr x-show="adSubTab === '{{ $ad->type }}' && ('{{ $ad->title }}'.toLowerCase().includes(searchQuery.toLowerCase()))" class="hover:bg-slate-50 transition-all border border-slate-50 rounded-xl group relative ring-1 ring-transparent hover:ring-[#1A73E8]/10 hover:shadow-sm" style="display: none;">
                                            <td class="py-3 px-4 text-center rounded-r-xl bg-white group-hover:bg-blue-50/30 border-y border-r border-slate-100"><input type="checkbox" class="rounded border-slate-300 text-[#1A73E8] focus:ring-[#1A73E8] cursor-pointer"></td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100"><div class="flex justify-center w-full"><img src="{{ $ad->image_url ?? 'https://ui-avatars.com/api/?name=Ad&background=random' }}" class="w-12 h-8 rounded border border-slate-100 object-cover shadow-sm bg-slate-100"></div></td>
                                            <td class="py-3 px-4 text-center text-[#1C1C1C] font-black bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $ad->title }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#6F6F6F]">{{ $ad->category->name ?? '-' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#6F6F6F] max-w-[100px] truncate" title="{{ $ad->description }}">{{ $ad->description ?? 'الوصف' }}</td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span>{{ number_format($ad->deposit_amount, 0) }}</span>
                                                    <x-riyal-icon />
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span>{{ number_format($ad->price, 0) }}</span>
                                                    <x-riyal-icon />
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100 text-[#1C1C1C]">{{ $ad->bids_count }}</td>
                                            
                                            <td x-show="adSubTab === 'hybrid_auction'" class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">{{ $ad->bid_status ?? '-' }}</td>

                                            <td class="py-3 px-4 text-center bg-white group-hover:bg-blue-50/30 border-y border-slate-100">
                                                @if($ad->status == 'upcoming')
                                                    <span class="px-5 py-1.5 bg-[#F1F5F9] text-[#64748B] rounded-full text-[11px] font-black tracking-wide">مستقبلي</span>
                                                @elseif($ad->status == 'finished')
                                                    <span class="px-5 py-1.5 bg-[#FEF2F2] text-[#EF4444] rounded-full text-[11px] font-black tracking-wide">منتهي</span>
                                                @else
                                                    <span class="px-5 py-1.5 bg-[#F0FDF4] text-[#22C55E] rounded-full text-[11px] font-black tracking-wide">قائم</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center rounded-l-xl bg-white group-hover:bg-blue-50/30 border-y border-l border-slate-100" dir="ltr">{{ $ad->created_at->format('Y/m/d') }}</td>
                                        </tr>
                                        <tr class="h-2" x-show="adSubTab === '{{ $ad->type }}' && ('{{ $ad->title }}'.toLowerCase().includes(searchQuery.toLowerCase()))"></tr>
                                        @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-10 text-slate-400 font-bold">لا توجد إعلانات مطابقة</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="flex items-center justify-between border-t border-slate-100 pt-6 mt-4">
                                <div class="flex items-center gap-2">
                                    <button class="w-8 h-8 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                    <span class="text-sm font-bold text-[#6F6F6F] px-2">1/10</span>
                                    <button class="w-8 h-8 bg-[#E2E8F0] text-[#6F6F6F] rounded-lg hover:bg-[#CBD5E1] flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 text-[13px] text-[#94A3B8] font-bold">
                                    <span>الصفوف لكل صفحة:</span>
                                    <div class="flex items-center gap-1 cursor-pointer">
                                        <span class="text-[#6F6F6F]">10</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                    <span class="mr-4 text-[#94A3B8]">1-10 من 97</span>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif
                 </template>
                 

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.transferSettlement = function(id, btn) {
        Swal.fire({
            title: 'تأكيد التحويل',
            text: "هل تريد حقاً تحويل مبلغ هذه التسوية وإكمال الإجراء؟",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#EF4444',
            confirmButtonText: 'نعم، تحويل',
            cancelButtonText: 'تدقيق ومراجعة'
        }).then((result) => {
            if (result.isConfirmed) {
                // Here an AJAX request would be sent
                Swal.fire({
                    title: 'تم التحويل!',
                    text: 'تم تحويل التسوية المالية بنجاح.',
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'موافق'
                });
                
                let row = btn.closest('tr');
                let statusBadge = row.querySelector('td:nth-child(7) span');
                if(statusBadge) {
                    statusBadge.className = 'px-5 py-1.5 bg-[#F0FDF4] text-[#22C55E] rounded-full text-[11px] font-black tracking-wide';
                    statusBadge.innerText = 'محولة';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const stats = @json($stats);
        const initSparkline = (id, data, color) => {
            const canvas = document.getElementById(id);
            if(!canvas) return;
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map((_, i) => i),
                    datasets: [{
                        data: data,
                        borderColor: color,
                        borderWidth: 2,
                        fill: false,
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
        };
        const growths = @json($stats['growths']);
        const getColor = (val) => val >= 0 ? '#10B981' : '#EF4444';

        initSparkline('sparkline-ads', stats.sparklines.ads, getColor(growths.ads));
        initSparkline('sparkline-sales', stats.sparklines.sales, getColor(growths.sales));
        initSparkline('sparkline-purchases', stats.sparklines.purchases, getColor(growths.purchases));
        initSparkline('sparkline-revenue', stats.sparklines.revenue, getColor(growths.revenue));
    });

    function sendDeliveryCode(orderId, btn) {
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        fetch(`{{ url('admin/accounts/orders') }}/${orderId}/send-code`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Replace button with the delivery code badge
                const cell = btn.closest('td');
                cell.innerHTML = `<span class="px-3 py-1.5 bg-[#F1F5F9] text-[#64748B] rounded font-bold text-xs tracking-wider">#${data.delivery_code}</span>`;

                // Show non-blocking toast
                Swal.fire({
                    icon: 'success',
                    title: 'تم الإرسال!',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            } else {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: data.message || 'حدث خطأ ما',
                    confirmButtonText: 'إغلاق'
                });
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = originalContent;
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ ما أثناء الاتصال بالسيرفر',
                confirmButtonText: 'إلغاء'
            });
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .dir-ltr { direction: ltr !important; }
</style>
@endpush
@endsection


