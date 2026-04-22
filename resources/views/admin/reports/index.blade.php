@extends('layouts.admin')

@section('title', 'التقارير والتحليلات')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { 
        border-radius: 20px !important; 
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important; 
        border: 1px solid #f1f5f9 !important; 
        font-family: 'Cairo', sans-serif !important; 
        width: 315px !important;
    }
    .flatpickr-months { padding: 8px !important; }
    .flatpickr-innerContainer, .flatpickr-rContainer, .dayContainer, .flatpickr-days { 
        width: 100% !important; 
        min-width: 300px !important;
    }
    .flatpickr-day { border-radius: 10px !important; }
    .flatpickr-day.selected { background: #1A73E8 !important; border-color: #1A73E8 !important; }
    .flatpickr-weekday { font-size: 11px !important; font-weight: 800 !important; color: #94A3B8 !important; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title & Main Filters Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-2">
        <div>
            <h2 class="text-[32px] font-black text-[#1C1C1C]">التقارير والتحليلات</h2>
        </div>
        
        <!-- Premium Filters Row -->
        <div class="flex flex-wrap items-center gap-4">
            
            <!-- 1. Custom Date Range Picker -->
            <div x-data="{ open: false }" class="relative">
                <button id="dateRangePicker" class="h-[52px] bg-white border border-slate-100 rounded-2xl px-6 flex items-center gap-4 text-sm font-black text-slate-600 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-blue-500 transition-all group">
                    <span class="p-1.5 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 text-[#1A73E8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </span>
                    <span class="text-[#1C1C1C]">{{ $startDate }} - {{ $endDate }}</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>

            <!-- 2. User Type Dropdown -->
            <div x-data="{ open: false }" class="relative">
                @php
                    $types = ['all' => 'كل المستخدمين', 'individual' => 'الأفراد', 'company' => 'الشركات', 'representative' => 'المندوبين', 'partner' => 'الشركاء'];
                @endphp
                <button @click="open = !open" class="h-[52px] min-w-[200px] bg-white border border-slate-100 rounded-2xl px-6 flex items-center justify-between gap-4 text-sm font-black text-slate-600 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-blue-500 transition-all group">
                    <span class="text-[#1C1C1C]">{{ $types[$accountType] ?? $accountType }}</span>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak x-transition.scale.origin.top.right class="absolute left-0 mt-3 w-full bg-white rounded-3xl shadow-2xl border border-slate-50 z-50 p-2 overflow-hidden">
                    @foreach($types as $val => $label)
                    <button @click="setQueryParam('account_type', '{{ $val }}')" 
                            class="w-full text-right px-5 py-3.5 rounded-2xl text-sm font-black transition-all {{ $accountType == $val ? 'bg-blue-50 text-[#1A73E8]' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Category Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="h-[52px] min-w-[180px] bg-white border border-slate-100 rounded-2xl px-6 flex items-center justify-between gap-4 text-sm font-black text-slate-600 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] hover:border-blue-500 transition-all group">
                    <span class="text-[#1C1C1C]">
                        @if($categoryId == 'all') كل الفئات
                        @else {{ $categoriesList->firstWhere('id', $categoryId)->name ?? 'فئة غير معروفة' }}
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak x-transition.scale.origin.top.right class="absolute left-0 mt-3 w-72 max-h-80 bg-white rounded-3xl shadow-2xl border border-slate-50 z-50 p-2 overflow-y-auto">
                    <button @click="setQueryParam('category_id', 'all')" 
                            class="w-full text-right px-5 py-3.5 rounded-2xl text-sm font-black transition-all {{ $categoryId == 'all' ? 'bg-blue-50 text-[#1A73E8]' : 'text-slate-600 hover:bg-slate-50' }}">
                        كل الفئات
                    </button>
                    @foreach($categoriesList as $cat)
                    <button @click="setQueryParam('category_id', '{{ $cat->id }}')" 
                            class="w-full text-right px-5 py-3.5 rounded-2xl text-sm font-black transition-all {{ $categoryId == $cat->id ? 'bg-blue-50 text-[#1A73E8]' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- 1. Total Users Trend -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6 relative">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C]">إجمالي المستخدمين</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">+ 8.42%</span>
                        <span class="text-sm font-black text-[#1C1C1C]">10,000</span>
                    </div>
                </div>
                <!-- Legend -->
                <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#1A73E8]"></div> أفراد</div>
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#10B981]"></div> شركات</div>
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#F59E0B]"></div> مندوبين</div>
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#94A3B8]"></div> شركاء</div>
                </div>
            </div>
            <div class="h-[250px] relative">
                <canvas id="userTrendChart"></canvas>
            </div>
        </div>

        <!-- 2. Users by City -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6 relative">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C]">توزيع المستخدمين حسب المدن</h3>
                </div>
            </div>
            <div class="h-[250px] relative">
                <canvas id="userCityChart"></canvas>
            </div>
        </div>

        <!-- 3. Ads Trend -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#1C1C1C]">إجمالي الإعلانات</h3>
                </div>
                <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#1A73E8]"></div> مزادات</div>
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#10B981]"></div> شراء فوري</div>
                    <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#F59E0B]"></div> مناقصات</div>
                </div>
            </div>
            <div class="h-[250px] relative">
                <canvas id="adsTrendChart"></canvas>
            </div>
        </div>

        <!-- 4. Top Categories -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6">
            <h3 class="text-lg font-black text-[#1C1C1C]">فئات الإعلانات</h3>
            <div class="h-[250px] relative">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- 5. Ad Status Donut -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6 relative overflow-visible">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-black text-[#1C1C1C]">حالة الإعلانات</h3>
                <!-- Card Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    @php
                        $stTypes = ['all' => 'الكل', 'auction' => 'مزادات', 'direct' => 'شراء فوري', 'tender' => 'مناقصات'];
                    @endphp
                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-[#1A73E8] rounded-xl text-[11px] font-black group transition-all">
                        <span>{{ $stTypes[$adStatusType] ?? $adStatusType }}</span>
                        <svg class="w-3 h-3 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute left-0 mt-2 w-32 bg-white rounded-2xl shadow-xl border border-slate-50 z-50 p-2 overflow-hidden outline-none">
                        @foreach($stTypes as $val => $label)
                        <button @click="setQueryParam('ad_status_type', '{{ $val }}')" 
                                class="w-full text-right px-4 py-2 rounded-lg text-[11px] font-black transition-all {{ $adStatusType == $val ? 'bg-blue-50 text-blue-600' : 'text-slate-400 hover:bg-slate-50' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center h-[280px] relative">
                <canvas id="adStatusChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pt-2 pointer-events-none">
                    <span class="text-4xl font-black text-[#1C1C1C]">{{ array_sum($adStatuses) }}</span>
                    <span class="text-[10px] font-bold text-slate-400">إجمالي الإعلانات</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-y-3 pt-4 px-4 text-[11px] font-black">
                <div class="flex items-center gap-2 text-slate-500"><div class="w-2 h-2 rounded-full bg-[#10B981]"></div> نشط</div>
                <div class="flex items-center gap-2 text-slate-500"><div class="w-2 h-2 rounded-full bg-[#6366F1]"></div> تحت المراجعة</div>
                <div class="flex items-center gap-2 text-slate-500"><div class="w-2 h-2 rounded-full bg-[#94A3B8]"></div> منتهي</div>
                <div class="flex items-center gap-2 text-slate-500"><div class="w-2 h-2 rounded-full bg-[#F59E0B]"></div> قادم</div>
            </div>
        </div>

        <!-- 6. Rating Gauge -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6 relative overflow-visible">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-black text-[#1C1C1C]">متوسط التقييمات</h3>
                <!-- Card Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    @php
                        $rTypes = ['all' => 'الكل', 'individual' => 'الأفراد', 'partner' => 'الشركاء'];
                    @endphp
                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-[#1A73E8] rounded-xl text-[11px] font-black group transition-all">
                        <span>{{ $rTypes[$ratingsUserType] ?? $ratingsUserType }}</span>
                        <svg class="w-3 h-3 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute left-0 mt-2 w-32 bg-white rounded-2xl shadow-xl border border-slate-50 z-50 p-2 overflow-hidden outline-none">
                        @foreach($rTypes as $val => $label)
                        <button @click="setQueryParam('ratings_user_type', '{{ $val }}')" 
                                class="w-full text-right px-4 py-2 rounded-lg text-[11px] font-black transition-all {{ $ratingsUserType == $val ? 'bg-blue-50 text-blue-600' : 'text-slate-400 hover:bg-slate-50' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-12 pt-4">
                <div class="flex-1 flex flex-col items-center justify-center relative h-[200px]">
                    <canvas id="ratingGaugeChart"></canvas>
                    <div class="absolute bottom-4 flex flex-col items-center">
                        <span class="text-4xl font-black text-[#1C1C1C]">{{ $ratings['percentage'] }}%</span>
                        <span class="text-[10px] font-bold text-slate-400">بناءً على آراء العملاء</span>
                    </div>
                </div>
                <div class="flex-1 space-y-4">
                    <div class="text-center pb-2">
                        <span class="text-5xl font-black text-[#1C1C1C]">{{ $ratings['avg'] }}</span>
                        <div class="flex items-center justify-center gap-0.5 text-amber-400 mt-2">
                            @for($i=1;$i<=5;$i++)
                                <svg class="w-4 h-4 {{ $i <= floor($ratings['avg']) ? 'fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 mt-1">{{ $ratings['total'] }} تقييم</p>
                    </div>
                    <div class="space-y-1.5">
                        @foreach([5, 4, 3, 2, 1] as $star)
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black w-2 text-slate-400">{{ $star }}</span>
                            <div class="flex-1 h-1.5 bg-slate-50 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full" style="width: {{ $ratings['total'] > 0 ? ($ratings['stars'][$star] / $ratings['total']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Purchases by Category -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6 relative">
            <h3 class="text-lg font-black text-[#1C1C1C]">إجمالي المشتريات</h3>
            <div class="h-[250px] relative">
                <canvas id="purchasesChart"></canvas>
            </div>
        </div>

        <!-- 8. Total Revenue -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col gap-6 relative">
            <h3 class="text-lg font-black text-[#1C1C1C]">إجمالي الإيرادات</h3>
            <div class="h-[250px] relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Helper function to update query params safely
function setQueryParam(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Date Range Picker
    flatpickr("#dateRangePicker", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: ["{{ $startDate }}", "{{ $endDate }}"],
        onClose: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const start = instance.formatDate(selectedDates[0], "Y-m-d");
                const end = instance.formatDate(selectedDates[1], "Y-m-d");
                
                const url = new URL(window.location.href);
                url.searchParams.set('start_date', start);
                url.searchParams.set('end_date', end);
                window.location.href = url.toString();
            }
        }
    });

    Chart.defaults.font.family = 'Cairo';
    Chart.defaults.color = '#94A3B8';
    Chart.defaults.font.weight = 'bold';
    Chart.defaults.scale.grid.color = '#F1F5F9';

    // 1. User Trend
    new Chart(document.getElementById('userTrendChart'), {
        type: 'bar',
        data: {
            labels: @json($days),
            datasets: [
                { label: 'أفراد', data: @json($userData['individual']), backgroundColor: '#1A73E8', borderRadius: 6, barThickness: 12 },
                { label: 'شركات', data: @json($userData['company']), backgroundColor: '#10B981', borderRadius: 6, barThickness: 12 },
                { label: 'مندوبين', data: @json($userData['representative']), backgroundColor: '#F59E0B', borderRadius: 6, barThickness: 12 },
                { label: 'شركاء', data: @json($userData['partner']), backgroundColor: '#94A3B8', borderRadius: 6, barThickness: 12 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true } } }
    });

    // 2. City
    new Chart(document.getElementById('userCityChart'), {
        type: 'bar',
        data: {
            labels: @json($cityDistribution->pluck('name')),
            datasets: [{ data: @json($cityDistribution->pluck('count')), backgroundColor: '#F1F5F9', hoverBackgroundColor: '#1A73E8', borderRadius: 8, barThickness: 30 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
    });

    // 3. Ads Trend
    new Chart(document.getElementById('adsTrendChart'), {
        type: 'line',
        data: {
            labels: @json($days),
            datasets: [
                { label: 'مزادات', data: @json($adsTrend['auction']), borderColor: '#1A73E8', tension: 0.4, borderWidth: 3, pointRadius: 0 },
                { label: 'شراء فوري', data: @json($adsTrend['direct']), borderColor: '#10B981', tension: 0.4, borderWidth: 3, pointRadius: 0 },
                { label: 'مناقصات', data: @json($adsTrend['tender']), borderColor: '#F59E0B', tension: 0.4, borderWidth: 3, pointRadius: 0 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
    });

    // 4. Categories
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: @json($topCategories->pluck('name')),
            datasets: [{ data: @json($topCategories->pluck('ads_count')), backgroundColor: ['#1A73E8', '#10B981', '#F59E0B', '#6366F1', '#EC4899', '#94A3B8'], borderRadius: 6, barThickness: 20 }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { grid: { display: false } } } }
    });

    // 5. Ad Status
    new Chart(document.getElementById('adStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['نشط', 'تحت المراجعة', 'منتهي', 'قادم'],
            datasets: [{
                data: [@json($adStatuses['active']), @json($adStatuses['pending']), @json($adStatuses['finished']), @json($adStatuses['upcoming'])],
                backgroundColor: ['#10B981', '#6366F1', '#94A3B8', '#F59E0B'],
                borderWidth: 0, cutout: '85%'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 6. Gauge
    new Chart(document.getElementById('ratingGaugeChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [@json($ratings['percentage']), 100 - @json($ratings['percentage'])],
                backgroundColor: ['#1A73E8', '#F1F5F9'],
                borderWidth: 0, cutout: '88%', circumference: 180, rotation: 270, borderRadius: 5
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 7. Purchases
    new Chart(document.getElementById('purchasesChart'), {
        type: 'bar',
        data: {
            labels: @json($purchasesByCategory->pluck('name')),
            datasets: [{ label: 'تم الدفع', data: @json($purchasesByCategory->pluck('orders_count')), backgroundColor: '#1A73E8', borderRadius: 8, barThickness: 25 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
    });

    // 8. Revenue
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: @json($days),
            datasets: [{ data: @json($revenueData), backgroundColor: '#A5C9FF', hoverBackgroundColor: '#6366F1', borderRadius: 6, barThickness: 25 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
    });
});
</script>
@endpush
@endsection
