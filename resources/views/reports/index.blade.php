@extends('layouts.main')

@section('title', 'تقاريري | تمت')

@push('styles')
<style>
    .reports-page {
        background-color: #F8FAFC;
        min-height: 90vh;
        padding: 3rem 0;
        direction: rtl;
        text-align: right;
        font-family: 'Cairo', sans-serif;
    }

    .breadcrumbs-pill {
        background-color: #1A73E8;
        border-radius: 12px;
        color: white !important;
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none !important;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
        transition: all 0.2s ease;
    }

    .breadcrumbs-pill:hover {
        background-color: #1557B0;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(26, 115, 232, 0.25);
    }

    .page-title {
        font-size: 2.25rem;
        font-weight: 900;
        color: #1E293B;
        margin-bottom: 2rem;
    }

    /* Tabs Container */
    .tabs-container {
        display: flex;
        gap: 0.75rem;
        background: #F1F5F9;
        padding: 0.4rem;
        border-radius: 16px;
        width: fit-content;
        margin-bottom: 2.5rem;
    }

    .tab-button {
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        color: #64748B;
        text-decoration: none;
        transition: all 0.25s ease;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .tab-button:hover {
        color: #1E293B;
    }

    .tab-button.active {
        background-color: #1A73E8;
        color: white;
        box-shadow: 0 6px 15px rgba(26, 115, 232, 0.2);
    }

    /* Filters Layout */
    .filters-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Custom Premium Select Dropdown */
    .custom-dropdown-container {
        position: relative;
        min-width: 200px;
    }

    .custom-dropdown-btn {
        width: 100%;
        background: white;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        padding: 0.75rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        color: #1A73E8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    .custom-dropdown-btn:hover {
        border-color: #1A73E8;
    }

    .custom-dropdown-menu {
        position: absolute;
        top: 110%;
        right: 0;
        width: 100%;
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        z-index: 50;
        display: none;
        overflow: hidden;
        padding: 0.5rem 0;
    }

    .custom-dropdown-menu.active {
        display: block;
    }

    .custom-dropdown-item {
        display: block;
        padding: 0.75rem 1.25rem;
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s;
    }

    .custom-dropdown-item:hover {
        background-color: #F8FAFC;
        color: #1A73E8;
    }

    .custom-dropdown-item.active {
        background-color: #E8F1FE;
        color: #1A73E8;
    }

    /* Standard Select */
    .period-select {
        background: white;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        padding: 0.75rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        color: #64748B;
        cursor: pointer;
        outline: none;
        min-width: 120px;
        transition: border-color 0.2s;
    }
    .period-select:focus {
        border-color: #1A73E8;
    }

    /* Stats & Chart Card */
    .chart-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 28px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        margin-bottom: 3rem;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
    }

    .stat-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #475569;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 900;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .stat-trend {
        background-color: #E8F8F0;
        color: #10B981;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.35rem 0.75rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-right: 1rem;
    }

    .stat-trend.down {
        background-color: #FEE2E2;
        color: #EF4444;
    }

    .stat-trend.flat {
        background-color: #F1F5F9;
        color: #64748B;
    }

    /* Table styling */
    .table-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }

    .reports-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .reports-table th {
        background-color: #F8FAFC;
        padding: 1.25rem 1.5rem;
        font-weight: 800;
        font-size: 0.9rem;
        color: #475569;
        border-bottom: 1.5px solid #F1F5F9;
    }

    .reports-table td {
        padding: 1.25rem 1.5rem;
        font-weight: 700;
        font-size: 0.9rem;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }

    .reports-table tr:last-child td {
        border-bottom: none;
    }

    .reports-table tr:hover td {
        background-color: #F8FAFC;
    }

    .ad-thumbnail {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }

    .type-badge {
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-block;
    }
    
    .type-badge.auction-elec { background-color: #FEF08A; color: #854D0E; }
    .type-badge.auction-hybrid { background-color: #FEE2E2; color: #991B1B; }
    .type-badge.direct { background-color: #E0F2FE; color: #075985; }

    .price-amount {
        font-weight: 900;
        color: #1A73E8;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .row-index {
        font-weight: 900;
        color: #94A3B8;
    }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
    }

    .empty-text {
        font-weight: 800;
        font-size: 1.25rem;
        color: #64748B;
        margin-top: 1rem;
    }
</style>
@endpush

@section('content')
<div class="reports-page">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <a href="{{ route('home') }}" class="breadcrumbs-pill">
            <span>الرئيسية</span>
            <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i>
            <span>تقاريري</span>
        </a>

        <!-- Page Header -->
        <h1 class="page-title">تقاريري</h1>

        <!-- Form container to submit on filters change -->
        <form id="reportsForm" action="{{ route('reports.index') }}" method="GET">
            <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
            <input type="hidden" name="type" id="typeInput" value="{{ $type }}">

            <!-- Tab Switcher -->
            <div class="tabs-container">
                <button type="button" onclick="switchTab('sales')" class="tab-button {{ $tab === 'sales' ? 'active' : '' }}">للمبيعات</button>
                <button type="button" onclick="switchTab('purchases')" class="tab-button {{ $tab === 'purchases' ? 'active' : '' }}">للمشتريات</button>
            </div>

            <!-- Filters Bar -->
            <div class="filters-bar">
                <!-- Dropdown Ad Type -->
                <div class="custom-dropdown-container" id="adTypeDropdown">
                    <div class="custom-dropdown-btn" onclick="toggleDropdown()">
                        <span id="selectedTypeText">
                            @if($type === 'electronic_auction') مزاد إلكتروني
                            @elseif($type === 'hybrid_auction') مزاد هجين
                            @elseif($type === 'direct_purchase') شراء فوري
                            @else كل الإعلانات @endif
                        </span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="custom-dropdown-menu" id="dropdownMenu">
                        <a href="#" onclick="selectDropdownType('all')" class="custom-dropdown-item {{ $type === 'all' ? 'active' : '' }}">كل الإعلانات</a>
                        <a href="#" onclick="selectDropdownType('electronic_auction')" class="custom-dropdown-item {{ $type === 'electronic_auction' ? 'active' : '' }}">مزاد إلكتروني</a>
                        <a href="#" onclick="selectDropdownType('hybrid_auction')" class="custom-dropdown-item {{ $type === 'hybrid_auction' ? 'active' : '' }}">مزاد هجين</a>
                        <a href="#" onclick="selectDropdownType('direct_purchase')" class="custom-dropdown-item {{ $type === 'direct_purchase' ? 'active' : '' }}">شراء فوري</a>
                    </div>
                </div>

                <!-- Dropdown Period -->
                <div>
                    <select name="period" class="period-select font-bold" onchange="this.form.submit()">
                        <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>شهري</option>
                        <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>سنوي</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Stats & Chart Card -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    @if($tab === 'sales')
                        <h3 class="stat-title">إجمالي الإيرادات</h3>
                        <div class="stat-value">
                            <span>{{ number_format($totalRevenue) }}</span>
                            <x-riyal-icon class="w-8 h-8 text-slate-800" />
                            @if($trendDirection === 'up')
                                <span class="stat-trend"><i class="fa-solid fa-arrow-up text-xs"></i> {{ $trendPercentage }}%</span>
                            @elseif($trendDirection === 'down')
                                <span class="stat-trend down"><i class="fa-solid fa-arrow-down text-xs"></i> {{ $trendPercentage }}%</span>
                            @else
                                <span class="stat-trend flat"><i class="fa-solid fa-minus text-xs"></i> {{ $trendPercentage }}%</span>
                            @endif
                        </div>
                    @else
                        <h3 class="stat-title">عدد الطلبات</h3>
                        <div class="stat-value">
                            <span>{{ number_format($purchasesCount) }}</span>
                            @if($trendDirection === 'up')
                                <span class="stat-trend"><i class="fa-solid fa-arrow-up text-xs"></i> {{ $trendPercentage }}%</span>
                            @elseif($trendDirection === 'down')
                                <span class="stat-trend down"><i class="fa-solid fa-arrow-down text-xs"></i> {{ $trendPercentage }}%</span>
                            @else
                                <span class="stat-trend flat"><i class="fa-solid fa-minus text-xs"></i> {{ $trendPercentage }}%</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chart Canvas -->
            <div style="position: relative; width: 100%; height: 320px;">
                <canvas id="reportChart"></canvas>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            @if($items->isEmpty())
                <div class="empty-state">
                    <div class="mx-auto flex items-center justify-center text-slate-300 mb-4" style="width: 100px; height: 100px; background: #F8FAFC; border-radius: 50%; border: 4px solid #F1F5F9;">
                        <i class="fa-solid fa-folder-open text-5xl"></i>
                    </div>
                    <h3 class="empty-text">لا توجد بيانات متاحة لهذا التقرير</h3>
                </div>
            @else
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 90px;">صورة الإعلان</th>
                            <th>اسم الإعلان</th>
                            <th>فئة المزاد</th>
                            <th>نوع الإعلان</th>
                            <th>السعر</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            @php
                                $isServiceOrder = ($item instanceof \App\Models\ServiceOrder);
                                if ($isServiceOrder) {
                                    $sAd = $item->service ?? $item->ad;
                                    $title = $sAd->title ?? 'طلب خدمة شريك';
                                    $image = $sAd && $sAd->image ? (Str::startsWith($sAd->image, 'http') ? $sAd->image : '/' . ltrim($sAd->image, '/')) : null;
                                    $price = $item->price;
                                    $date = $item->created_at;
                                    $categoryName = $sAd->category->name ?? 'خدمات الشركاء';
                                    $typeText = 'خدمة شريك';
                                    $typeClass = 'direct';
                                    $url = $sAd ? route('services.show', $sAd->id) : '#';
                                } else {
                                    $ad = $tab === 'sales' ? $item : $item->ad;
                                    $title = $ad->title ?? '-';
                                    $image = $ad && $ad->image ? (Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/')) : null;
                                    $price = $tab === 'sales' ? ($item->orders->first()?->final_price ?? $item->price) : $item->final_price;
                                    $date = $tab === 'sales' ? ($item->orders->first()?->created_at ?? $item->created_at) : $item->created_at;
                                    $categoryName = $ad->category->name ?? 'غير مصنف';
                                    $typeText = $ad ? ($ad->type == 'electronic_auction' ? 'مزاد إلكتروني' : ($ad->type == 'hybrid_auction' ? 'مزاد هجين' : ($ad->type == 'direct_purchase' ? 'شراء فوري' : 'مناقصة'))) : 'غير محدد';
                                    $typeClass = $ad ? ($ad->type == 'electronic_auction' ? 'auction-elec' : ($ad->type == 'hybrid_auction' ? 'auction-hybrid' : 'direct')) : 'direct';
                                    $url = $ad ? (in_array($ad->type, ['electronic_auction', 'hybrid_auction']) ? route('bids.history-page', $ad->id) : route('ads.show', $ad->id)) : '#';
                                }
                            @endphp
                            
                            <tr style="cursor: pointer;" onclick="window.location='{{ $url }}'">
                                <td class="row-index">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    @if($image)
                                        <img src="{{ $image }}" alt="{{ $title }}" class="ad-thumbnail">
                                    @else
                                        <div class="ad-thumbnail flex items-center justify-center bg-slate-100 text-slate-300">
                                            <i class="fa-solid fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $url }}" class="hover:text-blue-600 font-extrabold text-slate-800 transition-colors" style="text-decoration: none;">{{ $title }}</a>
                                </td>
                                <td>{{ $categoryName }}</td>
                                <td>
                                    <span class="type-badge {{ $typeClass }}">
                                        {{ $typeText }}
                                    </span>
                                </td>
                                <td>
                                    <div class="price-amount">
                                        <span>{{ number_format($price) }}</span>
                                        <x-riyal-icon class="w-4 h-4 text-blue-600" />
                                    </div>
                                </td>
                                <td style="color: #64748B; font-size: 0.85rem;">{{ $date ? $date->format('Y/m/d') : '-' }} - {{ $date ? $date->translatedFormat('h:i a') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab switching logic
    function switchTab(tabValue) {
        document.getElementById('tabInput').value = tabValue;
        document.getElementById('reportsForm').submit();
    }

    // Toggle custom type dropdown
    function toggleDropdown() {
        document.getElementById('dropdownMenu').classList.toggle('active');
    }

    // Select type from custom dropdown
    function selectDropdownType(typeValue) {
        document.getElementById('typeInput').value = typeValue;
        document.getElementById('reportsForm').submit();
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('adTypeDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            document.getElementById('dropdownMenu').classList.remove('active');
        }
    });

    // Chart.js implementation matching high fidelity styling
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('reportChart').getContext('2d');
        if (!ctx) return;

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(26, 115, 232, 0.25)');
        gradient.addColorStop(1, 'rgba(26, 115, 232, 0.00)');

        const labels = @json($chartLabels);
        const dataValues = @json($chartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ $tab === "sales" ? "الإيرادات" : "الطلبات" }}',
                    data: dataValues,
                    borderColor: '#1A73E8',
                    borderWidth: 4.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#1A73E8',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        rtl: true,
                        backgroundColor: '#1E293B',
                        padding: 12,
                        titleFont: { size: 13, family: 'Cairo', weight: 'bold' },
                        bodyFont: { size: 14, family: 'Cairo', weight: 'bold' },
                        displayColors: false,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat().format(context.parsed.y);
                                    if ('{{ $tab }}' === 'sales') {
                                        label += ' ر.س';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: '#F1F5F9',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            font: { family: 'Cairo', size: 11, weight: 'bold' },
                            color: '#94A3B8',
                            callback: function(value) {
                                if ('{{ $tab }}' === 'sales') {
                                    return new Intl.NumberFormat().format(value) + ' ر.س';
                                }
                                return value;
                            }
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { family: 'Cairo', size: 12, weight: 'bold' },
                            color: '#94A3B8'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
