@extends('layouts.main')

@section('title', 'المزادات و العروض | مزاد')

@push('styles')
<style>
    .bids-offers-page {
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

    /* Tabs Styling */
    .tab-container {
        display: flex;
        gap: 0.5rem;
        background: #F1F5F9;
        padding: 0.35rem;
        border-radius: 16px;
        width: fit-content;
        margin-bottom: 2.5rem;
    }

    .tab-btn {
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.95rem;
        color: #475569;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        background: transparent;
        text-decoration: none !important;
        display: inline-block;
    }

    .tab-btn.active {
        background: #1A73E8;
        color: white !important;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
    }

    /* Sidebar Filters */
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
        font-weight: 800;
        font-size: 0.95rem;
        color: #334155;
        margin-bottom: 1rem;
    }

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

    .category-filter-item .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #CBD5E1;
        border-radius: 6px;
        display: inline-block;
        position: relative;
        transition: all 0.2s;
        background: white;
    }

    .category-filter-item input:checked + .checkbox-custom {
        border-color: #1A73E8;
        background-color: #1A73E8;
    }

    .category-filter-item input:checked + .checkbox-custom::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 0.7rem;
        color: white;
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

    /* Cards layout */
    .bo-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 380px;
    }

    .bo-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.05);
    }

    .bo-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .bo-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #1A73E8;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.25rem;
    }

    .bo-user-info h4 {
        font-size: 0.95rem;
        font-weight: 900;
        color: #1E293B;
        margin-bottom: 0.15rem;
    }

    .bo-user-info span {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748B;
    }

    .bo-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        margin-bottom: 1.25rem;
    }

    .bo-line {
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .bo-line strong {
        color: #1E293B;
        font-weight: 800;
    }

    .bo-price-pill {
        background: #E8F1FE;
        color: #1A73E8;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        width: fit-content;
        margin-top: 0.5rem;
    }

    .bo-date {
        font-size: 0.75rem;
        color: #94A3B8;
        font-weight: 700;
        margin-top: 0.25rem;
    }

    .bo-footer {
        display: flex;
        gap: 0.6rem;
        margin-top: auto;
    }

    .bo-footer button, .bo-footer a {
        flex: 1;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none !important;
    }

    .btn-bo-primary {
        background: #1A73E8;
        color: white !important;
    }
    .btn-bo-primary:hover { background: #1557B0; }

    .btn-bo-outline {
        background: white;
        border: 1.5px solid #1A73E8 !important;
        color: #1A73E8 !important;
    }
    .btn-bo-outline:hover { background: #F8FAFC; }

    .btn-bo-grey {
        background: #F1F5F9;
        color: #64748B !important;
        border: 1px solid #E2E8F0 !important;
    }
    .btn-bo-grey:hover { background: #E2E8F0; }

    .bo-badge {
        font-size: 0.8rem;
        font-weight: 800;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        width: fit-content;
        margin-top: 0.5rem;
    }

    .bo-badge.agreed {
        background: #DCFCE7;
        color: #15803D;
    }

    .bo-badge.rejected {
        background: #FEE2E2;
        color: #B91C1C;
    }

    /* Modal Popups Styling */
    .custom-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(8px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: fadeIn 0.25s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .custom-modal-card {
        background: white;
        border-radius: 28px;
        width: 100%;
        max-width: 480px;
        padding: 2.25rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        direction: rtl;
        text-align: right;
        animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-close-btn {
        position: absolute;
        top: 1.5rem;
        left: 1.5rem;
        right: auto;
        font-size: 1.5rem;
        color: #1E293B;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s;
    }
    .modal-close-btn:hover { color: #64748B; }

    .modal-title {
        font-size: 1.75rem;
        font-weight: 900;
        color: #1E293B;
        margin-bottom: 0.5rem;
        margin-top: 0.25rem;
    }

    .modal-subtitle {
        font-size: 0.95rem;
        color: #64748B;
        font-weight: 600;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .modal-grey-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        font-weight: 800;
        color: #1E293B;
        font-size: 1.05rem;
        margin-bottom: 1.5rem;
    }

    .modal-input-group {
        margin-bottom: 1.25rem;
    }

    .modal-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 800;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .modal-input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .modal-input {
        width: 100%;
        height: 50px;
        padding-right: 1rem;
        padding-left: 3.5rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 16px;
        font-weight: 800;
        font-size: 1rem;
        color: #1E293B;
        outline: none;
        background: #F8FAFC;
        transition: all 0.2s;
    }

    .modal-input:focus {
        border-color: #1A73E8;
        background: white;
    }

    .modal-input-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-weight: 900;
    }

    .modal-info-line {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #1E293B;
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 2rem;
    }

    .modal-info-line i {
        color: #1E293B;
        font-size: 1.1rem;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
    }

    .modal-actions button {
        flex: 1;
        height: 52px;
        border-radius: 16px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-btn-primary {
        background: #1A73E8;
        color: white !important;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
    }
    .modal-btn-primary:hover {
        background: #1557B0;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(26, 115, 232, 0.25);
    }

    .modal-btn-cancel {
        background: #E2E8F0;
        color: #1E293B !important;
    }
    .modal-btn-cancel:hover {
        background: #CBD5E1;
        transform: translateY(-1px);
    }

    /* Role Tabs Styling */
    .role-tab-container {
        display: flex;
        gap: 0.5rem;
        background: #F1F5F9;
        padding: 0.35rem;
        border-radius: 16px;
        width: fit-content;
    }

    .role-tab-btn {
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.95rem;
        color: #475569;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        background: transparent;
        text-decoration: none !important;
        display: inline-block;
    }

    .role-tab-btn.active {
        background: #1A73E8;
        color: white !important;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
    }
</style>
@endpush

@section('content')
<div class="bids-offers-page">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <a href="{{ route('home') }}" class="breadcrumbs-pill">
            <span>الرئيسية</span>
            <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i>
            <span>المزادات و العروض</span>
        </a>

        <!-- Page Title & Tabs -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6">
            <h1 class="page-title !mb-0" style="margin-bottom: 0 !important;">المزادات و العروض</h1>
            
            <div class="flex flex-wrap gap-4 items-center">
                <!-- Role Tabs (Received vs Sent) -->
                <div class="role-tab-container">
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'seller']) }}" class="role-tab-btn {{ $role === 'seller' ? 'active' : '' }}">الواردة (كبائع)</a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'buyer']) }}" class="role-tab-btn {{ $role === 'buyer' ? 'active' : '' }}">المرسلة (كمشتري)</a>
                </div>

                <!-- Tabs (Auctions vs Offers) -->
                <div class="tab-container">
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'auctions']) }}" class="tab-btn {{ $tab === 'auctions' ? 'active' : '' }}">للمزادات</a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'offers']) }}" class="tab-btn {{ $tab === 'offers' ? 'active' : '' }}">العروض</a>
                </div>
            </div>
        </div>

        <!-- Action / Search Bar -->
        <div class="search-action-bar flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            <div class="flex items-center gap-3 flex-1 max-w-[560px]">
                <button type="button" class="action-btn active flex-shrink-0" id="filterToggleBtn" onclick="toggleMySidebar()" title="تصفية" style="height: 48px; border-radius: 16px;">
                    <i class="fa-solid fa-sliders"></i>
                </button>

                <form action="{{ route('bids-offers.index') }}" method="GET" style="position: relative; width: 100%;">
                    @foreach(request()->except('q', 'page') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $nv)
                                        <input type="hidden" name="{{ $key }}[{{ $k }}][]" value="{{ $nv }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}">
                                @endif
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث في العروض..." class="search-bar-input">
                    <i class="fa-solid fa-magnifying-glass search-bar-icon" style="position: absolute; top: 50%; transform: translateY(-50%); right: 1.25rem; left: auto; color: #94A3B8; font-size: 1.1rem;"></i>
                </form>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Order/Sort dropdown -->
                <div class="sort-dropdown-container" id="mySortContainer" onclick="this.classList.toggle('active')" style="position: relative;">
                    <button class="action-btn">
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                        <span>الترتيب</span>
                    </button>
                    <div class="sort-options" style="left: 0; right: auto;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="{{ request('sort') == 'newest' || !request('sort') ? 'active' : '' }}">الأحدث</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" class="{{ request('sort') == 'price_high' ? 'active' : '' }}">الأعلى سعراً</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" class="{{ request('sort') == 'price_low' ? 'active' : '' }}">الأقل سعراً</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Right Pane: Sidebar Filters -->
            <aside class="lg:col-span-1 block" id="mySidebarPane">
                <div class="filter-card">
                    <div class="filter-header">
                        <h3>تصفية النتائج</h3>
                        <a href="{{ route('bids-offers.index', ['tab' => $tab]) }}" class="reset-btn">إعادة تعيين</a>
                    </div>

                    <form id="filterForm" action="{{ route('bids-offers.index') }}" method="GET" class="space-y-2">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        <!-- الموقع -->
                        <div class="filter-group">
                            <div class="filter-group-header">الموقع</div>
                            <div class="filter-group-content active">
                                <div class="location-filter-wrapper" style="display: flex; gap: 0.75rem; align-items: center; margin-top: 0.5rem;">
                                    <button type="button" class="location-icon-btn" onclick="handleLocationClick()" style="width: 44px; height: 44px; background: #1A73E8; color: white; border: none; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; flex-shrink: 0;">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </button>
                                    <select name="city" class="filter-select" onchange="this.form.submit()" style="flex: 1; height: 44px; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 0 1rem; font-weight: 700; color: #475569; outline: none; background: white;">
                                        <option value="">حدد الموقع</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- نوع الاعلان (فقط للمزادات) -->
                        @if($tab !== 'offers')
                            <div class="filter-group">
                                <div class="filter-group-header">نوع الاعلان</div>
                                <div class="filter-group-content active" style="margin-top: 0.5rem;">
                                    <div class="filter-pills">
                                        <label class="filter-pill-label">
                                            <input type="radio" name="type" value="" {{ !request('type') ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span>الكل</span>
                                        </label>
                                        <label class="filter-pill-label">
                                            <input type="radio" name="type" value="auction" {{ request('type') == 'auction' || request('type') == 'electronic_auction' || request('type') == 'hybrid_auction' ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span>مزادات</span>
                                        </label>
                                        <label class="filter-pill-label">
                                            <input type="radio" name="type" value="tender" {{ request('type') == 'tender' ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span>مناقصات</span>
                                        </label>
                                    </div>

                                    <!-- Sub-filters for Auction -->
                                    @if(request('type') == 'auction' || request('type') == 'electronic_auction' || request('type') == 'hybrid_auction')
                                        <div class="auction-sub-filters" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #E2E8F0;">
                                            <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">نوع المزاد</span>
                                            <div class="filter-pills">
                                                <label class="filter-pill-label">
                                                    <input type="radio" name="type" value="auction" {{ request('type') == 'auction' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <span>الكل</span>
                                                </label>
                                                <label class="filter-pill-label">
                                                    <input type="radio" name="type" value="electronic_auction" {{ request('type') == 'electronic_auction' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <span>إلكتروني</span>
                                                </label>
                                                <label class="filter-pill-label">
                                                    <input type="radio" name="type" value="hybrid_auction" {{ request('type') == 'hybrid_auction' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <span>هجين</span>
                                                </label>
                                            </div>

                                            <div style="margin-top: 1rem;">
                                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                    <input type="checkbox" name="is_enfaz" value="1" {{ request('is_enfaz') ? 'checked' : '' }} onchange="this.form.submit()" style="width: 18px; height: 18px;">
                                                    <span style="font-size: 0.85rem; font-weight: 700; color: #475569;">عن طريق إنفاذ</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- حالة العرض / الإعلان -->
                        @if($tab === 'offers')
                            <div class="filter-group">
                                <div class="filter-group-header">حالة العرض</div>
                                <div class="filter-pills">
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="all" {{ request('status', 'all') === 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>الكل</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="accepted" {{ request('status') === 'accepted' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>تم الاتفاق</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="active" {{ request('status') === 'active' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>لم يتم الاتفاق</span>
                                    </label>
                                </div>
                            </div>
                        @else
                            <div class="filter-group">
                                <div class="filter-group-header">حالة الاعلان</div>
                                <div class="filter-pills">
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="all" {{ request('status', 'all') === 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>الكل</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="active" {{ request('status') === 'active' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>قائم</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="upcoming" {{ request('status') === 'upcoming' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>مستقبلي</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="status" value="accepted" {{ request('status') === 'accepted' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>منتهي</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- الفئات -->
                        <div class="filter-group">
                            <div class="filter-group-header">الفئات</div>
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

                        <!-- Dynamic Category Properties -->
                        @if(isset($activeCategory))
                            <div class="active-category-info" style="background: #E8F1FE; padding: 1rem; border-radius: 12px; margin-top: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(26, 115, 232, 0.1);">
                                <div style="width: 32px; height: 32px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #1A73E8; font-size: 1rem;">
                                    <i class="fa-solid fa-tags"></i>
                                </div>
                                <div style="flex: 1;">
                                    <span style="display: block; font-size: 0.75rem; color: #64748B; font-weight: 700;">الفئة المختارة</span>
                                    <span style="display: block; font-size: 0.9rem; color: #1A73E8; font-weight: 800;">{{ $activeCategory->name }}</span>
                                </div>
                                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'props' => null]) }}" style="color: #EF4444; font-size: 0.9rem;" title="إلغاء الفئة"><i class="fa-solid fa-circle-xmark"></i></a>
                            </div>

                            @if($activeCategory->properties->count() > 0)
                                @foreach($activeCategory->properties->sortBy('sort_order') as $prop)
                                    <div class="filter-group">
                                        <div class="filter-group-header">{{ $prop->label }}</div>
                                        <div class="filter-group-content active" style="margin-top: 0.5rem;">
                                            @if($prop->input_type == 'select')
                                                <select name="props[{{ $prop->id }}]" class="filter-select" onchange="this.form.submit()" style="width: 100%; height: 42px; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 0 1rem; font-weight: 700; color: #475569; outline: none; background: white;">
                                                    <option value="">{{ $prop->placeholder ?? 'الكل' }}</option>
                                                    @foreach($prop->options ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ (request('props')[$prop->id] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($prop->input_type == 'radio' || $prop->input_type == 'checkbox')
                                                <div class="filter-options-list" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                                                    @foreach($prop->options ?? [] as $opt)
                                                        <label class="filter-option-item" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                            <input type="{{ $prop->input_type }}" name="props[{{ $prop->id }}]{{ $prop->input_type == 'checkbox' ? '[]' : '' }}" value="{{ $opt }}" 
                                                                {{ in_array($opt, (array)(request('props')[$prop->id] ?? [])) ? 'checked' : '' }} onchange="this.form.submit()" style="width: 18px; height: 18px;">
                                                            <span class="custom-check-pill" style="font-size: 0.85rem; font-weight: 700; color: #475569;">{{ $opt }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                 <input type="{{ $prop->input_type == 'price' ? 'number' : ($prop->input_type ?: 'text') }}" name="props[{{ $prop->id }}]" class="filter-input-text" placeholder="{{ $prop->placeholder }}" value="{{ is_array(request('props')[$prop->id] ?? '') ? '' : (request('props')[$prop->id] ?? '') }}" onchange="this.form.submit()" style="width: 100%; height: 42px; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 0 1rem; font-weight: 700; color: #1E293B; outline: none; background: white;">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif

                        <!-- المعلن -->
                        <div class="filter-group">
                            <div class="filter-group-header">المعلن</div>
                            <div class="filter-categories-list">
                                <label class="category-filter-item">
                                    <input type="radio" name="advertiser_type" value="" {{ !request('advertiser_type') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="radio-custom"></span>
                                    <span class="cat-name font-bold">الكل</span>
                                </label>
                                <label class="category-filter-item">
                                    <input type="radio" name="advertiser_type" value="individual" {{ request('advertiser_type') == 'individual' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="radio-custom"></span>
                                    <span class="cat-name font-bold">فرد</span>
                                </label>
                                <label class="category-filter-item">
                                    <input type="radio" name="advertiser_type" value="company" {{ request('advertiser_type') == 'company' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="radio-custom"></span>
                                    <span class="cat-name font-bold">شركة</span>
                                </label>
                                <label class="category-filter-item">
                                    <input type="radio" name="advertiser_type" value="partner" {{ request('advertiser_type') == 'partner' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="radio-custom"></span>
                                    <span class="cat-name font-bold">مندوب شركة</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Left Pane: Bids Grid -->
            <div class="lg:col-span-3 transition-all duration-300" id="myAdsGridContent">
                @if($bids->isEmpty())
                    <!-- Empty State -->
                    <div class="filter-card py-16 text-center" style="border-radius: 32px; background: white;">
                        <div class="mx-auto mb-6 flex items-center justify-center" style="width: 120px; height: 120px; background: #F8FAFC; border-radius: 50%; border: 4px solid #F1F5F9;">
                            <i class="fa-solid fa-gavel text-4xl text-gray-300"></i>
                        </div>
                        <h2 class="text-xl font-black text-gray-800">لا توجد مزادات أو عروض حالياً</h2>
                        <p class="text-gray-400 font-bold mt-2">لم تقم باستقبال أي عروض أو مزادات على إعلاناتك بناءً على معايير التصفية الحالية.</p>
                    </div>
                @else
                    <!-- Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($bids as $bid)
                            @if($role === 'buyer' && $tab === 'auctions')
                                @if($bid->ad)
                                    @include('partials.ad-card', ['ad' => $bid->ad])
                                @endif
                            @else
                                @php
                                    $cardUser = ($role === 'buyer') ? ($bid->ad->user ?? null) : ($bid->user ?? null);
                                    $firstLetter = mb_substr($cardUser->name ?? 'أ', 0, 1);
                                    $userType = $cardUser ? ($cardUser->account_type == 'company' ? 'شركة' : ($cardUser->account_type == 'partner' ? 'مندوب شركة' : 'فرد')) : 'مجهول';
                                @endphp
                                <div class="bo-card" id="bo-card-{{ $bid->id }}">
                                    <div>
                                        <!-- Header -->
                                        <div class="bo-header">
                                            <div class="bo-avatar">{{ $firstLetter }}</div>
                                            <div class="bo-user-info">
                                                <h4>{{ $cardUser->name ?? 'مجهول' }}</h4>
                                                <span>{{ $userType }}</span>
                                            </div>
                                        </div>

                                        <!-- Body -->
                                        <div class="bo-body">
                                            <div class="bo-line">
                                                <span>الاعلان:</span>
                                                <strong>
                                                    @if($bid->ad)
                                                        <a href="{{ route('bids.history-page', $bid->ad->id) }}" class="hover:text-primary transition-all duration-200" style="text-decoration: none; color: inherit;">
                                                            {{ $bid->ad->title ?? 'إعلان غير معروف' }}
                                                        </a>
                                                    @else
                                                        إعلان غير معروف
                                                    @endif
                                                </strong>
                                            </div>
                                            <div class="bo-line">
                                                <span>الفئة:</span>
                                                <strong>{{ $bid->ad->category->name ?? 'غير مصنف' }}</strong>
                                            </div>
                                            
                                            <!-- Price Pill -->
                                            <div class="bo-price-pill" id="bo-price-pill-{{ $bid->id }}">
                                                <span>قيمة السومة</span>
                                                <strong>{{ number_format($bid->amount) }}</strong>
                                                <x-riyal-icon class="w-4 h-4" />
                                            </div>

                                            <!-- Date -->
                                            @php
                                                $formattedDate = $bid->created_at->format('Y/m/d - h:i:s a');
                                                $formattedDate = str_replace(['am', 'pm'], ['ص', 'م'], $formattedDate);
                                            @endphp
                                            <div class="bo-date">
                                                {{ $formattedDate }}
                                            </div>

                                            <!-- Badge Status if finished -->
                                            @if($bid->status === 'accepted')
                                                <div class="bo-badge agreed">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    <span>تم الاتفاق</span>
                                                </div>
                                            @elseif($bid->status === 'rejected')
                                                <div class="bo-badge rejected">
                                                    <i class="fa-solid fa-circle-xmark"></i>
                                                    <span>لم يتم الاتفاق</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="bo-footer mt-4" id="bo-footer-{{ $bid->id }}">
                                        @if($role === 'seller')
                                            @if($bid->status === 'active')
                                                @if($tab === 'offers')
                                                    <!-- Direct Purchase buttons: Accept & Counter Offer -->
                                                    <button class="btn-bo-primary" onclick="acceptBidOffer({{ $bid->id }})">
                                                        <i class="fa-solid fa-circle-check"></i> قبول العرض
                                                    </button>
                                                    <button class="btn-bo-outline" onclick="openCounterOfferModal({{ $bid->id }}, {{ $bid->amount }})">
                                                        <i class="fa-solid fa-handshake"></i> قدم عرض
                                                    </button>
                                                @else
                                                    <!-- Auction buttons: Accept & Reject -->
                                                    <button class="btn-bo-primary" onclick="acceptBidOffer({{ $bid->id }})">
                                                        <i class="fa-solid fa-circle-check"></i> قبول العرض
                                                    </button>
                                                    <button class="btn-bo-grey" onclick="rejectBidOffer({{ $bid->id }})">
                                                        <i class="fa-solid fa-circle-xmark"></i> رفض العرض
                                                    </button>
                                                @endif
                                            @else
                                                <!-- Finished Bids show Details -->
                                                <button class="btn-bo-primary w-full" onclick="showDetailsModal({{ $bid->id }})" style="flex: auto;">
                                                    <i class="fa-solid fa-circle-info"></i> عرض التفاصيل
                                                </button>
                                            @endif
                                        @else
                                            <!-- Buyer Role Actions -->
                                            @if($bid->status === 'active')
                                                <div class="text-center py-2 px-4 rounded-xl text-slate-500 font-bold bg-slate-50 border border-slate-200 w-full" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 42px;">
                                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                                    <span>بانتظار رد البائع</span>
                                                </div>
                                            @elseif($bid->status === 'accepted')
                                                <a href="{{ route('purchases.my') }}" class="btn-bo-primary w-full text-center py-2 rounded-xl" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 42px; width: 100%;">
                                                    <i class="fa-solid fa-credit-card"></i> إتمام الدفع
                                                </a>
                                            @else
                                                <button class="btn-bo-primary w-full" onclick="showDetailsModal({{ $bid->id }})" style="flex: auto;">
                                                    <i class="fa-solid fa-circle-info"></i> عرض التفاصيل
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12 flex justify-center">
                        {{ $bids->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 1️⃣ POPUP: Counter Offer Modal (قدم عرض) -->
<!-- ============================================== -->
<div id="counterOfferModal" class="custom-modal-overlay" onclick="closeCounterOfferModal(event)">
    <div class="custom-modal-card" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button class="modal-close-btn" onclick="document.getElementById('counterOfferModal').style.display='none'">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Header -->
        <h2 class="modal-title">قدم عرض</h2>
        <p class="modal-subtitle">الرجاء إدخال المبلغ الجديد الذي ترغب في تقديمه.</p>

        <!-- Grey Info Card -->
        <div class="modal-grey-card">
            <span>قيمة العرض الأخير:</span>
            <strong id="modalLastOfferAmount">35,000</strong>
            <x-riyal-icon class="w-5 h-5 text-primary" />
        </div>

        <form id="counterOfferForm" onsubmit="submitCounterOffer(event)">
            @csrf
            <input type="hidden" id="modalBidId" value="">
            
            <!-- Input group -->
            <div class="modal-input-group">
                <label class="modal-label" for="counterAmountInput">قيمة العرض</label>
                <div class="modal-input-container">
                    <input type="number" id="counterAmountInput" required placeholder="أدخل قيمة العرض" class="modal-input">
                    <span class="modal-input-icon">
                        <x-riyal-icon class="w-4 h-4 text-gray-500" />
                    </span>
                </div>
            </div>

            <!-- Info Lines -->
            <div class="modal-info-line">
                <i class="fa-solid fa-circle-info"></i>
                <span id="modalRemainingOffersText">لديك 2 عروض متبقية على هذا العرض.</span>
            </div>

            <!-- Actions -->
            <div class="modal-actions">
                <button type="submit" class="modal-btn-primary">إرسال العرض</button>
                <button type="button" class="modal-btn-cancel" onclick="document.getElementById('counterOfferModal').style.display='none'">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- 2️⃣ POPUP: Bid/Offer Details Modal (عرض التفاصيل) -->
<!-- ============================================== -->
<div id="detailsModal" class="custom-modal-overlay" onclick="closeDetailsModal(event)">
    <div class="custom-modal-card" onclick="event.stopPropagation()" style="max-width: 520px;">
        <button class="modal-close-btn" onclick="document.getElementById('detailsModal').style.display='none'">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h2 class="modal-title">تفاصيل العرض</h2>
        <p class="modal-subtitle">استعراض كافة التفاصيل الخاصة بهذا المزاد / العرض المكتمل.</p>

        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
            <!-- Ad info -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1rem;">
                <h4 style="font-weight: 900; color: #1E293B; margin-bottom: 0.5rem;"><i class="fa-solid fa-rectangle-list text-primary ml-1"></i> الإعلان والمعلومات الأساسية</h4>
                <div class="bo-line" style="margin-bottom: 0.25rem;">الاعلان: <strong id="detAdTitle">---</strong></div>
                <div class="bo-line">الفئة: <strong id="detAdCategory">---</strong></div>
            </div>

            <!-- Buyer info -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1rem;">
                <h4 style="font-weight: 900; color: #1E293B; margin-bottom: 0.5rem;"><i class="fa-solid fa-user text-primary ml-1"></i> معلومات المشتري</h4>
                <div class="bo-line" style="margin-bottom: 0.25rem;">اسم المشتري: <strong id="detBuyerName">---</strong></div>
                <div class="bo-line" style="margin-bottom: 0.25rem;">نوع الحساب: <strong id="detBuyerType">---</strong></div>
                <div class="bo-line">رقم الجوال: <strong id="detBuyerPhone" dir="ltr">---</strong></div>
            </div>

            <!-- Offer Details -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1rem;">
                <h4 style="font-weight: 900; color: #1E293B; margin-bottom: 0.5rem;"><i class="fa-solid fa-receipt text-primary ml-1"></i> تفاصيل القبول والاتفاق</h4>
                <div class="bo-line" style="margin-bottom: 0.25rem;">الحالة النهائية: <strong id="detStatus">---</strong></div>
                <div class="bo-line" style="margin-bottom: 0.25rem;">قيمة السومة النهائية: <strong id="detAmount">---</strong></div>
                <div class="bo-line">التاريخ والوقت: <strong id="detDate">---</strong></div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="modal-btn-cancel w-full" onclick="document.getElementById('detailsModal').style.display='none'">حسناً</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle sidebar filters
    window.toggleMySidebar = function() {
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('myAdsGridContent');
        const toggleBtn = document.getElementById('filterToggleBtn');
        
        if (!pane || !gridContent) return;
        
        if (pane.classList.contains('hidden')) {
            pane.classList.remove('hidden');
            pane.classList.add('block');
            gridContent.classList.remove('lg:col-span-4');
            gridContent.classList.add('lg:col-span-3');
            if (toggleBtn) toggleBtn.classList.add('active');
            localStorage.setItem('boFilterVisible', 'true');
        } else {
            pane.classList.remove('block');
            pane.classList.add('hidden');
            gridContent.classList.remove('lg:col-span-3');
            gridContent.classList.add('lg:col-span-4');
            if (toggleBtn) toggleBtn.classList.remove('active');
            localStorage.setItem('boFilterVisible', 'false');
        }
    };

    // Close sort dropdown on click outside
    document.addEventListener('click', function(e) {
        const sortContainer = document.getElementById('mySortContainer');
        if (sortContainer && !sortContainer.contains(e.target)) {
            sortContainer.classList.remove('active');
        }
    });

    // ──────────────────────────────────────────────────────────
    // AJAX: Accept Bid/Offer
    // ──────────────────────────────────────────────────────────
    window.acceptBidOffer = function(bidId) {
        Swal.fire({
            title: 'قبول السومة والاتفاق؟',
            text: "سيتم إنهاء الإعلان والموافقة على بيع السلعة بهذا السعر تلقائياً!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'نعم، أوافق على السومة',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'rounded-[32px] p-8',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/bids-offers/${bidId}/accept`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[32px]' }
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'عذراً',
                            text: data.message,
                            customClass: { popup: 'rounded-[32px]' }
                        });
                    }
                });
            }
        });
    };

    // ──────────────────────────────────────────────────────────
    // AJAX: Reject Bid/Offer
    // ──────────────────────────────────────────────────────────
    window.rejectBidOffer = function(bidId) {
        Swal.fire({
            title: 'رفض السومة؟',
            text: "هل أنت متأكد من رفض هذه السومة المقدمة؟",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'نعم، ارفض العرض',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'rounded-[32px] p-8',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/bids-offers/${bidId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[32px]' }
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
            }
        });
    };

    // ──────────────────────────────────────────────────────────
    // POPUP: Counter Offer Form management
    // ──────────────────────────────────────────────────────────
    window.openCounterOfferModal = function(bidId, currentAmount) {
        document.getElementById('modalBidId').value = bidId;
        document.getElementById('modalLastOfferAmount').innerText = Number(currentAmount).toLocaleString('en-US');
        
        // Fetch real-time count
        fetch(`/bids-offers/${bidId}/details`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const rem = res.data.remaining_offers;
                document.getElementById('modalRemainingOffersText').innerText = `لديك ${rem} عروض متبقية على هذا العرض.`;
                document.getElementById('counterAmountInput').value = '';
                document.getElementById('counterOfferModal').style.display = 'flex';
            }
        });
    };

    window.closeCounterOfferModal = function(event) {
        if (event.target === document.getElementById('counterOfferModal')) {
            document.getElementById('counterOfferModal').style.display = 'none';
        }
    };

    window.submitCounterOffer = function(event) {
        event.preventDefault();
        const bidId = document.getElementById('modalBidId').value;
        const newAmount = document.getElementById('counterAmountInput').value;

        fetch(`/bids-offers/${bidId}/counter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount: newAmount })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('counterOfferModal').style.display = 'none';
                Swal.fire({
                    icon: 'success',
                    title: data.message,
                    text: `تم تقديم عرضك الجديد بقيمة ${Number(newAmount).toLocaleString('en-US')} ريال.`,
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-[32px]' }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'عذراً',
                    text: data.message,
                    customClass: { popup: 'rounded-[32px]' }
                });
            }
        });
    };

    // ──────────────────────────────────────────────────────────
    // POPUP: Show details modal
    // ──────────────────────────────────────────────────────────
    window.showDetailsModal = function(bidId) {
        fetch(`/bids-offers/${bidId}/details`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const d = res.data;
                document.getElementById('detAdTitle').innerText = d.ad.title;
                document.getElementById('detAdCategory').innerText = d.ad.category;
                document.getElementById('detBuyerName').innerText = d.buyer.name;
                document.getElementById('detBuyerType').innerText = d.buyer.account_type_lbl;
                document.getElementById('detBuyerPhone').innerText = d.buyer.phone || 'غير مدرج';
                
                let statusHtml = '';
                if (d.status === 'accepted') {
                    statusHtml = '<span style="color: #15803D; font-weight:900;">تم الاتفاق والقبول 🎉</span>';
                } else if (d.status === 'rejected') {
                    statusHtml = '<span style="color: #B91C1C; font-weight:900;">لم يتم الاتفاق (مرفوض) ❌</span>';
                } else {
                    statusHtml = 'جاري التفاوض';
                }
                document.getElementById('detStatus').innerHTML = statusHtml;
                document.getElementById('detAmount').innerText = `${Number(d.amount).toLocaleString()} ريال سعودي`;
                document.getElementById('detDate').innerText = d.created_at;
                
                document.getElementById('detailsModal').style.display = 'flex';
            }
        });
    };

    window.closeDetailsModal = function(event) {
        if (event.target === document.getElementById('detailsModal')) {
            document.getElementById('detailsModal').style.display = 'none';
        }
    };

    // Restore Filter Visibility Preference on startup
    document.addEventListener('DOMContentLoaded', () => {
        const filterVisible = localStorage.getItem('boFilterVisible');
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('myAdsGridContent');
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
