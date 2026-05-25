@extends('layouts.main')

@section('title', 'مشترياتي | مزاد')

@push('styles')
<style>
    .my-purchases-page {
        background-color: #ffffff;
        min-height: 80vh;
        padding: 3rem 0;
        direction: rtl;
        text-align: right;
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

    /* Filter Tabs */
    .filter-tabs-container {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
        background: #F1F5F9;
        padding: 0.5rem;
        border-radius: 20px;
        width: fit-content;
    }

    .filter-tab {
        padding: 0.85rem 1.75rem;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.95rem;
        color: #64748B;
        text-decoration: none;
        transition: all 0.25s ease;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .filter-tab:hover {
        color: #1E293B;
        background: rgba(255, 255, 255, 0.5);
    }

    .filter-tab.active {
        background-color: #1A73E8;
        color: white;
        box-shadow: 0 8px 20px rgba(26, 115, 232, 0.2);
    }

    /* Empty State */
    .empty-state-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 32px;
        padding: 5rem 2rem;
        text-align: center;
        max-width: 680px;
        margin: 0 auto;
        box-shadow: 0 20px 40px rgba(0,0,0,0.02);
    }

    /* Sidebar and Filters styling matching search page */
    .filter-card {
        background: #F8FAFC;
        border-radius: 24px;
        border: 1px solid #E2E8F0;
        padding: 1.5rem;
    }
    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1.5px solid #E2E8F0;
        padding-bottom: 1rem;
    }
    .filter-header h3 {
        font-size: 1.15rem;
        font-weight: 900;
        color: #1E293B;
    }
    .reset-btn {
        font-size: 0.85rem;
        font-weight: 700;
        color: #94A3B8;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s;
        text-decoration: none !important;
    }
    .reset-btn:hover { color: #64748B; }

    .filter-group {
        border-bottom: 1.5px solid #E2E8F0;
        padding: 1.25rem 0;
    }
    .filter-group:last-child { border-bottom: none; }
    .filter-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 800;
        font-size: 0.95rem;
        color: #334155;
    }
    .filter-group-content {
        margin-top: 1rem;
        display: block;
    }
    .filter-group-content.hidden { display: none; }

    .filter-select {
        width: 100%;
        padding: 0.85rem 1rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        outline: none;
        background-color: #ffffff;
        cursor: pointer;
    }
    .filter-select:focus { border-color: #1A73E8; }

    .filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .filter-pill-label {
        position: relative;
        cursor: pointer;
    }
    .filter-pill-label input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .filter-pill-label span {
        display: inline-block;
        padding: 0.5rem 1.25rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        color: #64748B;
        transition: all 0.2s;
        background: white;
    }
    .filter-pill-label input:checked + span {
        background-color: #E8F1FE;
        border-color: #1A73E8;
        color: #1A73E8;
    }

    .filter-categories-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .category-filter-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.5rem 0;
    }
    .category-filter-item input {
        display: none;
    }
    .category-filter-item .radio-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #CBD5E1;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        transition: all 0.2s;
        background: white;
    }
    .category-filter-item input:checked + .radio-custom {
        border-color: #1A73E8;
        background-color: #1A73E8;
    }
    .category-filter-item input:checked + .radio-custom::after {
        content: '';
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .category-filter-item .cat-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
    }

    /* Action bar inputs */
    .search-action-bar {
        background: white;
        padding: 1.25rem 1.5rem;
        border-radius: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 2.5rem;
        border: 1.5px solid #F1F5F9;
    }

    .search-bar-input {
        width: 100%;
        height: 48px;
        padding-right: 3rem;
        padding-left: 1rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.95rem;
        color: #1E293B;
        outline: none;
        background-color: #F8FAFC;
    }
    .search-bar-input:focus { border-color: #1A73E8; background-color: white; }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        height: 44px;
        padding: 0 1.25rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        background: white;
        color: #64748B;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn:hover { border-color: #1A73E8; color: #1A73E8; }
    .action-btn.active { background-color: #1A73E8; border-color: #1A73E8; color: white !important; }

    .sort-dropdown-container {
        position: relative;
    }
    .sort-options {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: auto;
        margin-top: 0.5rem;
        background: white;
        border: 1.5px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        z-index: 50;
        min-width: 180px;
        overflow: hidden;
    }
    .sort-dropdown-container.active .sort-options {
        display: block;
    }
    .sort-options a {
        display: block;
        padding: 0.75rem 1.25rem;
        color: #475569;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        text-align: right;
        transition: background 0.2s;
    }
    .sort-options a:hover { background: #F8FAFC; color: #1A73E8; }
    .sort-options a.active { background: #E8F1FE; color: #1A73E8; }

    /* View Toggles style */
    .view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        background: white;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s;
    }
    .view-btn:hover { border-color: #1A73E8; color: #1A73E8; }
    .view-btn.active { background-color: #1A73E8; border-color: #1A73E8; color: white !important; }

    /* Card styling */
    .p-ad-card {
        border: 1px solid #E2E8F0;
        border-radius: 24px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.3s ease;
        height: 100%;
    }
    .p-ad-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    
    .p-card-main-content {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .p-card-image {
        position: relative;
        height: 220px;
        width: 100%;
        overflow: hidden;
    }
    .p-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .p-badges {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        left: 0.75rem;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.4rem;
        pointer-events: none;
    }
    .p-badge-pill {
        padding: 0.35rem 0.85rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.75rem;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    
    /* Custom tag colors matches screenshot */
    .p-badge-pill.status-pending,
    .p-badge-pill.status-receiving { background-color: #F97316 !important; opacity: 1 !important; } /* Orange - قيد الاستلام */
    .p-badge-pill.status-pending-payment { background-color: #EAB308 !important; opacity: 1 !important; } /* Yellow - بانتظار الدفع */
    .p-badge-pill.status-paid { background-color: #22C55E !important; opacity: 1 !important; } /* Green - تم الدفع */
    .p-badge-pill.status-received { background-color: #1A73E8 !important; opacity: 1 !important; } /* Blue - تم الاستلام */
    .p-badge-pill.status-expired { background-color: #EF4444 !important; opacity: 1 !important; } /* Red - منتهي */
    
    .p-badge-pill.type-direct { background-color: #F97316; } /* Orange - شراء فوري */
    .p-badge-pill.type-auction { background-color: #EAB308; } /* Yellow - الكتروني / هجين */

    .p-countdown {
        position: absolute;
        bottom: 0.75rem;
        right: 0.75rem;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .p-card-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .p-category-pill {
        background-color: #E8F1FE;
        color: #1A73E8;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        width: fit-content;
        margin-bottom: 0.75rem;
    }
    .p-ad-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1E293B;
        line-height: 1.4;
        margin-bottom: 0.75rem;
        min-height: 2.8rem;
    }
    .p-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.25rem 0;
    }
    .p-price-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748B;
    }
    .p-price-value {
        color: #1A73E8;
        font-weight: 900;
        font-size: 1.2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    /* User / Buyer details style */
    .buyer-info-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-top: 1.5px solid #F1F5F9;
        margin-top: auto;
    }
    .buyer-avatar-placeholder {
        width: 32px;
        height: 32px;
        background: #1A73E8;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 800;
    }
    .buyer-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
    }

    .p-card-footer {
        padding: 1rem 1.25rem 1.25rem 1.25rem;
        border-top: 1.5px solid #F1F5F9;
        display: flex;
        gap: 0.5rem;
    }

    /* Horizontal/List View layout exactly matching the sales page */
    #myPurchasesContainer.list-view {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
    
    #myPurchasesContainer.list-view .p-ad-card {
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        overflow: hidden !important;
        border-radius: 24px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        border: 1px solid #E2E8F0 !important;
    }

    #myPurchasesContainer.list-view .p-card-main-content {
        display: flex !important;
        flex-direction: row !important; /* Image on right, details on left in RTL */
        align-items: stretch !important;
        min-height: 200px !important;
    }

    #myPurchasesContainer.list-view .p-card-image {
        width: 300px !important;
        height: auto !important;
        flex-shrink: 0 !important;
        position: relative !important;
        border-radius: 0 20px 20px 0 !important;
        overflow: hidden !important;
    }

    #myPurchasesContainer.list-view .p-card-image img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    #myPurchasesContainer.list-view .p-card-body {
        flex: 1 !important;
        padding: 1.5rem !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }

    #myPurchasesContainer.list-view .p-category-pill {
        align-self: flex-start !important;
        margin-bottom: 0.5rem !important;
    }

    #myPurchasesContainer.list-view .p-ad-title {
        font-size: 1.2rem !important;
        margin-bottom: 0.5rem !important;
        min-height: auto !important;
    }

    #myPurchasesContainer.list-view .p-price-row {
        margin-bottom: 0.5rem !important;
    }

    #myPurchasesContainer.list-view .buyer-info-row {
        margin-top: 0.5rem !important;
        padding-top: 0.5rem !important;
    }

    #myPurchasesContainer.list-view .p-card-footer {
        width: 100% !important;
        padding: 0.75rem 1.5rem 1.5rem 1.5rem !important;
        border-top: none !important;
        margin-top: 0 !important;
    }

    @media (max-width: 768px) {
        #myPurchasesContainer.list-view .p-card-main-content {
            flex-direction: column !important;
        }
        #myPurchasesContainer.list-view .p-card-image {
            width: 100% !important;
            height: 200px !important;
            border-radius: 20px 20px 0 0 !important;
        }
    }

    /* Star Rating classes */
    .swal2-stars {
        display: flex;
        justify-content: center;
        gap: 0.4rem;
        font-size: 2.25rem;
        direction: ltr;
        margin: 1rem 0;
    }
    .swal2-star-item {
        color: #CBD5E1;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .swal2-star-item.selected,
    .swal2-star-item:hover,
    .swal2-star-item:hover ~ .swal2-star-item {
        color: #F59E0B;
    }
</style>
@endpush

@section('content')
<div class="my-purchases-page">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <a href="{{ route('home') }}" class="breadcrumbs-pill">
            <span>الرئيسية</span>
            <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i>
            <span>مشترياتي</span>
        </a>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h1 class="page-title !mb-0" style="margin-bottom: 0 !important;">مشترياتي</h1>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs-container">
            <a href="{{ route('purchases.my', ['status' => 'all'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'all' ? 'active' : '' }}">الكل</a>
            <a href="{{ route('purchases.my', ['status' => 'auctions'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'auctions' ? 'active' : '' }}">المزادات الذي فاز بها</a>
            <a href="{{ route('purchases.my', ['status' => 'direct_purchase'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'direct_purchase' ? 'active' : '' }}">الشراء الفوري</a>
        </div>

        <!-- Action / Search Bar -->
        <div class="search-action-bar flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            <!-- Right: Search input & Filter toggle -->
            <div class="flex items-center gap-3 flex-1 max-w-[560px]">
                <!-- Filter Toggle Button (Desktop & Mobile) -->
                <button type="button" class="action-btn active flex-shrink-0" id="filterToggleBtn" onclick="toggleMySidebar()" title="تصفية" style="height: 48px; border-radius: 16px;">
                    <i class="fa-solid fa-sliders"></i>
                </button>

                <form action="{{ route('purchases.my') }}" method="GET" style="position: relative; width: 100%;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    @foreach(request()->except('q', 'page', 'status') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $subK => $subV)
                                        <input type="hidden" name="{{ $key }}[{{ $k }}][{{ $subK }}]" value="{{ $subV }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}">
                                  @endif
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث في مشترياتي..." class="search-bar-input">
                    <i class="fa-solid fa-magnifying-glass search-bar-icon" style="position: absolute; top: 50%; transform: translateY(-50%); right: 1.25rem; left: auto; color: #94A3B8; font-size: 1.1rem;"></i>
                </form>
            </div>

            <!-- Left: Actions group -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Layout Toggles (Grid & List) -->
                <button type="button" class="view-btn active" id="gridViewBtn" onclick="toggleAdsLayout('grid')" title="عرض شبكي">
                    <i class="fa-solid fa-table-cells-large"></i>
                </button>
                <button type="button" class="view-btn" id="listViewBtn" onclick="toggleAdsLayout('list')" title="عرض قائمة">
                    <i class="fa-solid fa-list-ul"></i>
                </button>

                <!-- Order/Sort dropdown -->
                <div class="sort-dropdown-container" id="mySortContainer" onclick="this.classList.toggle('active')">
                    <button class="action-btn">
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                        <span>الترتيب</span>
                    </button>
                    <div class="sort-options">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="{{ request('sort') == 'newest' || !request('sort') ? 'active' : '' }}">الأحدث</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" class="{{ request('sort') == 'price_high' ? 'active' : '' }}">الأعلى سعراً</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" class="{{ request('sort') == 'price_low' ? 'active' : '' }}">الأقل سعراً</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Sidebar + Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Right Pane: Sidebar Filters -->
            <aside class="lg:col-span-1 block" id="mySidebarPane">
                <div class="filter-card">
                    <div class="filter-header">
                        <h3>تصفية النتائج</h3>
                        <a href="{{ route('purchases.my', ['status' => $status]) }}" class="reset-btn">إعادة تعيين</a>
                    </div>

                    <form id="filterForm" action="{{ route('purchases.my') }}" method="GET" class="space-y-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        <!-- الموقع (City) -->
                        <div class="filter-group">
                            <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                <span>الموقع</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="filter-group-content">
                                <select name="city" class="filter-select font-bold" onchange="this.form.submit()">
                                    <option value="">كل المدن</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- نوع الاعلان -->
                        <div class="filter-group">
                            <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                <span>نوع الإعلان</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="filter-group-content">
                                <div class="filter-pills">
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="" {{ !request('type') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>الكل</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="auction" {{ request('type') == 'auction' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>مزادات</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="direct_purchase" {{ request('type') == 'direct_purchase' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>شراء فوري</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- الفئات -->
                        <div class="filter-group">
                            <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                <span>الفئة الرئيسية</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="filter-group-content">
                                <div class="filter-categories-list">
                                    <label class="category-filter-item">
                                        <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="radio-custom"></span>
                                        <span class="cat-name font-bold">كل الفئات</span>
                                    </label>
                                    @foreach($categories as $cat)
                                        <label class="category-filter-item">
                                            <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="radio-custom"></span>
                                            <span class="cat-name font-bold">{{ $cat->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Category Properties -->
                        @if(isset($activeCategory))
                            <div class="active-category-info" style="background: #E8F1FE; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(26, 115, 232, 0.15);">
                                <div style="width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.1rem;">
                                    <i class="fa-solid fa-tags"></i>
                                </div>
                                <div class="flex-1">
                                    <span style="display: block; font-size: 0.75rem; color: #64748B; font-weight: 700;">الفئة المختارة</span>
                                    <span style="display: block; font-size: 1rem; color: #1A73E8; font-weight: 800;">{{ $activeCategory->name }}</span>
                                </div>
                                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'props' => null]) }}" style="color: #EF4444; font-size: 0.9rem;" title="إلغاء الفئة"><i class="fa-solid fa-circle-xmark"></i></a>
                            </div>

                            @if($activeCategory->properties->count() > 0)
                                @foreach($activeCategory->properties->sortBy('sort_order') as $prop)
                                    <div class="filter-group">
                                        <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                            <span>{{ $prop->label }}</span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                        <div class="filter-group-content">
                                            @if($prop->input_type == 'select')
                                                <select name="props[{{ $prop->id }}]" class="filter-select font-bold" onchange="this.form.submit()">
                                                    <option value="">{{ $prop->placeholder ?? 'الكل' }}</option>
                                                    @foreach($prop->options ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ (request('props')[$prop->id] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($prop->input_type == 'radio' || $prop->input_type == 'checkbox')
                                                <div class="filter-categories-list">
                                                    @foreach($prop->options ?? [] as $opt)
                                                        <label class="category-filter-item">
                                                            <input type="{{ $prop->input_type }}" name="props[{{ $prop->id }}]{{ $prop->input_type == 'checkbox' ? '[]' : '' }}" value="{{ $opt }}" 
                                                                {{ in_array($opt, (array)(request('props')[$prop->id] ?? [])) ? 'checked' : '' }} onchange="this.form.submit()">
                                                            <span class="radio-custom"></span>
                                                            <span class="cat-name font-bold">{{ $opt }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                 <input type="{{ $prop->input_type == 'price' ? 'number' : ($prop->input_type ?: 'text') }}" name="props[{{ $prop->id }}]" class="filter-select font-bold" placeholder="{{ $prop->placeholder }}" value="{{ is_array(request('props')[$prop->id] ?? '') ? '' : (request('props')[$prop->id] ?? '') }}" onchange="this.form.submit()">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    </form>
                </div>
            </aside>

            <!-- Left Pane: Purchases Grid -->
            <div class="lg:col-span-3 transition-all duration-300" id="myPurchasesGridContent">
                @if($orders->isEmpty())
                    <!-- Empty State -->
                    <div class="empty-state-card">
                        <div class="empty-illustration-container mx-auto mb-6" style="width: 150px; height: 150px; background: #F8FAFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid #F1F5F9;">
                            <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="25" y="15" width="46" height="58" rx="8" fill="#E2E8F0" stroke="#94A3B8" stroke-width="3" />
                                <circle cx="58" cy="58" r="23" fill="white" stroke="#1A73E8" stroke-width="4.5" />
                                <path d="M58 48C58 48 62 45 65 48C68 51 63 57 58 62C53 57 48 51 51 48C54 45 58 48 58 48Z" fill="#1A73E8" stroke="#1A73E8" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M74 74L86 86" stroke="#64748B" stroke-width="5.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="empty-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.5rem; color: #1E293B;">لا يوجد مشتريات حالياً</h2>
                        <p class="empty-subtext" style="color: #64748B; margin-bottom: 0;">مشترياتك وطلباتك التجارية ستظهر هنا فور إتمامها عبر الموقع.</p>
                    </div>
                @else
                    <!-- Purchases Grid List -->
                    <div id="myPurchasesContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 transition-all duration-300">
                        @foreach($orders as $order)
                            @php
                                $ad = $order->ad;
                                $image = $ad && $ad->image ? (Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/')) : null;
                                $seller = $order->seller;
                            @endphp
                            
                            <div class="p-ad-card" data-ad-id="{{ $ad->id ?? '' }}">
                                <div class="p-card-main-content" style="cursor: pointer;" onclick="window.location='{{ $ad ? route('ads.show', $ad->id) : '#' }}'">
                                    <div class="p-card-image">
                                        <a href="{{ $ad ? route('ads.show', $ad->id) : '#' }}" class="h-full w-full block bg-gray-50">
                                            @if($image)
                                                <img src="{{ $image }}" alt="{{ $ad->title ?? 'إعلان' }}">
                                            @else
                                                <div class="flex items-center justify-center h-full w-full text-gray-300">
                                                    <i class="fa-solid fa-image text-4xl"></i>
                                                </div>
                                            @endif
                                        </a>
                                        
                                        <div class="p-badges">
                                            <!-- Sale Type Tag -->
                                            @if($ad)
                                                <span class="p-badge-pill {{ $ad->type == 'direct_purchase' ? 'type-direct' : 'type-auction' }}">
                                                    @if($ad->type == 'electronic_auction') الكتروني
                                                    @elseif($ad->type == 'hybrid_auction') هجين
                                                    @elseif($ad->type == 'direct_purchase') شراء فوري
                                                    @else مناقصة @endif
                                                </span>
                                            @endif
                                            
                                            <!-- Order Status Tag -->
                                            <span class="p-badge-pill status-{{ str_replace('_', '-', $order->status) }}">
                                                @if($order->status == 'pending_payment') بانتظار الدفع
                                                @elseif($order->status == 'paid') تم الدفع
                                                @elseif($order->status == 'receiving') قيد الاستلام
                                                @elseif($order->status == 'received') تم الاستلام
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="p-card-body">
                                        <span class="p-category-pill">{{ $ad->category->name ?? 'غير مصنف' }}</span>
                                        <a href="{{ $ad ? route('ads.show', $ad->id) : '#' }}" style="text-decoration: none; color: inherit;">
                                            <h3 class="p-ad-title">{{ $ad->title ?? 'طلب شراء إعلان' }}</h3>
                                        </a>
                                        
                                        <div class="p-price-row">
                                            <span class="p-price-label">سعر الشراء النهائي</span>
                                            <div class="p-price-value font-black text-blue-600">
                                                <span>{{ number_format($order->final_price) }}</span>
                                                <x-riyal-icon class="w-5 h-5 text-blue-600" />
                                            </div>
                                        </div>
                                        
                                        <!-- Seller details -->
                                        @if($seller)
                                            <div class="buyer-info-row">
                                                <div class="buyer-avatar-placeholder">
                                                    {{ mb_substr($seller->name, 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-[10px] text-gray-400 font-bold">البائع</span>
                                                    <span class="buyer-name">{{ $seller->name }}</span>
                                                </div>
                                            </div>
                                        @endif


                                    </div>
                                </div>
                                
                                <!-- Action footer -->
                                @php
                                    $show_footer = in_array($order->status, ['pending_payment', 'receiving', 'received']);
                                @endphp

                                @if($show_footer)
                                    <div class="p-card-footer" onclick="event.stopPropagation()">
                                        @if($order->status == 'pending_payment')
                                            <button type="button" onclick="showPaymentDetails('{{ $order->order_number }}', '{{ number_format($order->final_price) }}')" class="btn" style="width: 100%; background: linear-gradient(135deg, #1A73E8 0%, #1557B0 100%); color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 42px; cursor: pointer; font-weight: bold; border: none; box-shadow: 0 4px 12px rgba(26, 115, 232, 0.2);">
                                                <span>اتمام الدفع</span>
                                            </button>
                                        @elseif($order->status == 'receiving')
                                            <button type="button" onclick="copyDeliveryCode('{{ $order->delivery_code ?? '123456' }}')" class="btn" style="width: 100%; background: linear-gradient(135deg, #1A73E8 0%, #1557B0 100%); color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 42px; cursor: pointer; font-weight: bold; border: none; box-shadow: 0 4px 12px rgba(26, 115, 232, 0.2);">
                                                <span>أرسل كود التسليم</span>
                                            </button>
                                        @elseif($order->status == 'received')
                                            @if($order->review)
                                                <button type="button" disabled class="btn" style="width: 100%; background-color: #F1F5F9; color: #94A3B8; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 42px; font-weight: bold;">
                                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                                    <span>تم التقييم بنجاح</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="openRatingModal('{{ $seller->id ?? '' }}', '{{ $seller->name ?? 'البائع' }}')" class="btn" style="width: 100%; border: 1.5px solid #1A73E8; color: #1A73E8; background: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 42px; cursor: pointer; font-weight: bold; transition: all 0.2s;">
                                                    <span>أقيم تجربتك</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-12 flex justify-center">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Review submission form (executed via JS dynamically) -->
<form id="purchasesReviewForm" action="{{ route('reviews.store') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="rated_user_id" id="review_seller_id">
    <input type="hidden" name="rating" id="review_rating_val">
    <input type="hidden" name="comment" id="review_comment_val">
</form>

@endsection

@push('scripts')
<script>
    // Toggle sidebar filters
    window.toggleMySidebar = function() {
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('myPurchasesGridContent');
        const toggleBtn = document.getElementById('filterToggleBtn');
        
        if (!pane || !gridContent) return;
        
        if (pane.classList.contains('hidden')) {
            pane.classList.remove('hidden');
            pane.classList.add('block');
            gridContent.classList.remove('lg:col-span-4');
            gridContent.classList.add('lg:col-span-3');
            if (toggleBtn) toggleBtn.classList.add('active');
            localStorage.setItem('myPurchasesFilterVisible', 'true');
        } else {
            pane.classList.remove('block');
            pane.classList.add('hidden');
            gridContent.classList.remove('lg:col-span-3');
            gridContent.classList.add('lg:col-span-4');
            if (toggleBtn) toggleBtn.classList.remove('active');
            localStorage.setItem('myPurchasesFilterVisible', 'false');
        }
    };

    // Toggle grid/list layouts
    window.toggleAdsLayout = function(mode) {
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        const container = document.getElementById('myPurchasesContainer');
        
        if (!gridBtn || !listBtn || !container) return;
        
        if (mode === 'list') {
            gridBtn.classList.remove('active');
            listBtn.classList.add('active');
            container.classList.add('list-view');
        } else {
            listBtn.classList.remove('active');
            gridBtn.classList.add('active');
            container.classList.remove('list-view');
        }
        localStorage.setItem('myPurchasesLayout', mode);
    };

    // Close sort dropdown
    document.addEventListener('click', function(e) {
        const sortContainer = document.getElementById('mySortContainer');
        if (sortContainer && !sortContainer.contains(e.target)) {
            sortContainer.classList.remove('active');
        }
    });

    // Toggle filter groups
    window.toggleSidebarGroup = function(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('i');
        if (content) {
            content.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    };

    // Copy delivery code to clipboard
    window.copyDeliveryCode = function(code) {
        navigator.clipboard.writeText(code).then(() => {
            Swal.fire({
                title: 'تم النسخ!',
                text: 'تم نسخ كود الاستلام بنجاح، قدّمه للبائع لتسليم السلعة.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'rounded-[32px]' }
            });
        });
    };

    // Show Payment Bank Transfer details popup
    window.showPaymentDetails = function(orderNumber, price) {
        Swal.fire({
            title: '<span style="font-size: 1.35rem; font-weight: 900; color: #1E293B;">تفاصيل الحساب البنكي للدفع</span>',
            html: `
                <div class="text-right space-y-4" style="direction: rtl;">
                    <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-2xl mb-4">
                        <p class="text-xs text-gray-500 font-bold mb-1">رقم الطلب:</p>
                        <p class="text-sm font-black text-blue-600">#${orderNumber}</p>
                        <p class="text-xs text-gray-500 font-bold mt-2 mb-1">الإجمالي المطلوب سداده:</p>
                        <p class="text-lg font-black text-gray-900">${price} ريال سعودي</p>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-400 font-bold block">اسم البنك:</span>
                            <span class="text-sm font-black text-slate-700 block">البنك الأهلي السعودي (SNB)</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 font-bold block">اسم الحساب:</span>
                            <span class="text-sm font-black text-slate-700 block">شركة تقنية المعلومات للمزادات المحدودة</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 font-bold block">رقم الآيبان (IBAN):</span>
                            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-2.5 rounded-xl mt-1">
                                <span class="text-xs font-black text-slate-800" style="font-family: monospace; letter-spacing: 0.5px;">SA9876543210000012345678</span>
                                <button type="button" onclick="navigator.clipboard.writeText('SA9876543210000012345678'); Swal.fire('نسخ!', 'تم نسخ رقم الآيبان.', 'success')" class="text-blue-500 hover:text-blue-700 font-bold text-xs">نسخ</button>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-rose-500 font-bold leading-relaxed mt-4 pt-3 border-t border-slate-100">
                        * تنبيه: يرجى كتابة رقم الطلب (#${orderNumber}) في وصف الحوالة البنكية، ثم إرسال صورة التحويل لخدمة العملاء لإتمام مراجعة الطلب وتأكيد عملية الدفع.
                    </p>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'موافق',
            confirmButtonColor: '#1A73E8',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full',
                confirmButton: 'rounded-2xl px-8 py-3.5 font-bold text-sm w-full bg-primary border-0'
            }
        });
    };

    // Open Interactive Star Rating Modal for the Buyer to rate the Seller
    window.openRatingModal = function(sellerId, sellerName) {
        let currentRating = 5;
        
        Swal.fire({
            title: `<span style="font-size: 1.3rem; font-weight: 900;">تقييم البائع (${sellerName})</span>`,
            html: `
                <div class="text-center" style="direction: rtl;">
                    <p class="text-xs font-bold text-slate-400 mb-4">ما هو تقييمك لتجربتك مع البائع؟</p>
                    <div class="swal2-stars" id="star-rating-container">
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="1"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="2"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="3"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="4"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="5"></i>
                    </div>
                    <textarea id="swal-rating-comment" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 focus:outline-none focus:border-blue-500 text-sm font-bold mt-4" rows="3" placeholder="اكتب تعليقك هنا حول المعاملة مع البائع..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'إرسال التقييم',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#F1F5F9',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full text-right',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold text-sm bg-primary border-0',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold text-sm text-slate-500 border-0'
            },
            didOpen: () => {
                const starItems = document.querySelectorAll('.swal2-star-item');
                starItems.forEach(star => {
                    star.addEventListener('click', (e) => {
                        const index = parseInt(star.getAttribute('data-index'));
                        currentRating = index;
                        // Color stars up to index
                        starItems.forEach(s => {
                            const idx = parseInt(s.getAttribute('data-index'));
                            if (idx <= index) {
                                s.classList.add('selected');
                            } else {
                                s.classList.remove('selected');
                            }
                        });
                    });
                });
            },
            preConfirm: () => {
                const comment = document.getElementById('swal-rating-comment').value.trim();
                return { rating: currentRating, comment: comment };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Populate forms and submit
                document.getElementById('review_seller_id').value = sellerId;
                document.getElementById('review_rating_val').value = result.value.rating;
                document.getElementById('review_comment_val').value = result.value.comment;
                
                // Show loading
                Swal.fire({
                    title: 'جاري تسجيل التقييم...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit Form
                document.getElementById('purchasesReviewForm').submit();
            }
        });
    };

    // Load preferences
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Layout Preference
        const savedLayout = localStorage.getItem('myPurchasesLayout');
        if (savedLayout === 'list') {
            toggleAdsLayout('list');
        }

        // 2. Filter Visibility Preference
        const filterVisible = localStorage.getItem('myPurchasesFilterVisible');
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('myPurchasesGridContent');
        const toggleBtn = document.getElementById('filterToggleBtn');
        
        if (filterVisible === 'false') {
            if (pane && gridContent) {
                pane.classList.add('hidden');
                pane.classList.remove('block');
                gridContent.classList.add('lg:col-span-4');
                gridContent.classList.remove('lg:col-span-3');
                if (toggleBtn) toggleBtn.classList.remove('active');
            }
        } else {
            if (pane && gridContent) {
                pane.classList.remove('hidden');
                pane.classList.add('block');
                gridContent.classList.remove('lg:col-span-4');
                gridContent.classList.add('lg:col-span-3');
                if (toggleBtn) toggleBtn.classList.add('active');
            }
        }
    });
</script>
@endpush
