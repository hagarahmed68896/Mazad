<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>البحث | مزاد</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Cairo:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1A73E8',
                    }
                }
            }
        }
    </script>

    <!-- Leaflet (Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        body { font-family: 'Cairo', 'Outfit', sans-serif; background-color: #F8FAFC; }
        .container {
            max-width: 100% !important;
            padding-left: 64px !important;
            padding-right: 64px !important;
        }
        @media (max-width: 1024px) {
            .container { padding-left: 24px !important; padding-right: 24px !important; }
        }

        /* ===== Pagination ===== */
        .mazad-pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .pagination-list {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
        }
        .page-item .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            border-radius: 0.625rem;
            border: 1.5px solid #E2E8F0;
            background: #fff;
            color: #334155;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
        }
        .page-item .page-link:hover {
            background: #F1F5F9;
            border-color: #C8A96E;
            color: #C8A96E;
        }
        .page-item.active .page-link {
            background: linear-gradient(135deg, #C8A96E, #A0793A);
            border-color: #C8A96E;
            color: #fff;
            box-shadow: 0 4px 12px rgba(200, 169, 110, 0.35);
        }
        .page-item.disabled .page-link {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }
        .page-item .page-link.page-nav {
            font-size: 0.8rem;
        }
        .page-item .page-link.page-dots {
            border: none;
            background: transparent;
            color: #94A3B8;
            cursor: default;
        }

        /* ===== Mobile Responsive Overrides ===== */
        @media (max-width: 768px) {
            html, body {
                overflow-x: hidden;
                width: 100%;
                position: relative;
            }

            * {
                box-sizing: border-box !important;
            }

            /* Fix Container Padding on Mobile */
            .container {
                padding: 0 0.75rem !important;
                width: 100% !important;
                max-width: 100vw !important;
            }

            /* Prevent categories-nav from overlapping search header */
            .categories-nav {
                position: relative !important;
                top: auto !important;
                padding: 0.25rem 0 !important;
                width: 100% !important;
            }
            
            .category-pills {
                padding: 0 0.75rem !important;
                gap: 0.5rem !important;
            }
            .category-pill {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.8rem !important;
            }

            /* Search Page Header */
            .search-page-header {
                padding: 1rem 0 0.75rem !important;
                margin-top: 0 !important;
            }
            .search-page-header .page-title {
                font-size: 1.25rem !important;
                margin-top: 0.25rem !important;
            }

            /* Search Action Bar */
            .search-action-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.75rem !important;
                padding: 0.75rem !important;
                margin-bottom: 1rem !important;
                width: 100% !important;
                border-radius: 10px !important;
                overflow: visible !important; /* Changed to visible to allow dropdowns if any */
            }

            .search-input-group {
                max-width: 100% !important;
                width: 100% !important;
                order: 1 !important;
            }

            .search-bar-input {
                padding-right: 2.5rem !important;
                padding-left: 0.75rem !important;
                font-size: 0.85rem !important;
                height: 42px !important;
            }

            .search-bar-icon {
                right: 1rem !important;
                font-size: 0.9rem !important;
            }

            .action-buttons-group {
                width: 100% !important;
                order: 2 !important;
                display: flex !important;
                justify-content: flex-start !important;
                gap: 0.4rem !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                padding-bottom: 2px !important;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            .action-buttons-group::-webkit-scrollbar { display: none; }

            .action-btn {
                white-space: nowrap !important;
                padding: 0 0.6rem !important;
                font-size: 0.75rem !important;
                flex-shrink: 0 !important;
                height: 36px !important;
                gap: 0.3rem !important;
                border-radius: 8px !important;
            }
            .action-btn i { font-size: 0.85rem !important; }

            .view-btn {
                width: 36px !important;
                height: 36px !important;
                flex-shrink: 0 !important;
                font-size: 0.9rem !important;
                border-radius: 8px !important;
            }

            /* Results Summary */
            .results-summary {
                padding: 0 0.75rem !important;
                font-size: 0.85rem !important;
                margin-bottom: 0.75rem !important;
            }

            /* Empty State */
            .empty-search-state {
                padding: 2.5rem 1rem !important;
                margin: 0 !important;
                width: 100% !important;
                border-radius: 12px !important;
            }
            .empty-search-state h3 {
                font-size: 1rem !important;
            }
            .empty-icon-wrapper i {
                font-size: 2.5rem !important;
            }
            
            /* Navbar Mobile fixes */
            .navbar .container {
                padding: 0 0.5rem !important;
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 0.25rem !important;
            }
            .nav-right-visual {
                gap: 0.25rem !important;
                flex-shrink: 1 !important;
                min-width: 0 !important;
            }
            .logo svg { height: 22px !important; width: auto !important; }
            
            .location-picker.lg-location {
                padding: 0.2rem 0.4rem !important;
                font-size: 0.65rem !important;
                margin-right: 0 !important;
                gap: 0.2rem !important;
                background: #F1F5F9 !important;
                border-radius: 6px !important;
                flex-shrink: 1 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                max-width: 100px !important;
            }
            .location-picker.lg-location i { font-size: 0.6rem !important; }
            .location-picker.lg-location .flag-icon { width: 14px !important; }

            .nav-left-visual {
                gap: 0.2rem !important;
                flex-shrink: 0 !important;
            }

            .guest-actions {
                gap: 0.25rem !important;
            }
            .guest-actions .btn {
                padding: 0.3rem 0.5rem !important;
                font-size: 0.65rem !important;
                height: 32px !important;
                display: flex !important;
                align-items: center !important;
            }

            /* Navbar Search Fix on Mobile */
            .search-container {
                position: relative !important;
                display: flex !important;
                align-items: center !important;
            }
            #navbarSearchInput {
                transition: all 0.3s ease !important;
            }
            
            /* Target expanded state (JS sets 180px) */
            #navbarSearchInput[style*="width: 180px"] {
                width: 100px !important; /* Cap expansion on mobile */
                opacity: 1 !important;
                padding: 0.3rem 0.5rem !important;
                background: white !important;
            }

            .nav-icon-link {
                width: 32px !important;
                height: 32px !important;
            }
            .nav-icon-link svg {
                width: 18px !important;
                height: 18px !important;
            }
        @media (max-width: 768px) {
            /* ... */
        }
        .fa-heart.fa-solid { color: #EF4444 !important; }
        .search-container-grid.sidebar-hidden {
            grid-template-columns: 1fr !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    @include('partials.navbar')
    @include('partials.sidebar')
    @include('partials.categories-bar')

    <div class="search-page-header">
        <div class="container">
            <nav class="breadcrumbs">
                <a href="{{ route('home') }}">الرئيسية</a>
                <i class="fa-solid fa-chevron-left"></i>
                <span>البحث</span>
            </nav>
            <h1 class="page-title">البحث</h1>
        </div>
    </div>

    <main id="searchContainer" class="container search-container-grid sidebar-hidden">
        <!-- Sidebar Filters -->
        <aside class="search-sidebar hidden" id="searchSidebar">
            <div class="filter-card">
                <div class="filter-header">
                    <h3>تصفية النتائج</h3>
                    <button class="reset-btn" onclick="window.location.href='{{ route('location.clear') }}'">إعادة تعيين</button>
                </div>

                <form id="filterForm" action="{{ route('search') }}" method="GET">
                    <!-- Search Keyword (Hidden in sidebar, but kept in URL) -->
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="view" value="{{ request('view', 'grid') }}">

                    <!-- الموقع (City) -->
                    <div class="filter-group">
                        <div class="filter-group-header" onclick="toggleFilter(this)">
                            <span>الموقع</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content active">
                            <div class="location-filter-wrapper">
                                <button type="button" class="location-icon-btn" onclick="handleLocationClick()">
                                    <i class="fa-solid fa-location-dot"></i>
                                </button>
                                <select name="city" class="filter-select" onchange="this.form.submit()">
                                    <option value="">حدد الموقع</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $currentAddr = Auth::check() ? Auth::user()->address : session('user_address');
                            @endphp
                            @if($currentAddr)
                                <div class="current-location-status">
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                    <span>نتائج قريبة من: <strong>{{ \Illuminate\Support\Str::limit($currentAddr, 30) }}</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- نوع الاعلان -->
                    <div class="filter-group">
                        <div class="filter-group-header" onclick="toggleFilter(this)">
                            <span>نوع الاعلان</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content active">
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
                                    <input type="radio" name="type" value="direct_purchase" {{ request('type') == 'direct_purchase' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>شراء فوري</span>
                                </label>
                                <label class="filter-pill-label">
                                    <input type="radio" name="type" value="tender" {{ request('type') == 'tender' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>مناقصات</span>
                                </label>
                            </div>

                            <!-- Sub-filters for Auction -->
                            @if(request('type') == 'auction' || request('type') == 'electronic_auction' || request('type') == 'hybrid_auction')
                                <div class="auction-sub-filters" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed #E2E8F0;">
                                    <span style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.75rem;">نوع المزاد</span>
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
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                                            <input type="checkbox" name="is_enfaz" value="1" {{ request('is_enfaz') ? 'checked' : '' }} onchange="this.form.submit()" style="width: 18px; height: 18px;">
                                            <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-main);">عن طريق إنفاذ</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dynamic Category Properties -->
                    @if(isset($activeCategory))
                        <div class="active-category-info" style="background: #E8F1FE; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(26, 115, 232, 0.1);">
                            <div style="width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.1rem;">
                                <i class="fa-solid fa-tags"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.75rem; color: #64748B; font-weight: 700;">الفئة المختارة</span>
                                <span style="display: block; font-size: 1rem; color: var(--primary-color); font-weight: 800;">{{ $activeCategory->name }}</span>
                            </div>
                            <a href="{{ request()->fullUrlWithQuery(['category' => null, 'props' => null]) }}" style="margin-right: auto; color: #EF4444; font-size: 0.9rem;" title="إلغاء الفئة"><i class="fa-solid fa-circle-xmark"></i></a>
                        </div>

                        @if($activeCategory->properties->count() > 0)
                            @foreach($activeCategory->properties->sortBy('sort_order') as $prop)
                                <div class="filter-group">
                                    <div class="filter-group-header" onclick="toggleFilter(this)">
                                        <span>{{ $prop->label }}</span>
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="filter-group-content active">
                                        @if($prop->input_type == 'select')
                                            <select name="props[{{ $prop->id }}]" class="filter-select" onchange="this.form.submit()">
                                                <option value="">{{ $prop->placeholder ?? 'الكل' }}</option>
                                                @foreach($prop->options ?? [] as $opt)
                                                    <option value="{{ $opt }}" {{ (request('props')[$prop->id] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($prop->input_type == 'radio' || $prop->input_type == 'checkbox')
                                            <div class="filter-options-list">
                                                @foreach($prop->options ?? [] as $opt)
                                                    <label class="filter-option-item">
                                                        <input type="{{ $prop->input_type }}" name="props[{{ $prop->id }}]{{ $prop->input_type == 'checkbox' ? '[]' : '' }}" value="{{ $opt }}" 
                                                            {{ in_array($opt, (array)(request('props')[$prop->id] ?? [])) ? 'checked' : '' }} onchange="this.form.submit()">
                                                        <span class="custom-check-pill">{{ $opt }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                             <input type="{{ $prop->input_type == 'price' ? 'number' : ($prop->input_type ?: 'text') }}" name="props[{{ $prop->id }}]" class="filter-input-text" placeholder="{{ $prop->placeholder }}" value="{{ is_array(request('props')[$prop->id] ?? '') ? '' : (request('props')[$prop->id] ?? '') }}" onchange="this.form.submit()">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif

                    <!-- حالة الاعلان -->
                    <div class="filter-group">
                        <div class="filter-group-header" onclick="toggleFilter(this)">
                            <span>حالة الاعلان</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content">
                            <div class="filter-pills">
                                <label class="filter-pill-label">
                                    <input type="radio" name="ad_status" value="active" {{ request('ad_status', 'active') == 'active' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>قائم</span>
                                </label>
                                <label class="filter-pill-label">
                                    <input type="radio" name="ad_status" value="upcoming" {{ request('ad_status') == 'upcoming' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>قادم</span>
                                </label>
                                <label class="filter-pill-label">
                                    <input type="radio" name="ad_status" value="finished" {{ request('ad_status') == 'finished' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>منتهي</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- الفئات -->
                    <div class="filter-group">
                        <div class="filter-group-header" onclick="toggleFilter(this)">
                            <span>تغيير الفئة</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content {{ !request('category') ? 'active' : '' }}">
                            <div class="filter-categories-list">
                                <label class="category-filter-item">
                                    <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="radio-custom"></span>
                                    <span class="cat-name">كل الفئات</span>
                                </label>
                                @foreach($categories as $cat)
                                    <label class="category-filter-item">
                                        <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="radio-custom"></span>
                                        <span class="cat-name">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <!-- المعلن -->
                    <div class="filter-group">
                        <div class="filter-group-header" onclick="toggleFilter(this)">
                            <span>المعلن</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content">
                            <div class="filter-pills">
                                <label class="filter-pill-label">
                                    <input type="radio" name="user_type" value="" {{ !request('user_type') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>الكل</span>
                                </label>
                                <label class="filter-pill-label">
                                    <input type="radio" name="user_type" value="individual" {{ request('user_type') == 'individual' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>أفراد</span>
                                </label>
                                <label class="filter-pill-label">
                                    <input type="radio" name="user_type" value="company" {{ request('user_type') == 'company' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span>شركات</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Search Results Content -->
        <section class="search-main-content">
            <!-- New Search Action Bar -->
            <div class="search-action-bar">
                <!-- Search Input Side (Right in RTL) -->
                <div class="flex items-center gap-3 flex-1 max-w-[400px]">
                    <button type="button" onclick="toggleSearchSidebar()" class="action-btn" style="width: 42px; height: 42px; min-width: 42px; justify-content: center; padding: 0; background: white; border: 1.5px solid #E2E8F0; border-radius: 10px; color: #64748B;">
                        <i class="fa-solid fa-sliders"></i>
                    </button>
                    <form action="{{ route('search') }}" method="GET" class="search-input-group" style="flex: 1;">
                        @foreach(request()->except('q', 'page', 'category', 'props') as $key => $value)
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
                        <div style="position: relative; width: 100%;">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث عن..." class="search-bar-input" style="width: 100%;">
                            <i class="fa-solid fa-magnifying-glass search-bar-icon" style="position: absolute; top: 50%; transform: translateY(-50%); right: 1rem; left: auto; color: #94A3B8;"></i>
                        </div>
                    </form> 
                </div>

                <!-- Action Buttons Side (Left in RTL) -->
                <div class="action-buttons-group">
                    <div class="sort-dropdown-container" onclick="this.classList.toggle('active')">
                        <button class="action-btn sort-btn">
                            <i class="fa-solid fa-arrow-down-wide-short"></i>
                            <span>الترتيب</span>
                        </button>
                        <div class="sort-options">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'relevant']) }}" class="{{ request('sort') == 'relevant' || !request('sort') ? 'active' : '' }}">الأكثر صلة</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" class="{{ request('sort') == 'price_high' ? 'active' : '' }}">الأعلى سعراً</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" class="{{ request('sort') == 'price_low' ? 'active' : '' }}">الأقل سعراً</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'ending_soon']) }}" class="{{ request('sort') == 'ending_soon' ? 'active' : '' }}">تنتهي قريباً</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="{{ request('sort') == 'newest' ? 'active' : '' }}">الأحدث</a>
                        </div>
                    </div>
                    
                    <button type="button" class="action-btn map-view-toggle-btn" id="mapViewBtn" onclick="setView('map')">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>بحث بالخريطة</span>
                    </button>

                    <button type="button" class="view-btn {{ request('view', 'grid') == 'list' ? 'active' : '' }}" id="listViewBtn" onclick="setView('list')">
                        <i class="fa-solid fa-list-ul"></i>
                    </button>
                    <button type="button" class="view-btn {{ request('view', 'grid') == 'grid' ? 'active' : '' }}" id="gridViewBtn" onclick="setView('grid')">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </button>
                </div>
            </div>

            <div class="results-summary">
                @if($ads->total() > 0)
                    <span>وجدت {{ $ads->total() }} إعلان</span>
                    @if(request('q'))
                        لكلمة <span class="keyword">"{{ request('q') }}"</span>
                    @endif
                    @if(isset($activeCategory))
                        في فئة <span class="keyword">"{{ $activeCategory->name }}"</span>
                    @endif
                    @if(!request('q') && !isset($activeCategory))
                        لكلمة <span class="keyword">"الكل"</span>
                    @endif
                @else
                    لا يوجد نتائج لـ <span class="keyword">"{{ request('q') ?? (isset($activeCategory) ? $activeCategory->name : 'الكل') }}"</span>
                @endif
            </div>

            <!-- Ads Results Grid/List -->
            <!-- Map View Container -->
            <div id="mapViewContainer" class="map-view-container">
                <div class="map-search-overlay">
                    <div class="map-search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="mapSearchInput" placeholder="ابحث عن المزاد المناسب لك" value="{{ request('q') }}">
                    </div>
                </div>
                <div id="searchMap" style="width: 100%; height: 100%;"></div>
                
                <!-- Floating Ad Detail (Mobile Style) -->
                <div id="mapAdDetail" class="map-ad-detail-floating">
                    <!-- Content will be injected via JS -->
                </div>
            </div>

            <div class="ads-results-grid" id="adsResults">
                @forelse($ads as $ad)
                    @include('partials.ad-card', ['ad' => $ad])
                @empty
                    <div class="empty-search-state">
                        <div class="empty-icon-wrapper">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                            <div class="empty-mark">X</div>
                        </div>
                        <h3>لم يتم العثور على أي نتائج مطابقة لبحثك</h3>
                        <p>جرب استخدام كلمات أصغر أو قلل عوامل التصفية أو تأكد من كتابة الكلمات بشكل صحيح.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="search-pagination">
                {{ $ads->links() }}
            </div>
        </section>
    </main>

    @include('partials.footer')

    <!-- Auth Required Modal (Global) -->
    <div id="authRequiredModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
        <div class="bg-white w-full max-w-sm rounded-[28px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.25)] animate-in fade-in zoom-in duration-300" dir="rtl">
            <!-- Header -->
            <div class="relative bg-gradient-to-br from-primary to-blue-700 p-8 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                    <i class="fa-solid fa-lock text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-white">تسجيل الدخول مطلوب</h3>
                <p class="text-blue-100 text-sm font-medium mt-1">يجب تسجيل الدخول أولاً للمتابعة</p>
                <button onclick="hideAuthModal()" class="absolute top-4 left-4 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <!-- Body -->
            <div class="p-6 space-y-3">
                <a href="{{ route('login') }}" class="block w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all text-center text-lg shadow-lg shadow-blue-200">
                    <i class="fa-solid fa-right-to-bracket ml-2"></i> تسجيل الدخول
                </a>
            </div>
        </div>
    </div>

    <!-- Location Modal -->
    <div id="locationModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="document.getElementById('locationModal').style.display='none'">&times;</span>
            @auth
            <h2 style="margin-bottom: 1.5rem; font-weight: 800;">حدد موقعك</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">اختر المدينة لعرض الإعلانات القريبة منك</p>
            
            <div class="map-container" id="locationMap">
                <!-- Map will be initialized here -->
            </div>
            
            <button id="confirmLocationBtn" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 1rem;" onclick="confirmLocation()">تأكيد هذا الموقع</button>
            @else
            <div style="padding: 1rem; text-align:center;">
                <i class="fa-solid fa-user-lock" style="font-size: 3rem; color: #1A73E8; margin-bottom: 1.5rem;"></i>
                <h2 style="margin-bottom: 1rem;">يرجى تسجيل الدخول أولاً</h2>
                <p style="color: #64748B; margin-bottom: 2rem;">يجب أن تكون مسجلاً لدينا لتتمكن من تحديد عنوانك واختيار مدينتك بدقة.</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 0.75rem 2rem;">تسجيل الدخول</a>
                    <button class="btn btn-outline" onclick="document.getElementById('locationModal').style.display='none'" style="padding: 0.75rem 2rem;">إلغاء</button>
                </div>
            </div>
            @endauth
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('searchContainer');
            const sidebar = document.getElementById('searchSidebar');
            if (localStorage.getItem('searchSidebarOpen') === 'true') {
                sidebar.classList.remove('hidden');
                container.classList.remove('sidebar-hidden');
            }
        });

        function toggleSearchSidebar() {
            const container = document.getElementById('searchContainer');
            const sidebar = document.getElementById('searchSidebar');
            sidebar.classList.toggle('hidden');
            container.classList.toggle('sidebar-hidden');
            localStorage.setItem('searchSidebarOpen', !sidebar.classList.contains('hidden'));
        }

        function toggleFilter(header) {
            const group = header.parentElement;
            const content = group.querySelector('.filter-group-content');
            content.classList.toggle('active');
            const icon = header.querySelector('i');
            icon.classList.toggle('fa-chevron-up');
            icon.classList.toggle('fa-chevron-down');
        }

        function setView(view) {
            const grid = document.getElementById('adsResults');
            const mapContainer = document.getElementById('mapViewContainer');
            const gridBtn = document.getElementById('gridViewBtn');
            const listBtn = document.getElementById('listViewBtn');
            const mapBtn = document.getElementById('mapViewBtn');
            const viewInput = document.querySelector('input[name="view"]');
            
            // Reset all
            grid.classList.remove('list-view', 'hidden');
            mapContainer.classList.remove('active');
            gridBtn.classList.remove('active');
            listBtn.classList.remove('active');
            mapBtn.classList.remove('active');

            if (viewInput) viewInput.value = view;
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            window.history.pushState({}, '', url);

            if (view === 'list') {
                grid.classList.add('list-view');
                listBtn.classList.add('active');
            } else if (view === 'map') {
                grid.classList.add('hidden');
                mapContainer.classList.add('active');
                mapBtn.classList.add('active');
                initSearchMap(); 
            } else {
                gridBtn.classList.add('active');
            }
        }

        let searchMapInstance = null;
        let markersClusterGroup = null;

        function initSearchMap() {
            if (searchMapInstance) {
                searchMapInstance.invalidateSize();
                return;
            }

            // Default center (Saudi Arabia)
            searchMapInstance = L.map('searchMap', { zoomControl: false }).setView([24.7136, 46.6753], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(searchMapInstance);

            // Custom Cluster Icon
            markersClusterGroup = L.markerClusterGroup({
                iconCreateFunction: function(cluster) {
                    return L.divIcon({
                        html: `<span>${cluster.getChildCount()}</span>`,
                        className: 'custom-marker-cluster',
                        iconSize: L.point(44, 44)
                    });
                },
                polygonOptions: {
                    fillColor: '#1A73E8',
                    color: '#1A73E8',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.1
                }
            });

            const ads = @json($ads->items());
            
            ads.forEach(ad => {
                const lat = parseFloat(ad.lat);
                const lng = parseFloat(ad.lng);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const customIcon = L.divIcon({
                        className: 'custom-marker-single',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });

                    const marker = L.marker([lat, lng], { icon: customIcon });
                    
                    marker.on('click', (e) => {
                        // Highlight selected marker
                        document.querySelectorAll('.custom-marker-single').forEach(el => el.classList.remove('selected'));
                        e.target.getElement().classList.add('selected');
                        
                        showFloatingDetail(ad);
                        
                        // Center map on marker slightly offset to show floating card
                        searchMapInstance.panTo([lat - 0.02, lng]);
                    });

                    markersClusterGroup.addLayer(marker);
                }
            });

            searchMapInstance.addLayer(markersClusterGroup);

            if (markersClusterGroup.getLayers().length > 0) {
                searchMapInstance.fitBounds(markersClusterGroup.getBounds().pad(0.1));
            }

            // Close floating card on map click
            searchMapInstance.on('click', (e) => {
                if (e.originalEvent.target.id === 'searchMap') {
                    hideFloatingDetail();
                }
            });

            // Map search input logic
            const mapSearchInput = document.getElementById('mapSearchInput');
            if (mapSearchInput) {
                mapSearchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        window.location.href = `{{ route('search') }}?q=${encodeURIComponent(mapSearchInput.value)}&view=map`;
                    }
                });
            }
        }

        function showFloatingDetail(ad) {
            const detailBox = document.getElementById('mapAdDetail');
            if (!detailBox) return;

            const formatPrice = (p) => new Intl.NumberFormat().format(p);
            
            detailBox.innerHTML = `
                <div class="map-p-card" style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); direction: rtl; text-align: right; font-family: 'Cairo', sans-serif;">
                    <div style="position: relative; height: 180px;">
                        <img src="${ad.image ? '{{ asset('') }}' + ad.image : '{{ asset('images/laptop.png') }}'}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 15px; right: 15px; display: flex; flex-direction: column; gap: 6px;">
                            ${ad.is_featured ? '<span style="background: #EF4444; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);">مميز</span>' : ''}
                            <span style="background: #D4B05B; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; box-shadow: 0 4px 10px rgba(212, 176, 91, 0.3);">إلكتروني</span>
                            <span style="background: #10B981; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);">قائم</span>
                        </div>
                        <button style="position: absolute; top: 15px; left: 15px; background: white; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748B; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                    </div>
                    <div style="padding: 20px;">
                        <span style="color: #1A73E8; background: #E8F1FE; padding: 4px 15px; border-radius: 10px; font-size: 0.8rem; font-weight: 800;">${ad.category ? ad.category.name : 'عقارات'}</span>
                        <h4 style="margin: 12px 0; font-size: 1.25rem; font-weight: 800; color: #1E293B;">${ad.title}</h4>
                        <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 15px;"><i class="fa-solid fa-location-dot" style="color: #1A73E8; margin-left: 5px;"></i> ${ad.address || (ad.city ? ad.city.name : 'السعودية')}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <span style="font-size: 0.95rem; color: #64748B;">السعر الحالي:</span>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-money-bill-wave" style="color: #1A73E8;"></i>
                                <span style="color: #1A73E8; font-weight: 900; font-size: 1.3rem;">${formatPrice(ad.price)} <small style="font-size: 0.8rem;">ر.س</small></span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; font-size: 0.9rem; color: #64748B; border-top: 1px solid #F1F5F9; padding-top: 15px;">
                            <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-gavel"></i> ${ad.bids_count || 50} مزايدة</span>
                            <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-eye"></i> 50 مشاهدة</span>
                        </div>
                        <a href="/ads/${ad.id}" style="display: block; background: #1A73E8; color: white; text-align: center; padding: 14px; border-radius: 15px; text-decoration: none; font-size: 1.1rem; font-weight: 800; transition: all 0.3s; box-shadow: 0 4px 15px rgba(26, 115, 232, 0.3);">زايد الآن</a>
                    </div>
                </div>
            `;
            detailBox.classList.add('active');
        }

        function hideFloatingDetail() {
            const detailBox = document.getElementById('mapAdDetail');
            if (detailBox) detailBox.classList.remove('active');
            document.querySelectorAll('.custom-marker-single').forEach(el => el.classList.remove('selected'));
        }

        let locationMapInstance = null;
        let currentLocationMarker = null;

        function handleLocationClick() {
            const modal = document.getElementById('locationModal');
            if (modal) {
                modal.style.display = 'flex';
                
                @auth
                // Small delay to ensure modal is visible for Leaflet
                setTimeout(() => {
                    if (!locationMapInstance) {
                        locationMapInstance = L.map('locationMap').setView([24.7136, 46.6753], 6);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(locationMapInstance);
                        
                        currentLocationMarker = L.marker([24.7136, 46.6753], { draggable: true }).addTo(locationMapInstance);
                        
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(pos => {
                                const lat = pos.coords.latitude;
                                const lng = pos.coords.longitude;
                                locationMapInstance.setView([lat, lng], 13);
                                currentLocationMarker.setLatLng([lat, lng]);
                            });
                        }
                    } else {
                        locationMapInstance.invalidateSize();
                    }
                }, 300);
                @endauth
            }
        }

        function confirmLocation() {
            const btn = document.getElementById('confirmLocationBtn');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري استنتاج العنوان...';
                btn.disabled = true;
            }

            if (!currentLocationMarker) return;
            const pos = currentLocationMarker.getLatLng();
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}&accept-language=ar`)
                .then(res => res.json())
                .then(data => {
                    let cityOnly = "السعودية";
                    if (data.address) {
                        const addr = data.address;
                        cityOnly = addr.city || addr.town || addr.village || addr.state || "السعودية";
                    }
                    saveLocation(pos.lat, pos.lng, data.display_name || "السعودية", cityOnly);
                })
                .catch(err => {
                    saveLocation(pos.lat, pos.lng, "الرياض", "الرياض");
                });
        }

        function saveLocation(lat, lng, address, city) {
            const btn = document.getElementById('confirmLocationBtn');
            
            fetch('{{ route("location.update") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ latitude: lat, longitude: lng, address: address })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    window.location.reload();
                } else if (data) {
                    alert(data.message || "خطأ في حفظ الموقع");
                    if(btn) { btn.innerHTML = 'تأكيد هذا الموقع'; btn.disabled = false; }
                }
            })
            .catch(err => {
                console.error(err);
                if(btn) { btn.innerHTML = 'تأكيد هذا الموقع'; btn.disabled = false; }
            });
        }
        window.toggleSidebar = function() {
            const drawer = document.getElementById('sidebarDrawer');
            const overlay = document.getElementById('sidebarOverlay');
            if (drawer && overlay) {
                drawer.classList.toggle('active');
                overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
            }
        };

        window.toggleNavbarSearch = function() {
            // Already on search page, maybe just focus input
            document.querySelector('.main-search-input').focus();
        };

        // Countdown timers
        function updateCountdowns() {
            document.querySelectorAll('.p-countdown').forEach(el => {
                const endData = el.dataset.end;
                if (!endData) return;
                const endDate = new Date(endData).getTime();
                const now = new Date().getTime();
                const diff = endDate - now;
                const timerSpan = el.querySelector('.countdown-timer');
                if (!timerSpan) return;
                if (isNaN(endDate) || diff <= 0) { 
                    timerSpan.innerText = "منتهي"; 
                    return; 
                }
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                timerSpan.innerText = `${d} يوم : ${h} ساعة : ${m} دقيقة : ${s} ثانية`;
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const initialView = "{{ request('view', 'grid') }}";
            if (initialView !== 'grid') {
                setView(initialView);
            }
            
            setInterval(updateCountdowns, 1000);
            updateCountdowns();
        });
        // Global Auth Guard
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

        function requireAuth(callback) {
            if (isLoggedIn) {
                if (typeof callback === 'function') callback();
            } else {
                showAuthModal();
            }
        }

        function showAuthModal() {
            const modal = document.getElementById('authRequiredModal');
            if(modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function hideAuthModal() {
            const modal = document.getElementById('authRequiredModal');
            if(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        }

        // Global Wishlist Logic
        function toggleGlobalWishlist(event, adId) {
            const icons = document.querySelectorAll(`.wishlist-icon-${adId}`);
            
            fetch(`/wishlist/toggle/${adId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (icons.length > 0) {
                        icons.forEach(icon => {
                            if (data.action === 'added') {
                                icon.classList.remove('fa-regular');
                                icon.classList.add('fa-solid');
                                icon.style.color = '#EF4444';
                            } else {
                                icon.classList.remove('fa-solid');
                                icon.classList.add('fa-regular');
                                icon.style.color = '';
                            }
                        });
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 1500,
                        position: 'top-end',
                        toast: true
                    });
                }
            })
            .catch(error => console.error('Wishlist Error:', error));
        }

        window.onclick = function(event) {
            const locModal = document.getElementById('locationModal');
            const authModal = document.getElementById('authRequiredModal');
            if (event.target == locModal) locModal.style.display = 'none';
            if (event.target == authModal) hideAuthModal();
        }
    </script>
    @include('partials.account-type-modal')
</body>
</html>
