@extends('layouts.main')

@section('title', 'إعلاناتي | تمت')

@push('styles')
<style>
    .my-ads-page {
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

    /* Override flex behavior of standard ad-card inside grid */
    .p-ad-card {
        flex: 1 1 auto !important;
        width: 100% !important;
        margin: 0 !important;
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

    /* List View Overrides for Ad Card - EXACTLY like search page */
    #myAdsContainer.list-view {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
    
    #myAdsContainer.list-view .p-ad-card {
        display: flex !important;
        height: 250px !important;
        background: white !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 20px !important;
        overflow: hidden !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        transition: all 0.3s ease !important;
    }

    #myAdsContainer.list-view .p-ad-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 12px 30px rgba(0,0,0,0.04) !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-card-image {
        width: 350px !important;
        height: 100% !important;
        flex-shrink: 0 !important;
        position: relative !important;
        border-radius: 0 !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-card-image img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-card-body {
        flex: 1 !important;
        padding: 1.5rem !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-category-pill {
        align-self: flex-start !important;
        width: fit-content !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-ad-title {
        font-size: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-price-row {
        margin-top: 0 !important;
    }

    #myAdsContainer.list-view .p-ad-card .p-card-footer {
        border-top: none !important;
        padding-top: 1rem !important;
        margin-top: auto !important;
        display: flex !important;
        justify-content: flex-start !important;
        align-items: center !important;
        gap: 1rem !important;
    }

    @media (max-width: 768px) {
        #myAdsContainer.list-view .p-ad-card {
            flex-direction: column !important;
            height: auto !important;
        }
        #myAdsContainer.list-view .p-ad-card .p-card-image {
            width: 100% !important;
            height: 200px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="my-ads-page">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <a href="{{ route('home') }}" class="breadcrumbs-pill">
            <span>الرئيسية</span>
            <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i>
            <span>إعلاناتي</span>
        </a>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h1 class="page-title !mb-0" style="margin-bottom: 0 !important;">إعلاناتي</h1>
            <a href="{{ route('ads.promote-page') }}" class="btn btn-p-primary flex items-center gap-2" style="background-color: #1A73E8; border-color: #1A73E8; color: white; border-radius: 12px; padding: 0.6rem 1.2rem; font-weight: 700; text-decoration: none;">
                <i class="fa-solid fa-rectangle-ad text-lg"></i>
                <span>ميز إعلاناتك</span>
            </a>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs-container">
            <a href="{{ route('ads.my', ['status' => 'all'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'all' ? 'active' : '' }}">الكل</a>
            <a href="{{ route('ads.my', ['status' => 'incomplete'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'incomplete' ? 'active' : '' }}">الإعلانات الغير مكتملة</a>
            <a href="{{ route('ads.my', ['status' => 'active'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'active' ? 'active' : '' }}">الإعلانات القائمة</a>
            <a href="{{ route('ads.my', ['status' => 'upcoming'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'upcoming' ? 'active' : '' }}">الإعلانات المستقبلية</a>
            <a href="{{ route('ads.my', ['status' => 'finished'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'finished' ? 'active' : '' }}">الإعلانات المنتهية</a>
        </div>

        <!-- Action / Search Bar -->
        <div class="search-action-bar flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            <!-- Right: Search input & Filter toggle -->
            <div class="flex items-center gap-3 flex-1 max-w-[560px]">
                <!-- Filter Toggle Button (Desktop & Mobile) -->
                <button type="button" class="action-btn active flex-shrink-0" id="filterToggleBtn" onclick="toggleMySidebar()" title="تصفية" style="height: 48px; border-radius: 16px;">
                    <i class="fa-solid fa-sliders"></i>
                </button>

                <form action="{{ route('ads.my') }}" method="GET" style="position: relative; width: 100%;">
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
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث في إعلاناتي..." class="search-bar-input">
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
            
            <!-- Right Pane: Sidebar Filters (placed first in HTML, rendering on the right in RTL) -->
            <aside class="lg:col-span-1 block" id="mySidebarPane">
                <div class="filter-card">
                    <div class="filter-header">
                        <h3>تصفية النتائج</h3>
                        <a href="{{ route('ads.my', ['status' => $status]) }}" class="reset-btn">إعادة تعيين</a>
                    </div>

                    <form id="filterForm" action="{{ route('ads.my') }}" method="GET" class="space-y-2">
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
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="tender" {{ request('type') == 'tender' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>مناقصات</span>
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

            <!-- Left Pane: Ads Grid (placed second in HTML, rendering on the left in RTL) -->
            <div class="lg:col-span-3 transition-all duration-300" id="myAdsGridContent">
                @if($ads->isEmpty())
                    <!-- Empty State -->
                    <div class="empty-state-card">
                        <div class="empty-illustration-container mx-auto mb-6" style="width: 150px; height: 150px; background: #F8FAFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid #F1F5F9;">
                            <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="25" y="15" width="46" height="58" rx="8" fill="#E2E8F0" stroke="#94A3B8" stroke-width="3" />
                                <line x1="35" y1="28" x2="55" y2="28" stroke="#94A3B8" stroke-width="3.5" stroke-linecap="round"/>
                                <line x1="35" y1="38" x2="61" y2="38" stroke="#94A3B8" stroke-width="3.5" stroke-linecap="round"/>
                                <line x1="35" y1="48" x2="50" y2="48" stroke="#94A3B8" stroke-width="3.5" stroke-linecap="round"/>
                                <circle cx="58" cy="58" r="23" fill="white" stroke="#64748B" stroke-width="4.5" />
                                <path d="M51 51L65 65M65 51L51 65" stroke="#334155" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M74 74L86 86" stroke="#64748B" stroke-width="5.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="empty-title">لا يوجد إعلانات تطابق البحث</h2>
                        <p class="empty-subtext">حاول تغيير خيارات التصفية أو أضف إعلاناً جديداً للبدء فوراً.</p>
                        <a href="{{ route('ads.create') }}" class="empty-btn">
                            <span>أضف إعلانك الأول</span>
                        </a>
                    </div>
                @else
                    <!-- Ads Grid List -->
                    <div id="myAdsContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 transition-all duration-300">
                        @foreach($ads as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-12 flex justify-center">
                        {{ $ads->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle sidebar pane visibility dynamically on both desktop and mobile
    window.toggleMySidebar = function() {
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('myAdsGridContent');
        const toggleBtn = document.getElementById('filterToggleBtn');
        
        if (!pane || !gridContent) return;
        
        if (pane.classList.contains('hidden')) {
            // Show Sidebar
            pane.classList.remove('hidden');
            pane.classList.add('block');
            gridContent.classList.remove('lg:col-span-4');
            gridContent.classList.add('lg:col-span-3');
            if (toggleBtn) toggleBtn.classList.add('active');
            localStorage.setItem('myAdsFilterVisible', 'true');
        } else {
            // Hide Sidebar
            pane.classList.remove('block');
            pane.classList.add('hidden');
            gridContent.classList.remove('lg:col-span-3');
            gridContent.classList.add('lg:col-span-4');
            if (toggleBtn) toggleBtn.classList.remove('active');
            localStorage.setItem('myAdsFilterVisible', 'false');
        }
    };

    // Toggle layout between Grid and List
    window.toggleAdsLayout = function(mode) {
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        const container = document.getElementById('myAdsContainer');
        
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
        
        // Store layout choice in localStorage
        localStorage.setItem('myAdsLayout', mode);
    };

    // Close sorting dropdown on click outside
    document.addEventListener('click', function(e) {
        const sortContainer = document.getElementById('mySortContainer');
        if (sortContainer && !sortContainer.contains(e.target)) {
            sortContainer.classList.remove('active');
        }
    });

    // Toggle filter groups inside sidebar
    window.toggleSidebarGroup = function(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('i');
        if (content) {
            content.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    };

    // Load saved preferences on startup
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Layout Preference
        const savedLayout = localStorage.getItem('myAdsLayout');
        if (savedLayout === 'list') {
            toggleAdsLayout('list');
        }

        // 2. Filter Visibility Preference
        const filterVisible = localStorage.getItem('myAdsFilterVisible');
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
