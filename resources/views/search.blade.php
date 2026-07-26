<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($activeCategory) ? $activeCategory->name . ' | تمت' : 'البحث | تمت' }}</title>

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
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <style>
        .glowing-circle-marker {
            background: rgba(26, 115, 232, 0.15) !important;
            border: 2px dashed #1A73E8 !important;
            border-radius: 50% !important;
            box-shadow: 0 0 10px rgba(26, 115, 232, 0.5) !important;
            transition: all 0.2s ease-in-out;
        }
        .glowing-circle-marker.selected {
            background: rgba(26, 115, 232, 0.3) !important;
            border: 2px solid #1A73E8 !important;
            box-shadow: 0 0 15px rgba(26, 115, 232, 0.8) !important;
        }
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
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.25);
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
        
        .fa-heart.fa-solid { color: #EF4444 !important; }
        
        .fa-heart.fa-solid { color: #EF4444 !important; }
        
        .active-pill {
            background: #1A73E8 !important;
            color: #fff !important;
        }
        
        /* Restore Original Layout */
        .search-container-grid {
            display: grid !important;
            grid-template-columns: 320px 1fr !important;
            gap: 2.5rem !important;
            width: 100% !important;
            max-width: 100% !important;
            background: transparent !important;
            margin-bottom: 4rem !important;
            padding: 0 64px !important;
        }

        .search-sidebar {
            width: 320px !important;
        }

        .search-main-content {
            min-width: 0 !important;
        }

        /* Results Grid - 3 Columns */
        .ads-results-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 2rem !important;
            width: 100% !important;
        }

        .ads-results-grid.list-view {
            grid-template-columns: 1fr !important;
        }

        /* Fix Ad Card Width */
        .ads-results-grid .p-ad-card {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            margin: 0 !important;
        }

        /* Action Bar Styling */
        .search-action-bar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            background: white !important;
            padding: 1.25rem 1.5rem !important;
            border-radius: 20px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
            margin-bottom: 2.5rem !important;
            gap: 2rem !important;
        }

        .search-input-group {
            flex: 1 !important;
            max-width: 500px !important;
            display: flex !important;
            align-items: center !important;
        }

        .action-buttons-group {
            display: flex !important;
            align-items: center !important;
            gap: 1rem !important;
        }

        @media (max-width: 1280px) {
            .ads-results-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 1024px) {
            .search-container-grid {
                grid-template-columns: 1fr !important;
                padding: 0 24px !important;
            }
            .search-sidebar {
                display: none !important; /* Hide sidebar on mobile/tablet to keep it clean */
            }
        }

        @media (max-width: 768px) {
            .ads-results-grid {
                grid-template-columns: 1fr !important;
            }
            .search-action-bar {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            .search-input-group {
                max-width: 100% !important;
            }
        }

        .results-summary {
            font-weight: 800 !important;
            color: #64748B !important;
            margin-bottom: 2rem !important;
            font-size: 1.1rem !important;
        }

        .results-summary .keyword {
            color: #1A73E8 !important;
        }
        .profile-dropdown.active {
            display: block !important;
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
                @if(isset($activeCategory))
                    <a href="{{ route('categories.all') }}">أقسام المنصة</a>
                    <i class="fa-solid fa-chevron-left"></i>
                    <span>{{ $activeCategory->name }}</span>
                @else
                    <span>البحث</span>
                @endif
            </nav>
            <h1 class="page-title">{{ isset($activeCategory) ? $activeCategory->name : 'البحث' }}</h1>
        </div>
    </div>

    <main id="searchContainer" class="container search-container-grid">
        <!-- Sidebar Filters -->
        <aside class="search-sidebar" id="searchSidebar">
            <div class="filter-card">
                <div class="filter-header">
                    <h3>تصفية النتائج</h3>
                    <button class="reset-btn" onclick="window.location.href='{{ route('location.clear') }}'">إعادة تعيين</button>
                </div>

                <form id="filterForm" action="{{ Route::currentRouteName() == 'categories.show' ? route('categories.show', $activeCategory->id) : route('search') }}" method="GET">
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
                            <!-- City select -->
                            <div class="location-filter-wrapper mb-3" style="margin-bottom: 0.75rem;">
                                <select name="city_id" id="citySelect" class="filter-select" onchange="filterDistrictsAndSubmit()" style="width: 100%;">
                                    <option value="">كل المدن</option>
                                    @foreach($regions as $reg)
                                        <option value="{{ $reg->id }}" {{ request('city_id') == $reg->id ? 'selected' : '' }}>{{ $reg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Neighborhood/District select -->
                            <div class="location-filter-wrapper mb-3" style="display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.75rem;">
                                <select name="district_id" id="districtSelect" class="filter-select" onchange="this.form.submit()" style="width: 100%;">
                                    <option value="">كل الأحياء</option>
                                    @foreach($cities as $dist)
                                        <option value="{{ $dist->id }}" data-region-id="{{ $dist->region_id }}" {{ request('district_id') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $currentAddr = Auth::check() ? Auth::user()->address : null;
                                if (request()->filled('city_id')) {
                                    $reg = \App\Models\Region::find(request('city_id'));
                                    if ($reg) {
                                        $currentAddr = $reg->name;
                                        if (request()->filled('district_id')) {
                                            $dist = \App\Models\City::find(request('district_id'));
                                            if ($dist) {
                                                $currentAddr .= '، ' . $dist->name;
                                            }
                                        }
                                    }
                                }
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
                                    <span class="{{ request('type') == 'auction' || request('type') == 'electronic_auction' || request('type') == 'hybrid_auction' ? 'active-pill' : '' }}">مزادات</span>
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
                            <a href="{{ Route::currentRouteName() == 'categories.show' ? route('search', request()->except(['category', 'props'])) : request()->fullUrlWithQuery(['category' => null, 'props' => null]) }}" style="margin-right: auto; color: #EF4444; font-size: 0.9rem;" title="إلغاء الفئة"><i class="fa-solid fa-circle-xmark"></i></a>
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
                    <form action="{{ Route::currentRouteName() == 'categories.show' ? route('categories.show', $activeCategory->id) : route('search') }}" method="GET" class="search-input-group" style="flex: 1;">
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
            <h2 style="margin-bottom: 1.5rem; font-weight: 800;">حدد موقعك</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">اختر المدينة لعرض الإعلانات القريبة منك</p>
            
            <div class="map-container" id="locationMap">
                <!-- Map will be initialized here -->
            </div>
            
            <button id="confirmLocationBtn" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 1rem;" onclick="confirmLocation()">تأكيد هذا الموقع</button>
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
            // No longer using container.classList.toggle('sidebar-hidden') as we use flexbox now
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

            const ads = @json($ads->items());
            let selectedAdIsRealEstate = true;

            function isAdRealEstateJS(ad) {
                if (!ad.category) return false;
                const name = (ad.category.name || '').toLowerCase();
                return name.includes('عقار') || 
                       name.includes('عقارات') || 
                       name.includes('العقارات') || 
                       name.includes('real estate') || 
                       name.includes('realestate') || 
                       name.includes('property') || 
                       name.includes('properties');
            }

            function isAdCarsJS(ad) {
                if (!ad.category) return false;
                const name = (ad.category.name || '').toLowerCase();
                return name.includes('سيارات') || 
                       name.includes('سيارة') || 
                       name.includes('car') || 
                       name.includes('cars') || 
                       name.includes('vehicle') || 
                       name.includes('vehicles');
            }

            const hasNonRealEstate = ads.some(ad => !((ad.is_real_estate !== undefined) ? ad.is_real_estate : isAdRealEstateJS(ad)));
            const mapOptions = { zoomControl: false };
            if (hasNonRealEstate) {
                mapOptions.maxZoom = 13;
            }

            // Default center (Saudi Arabia)
            searchMapInstance = L.map('searchMap', mapOptions).setView([24.7136, 46.6753], 6);

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

            ads.forEach(ad => {
                const lat = parseFloat(ad.lat);
                const lng = parseFloat(ad.lng);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const isRealEstate = (ad.is_real_estate !== undefined) ? ad.is_real_estate : isAdRealEstateJS(ad);
                    const markerClass = isRealEstate ? 'custom-marker-single' : 'custom-marker-single glowing-circle-marker';
                    const markerSize = isRealEstate ? [22, 22] : [44, 44];
                    const markerAnchor = isRealEstate ? [11, 11] : [22, 22];

                    const customIcon = L.divIcon({
                        className: markerClass,
                        iconSize: markerSize,
                        iconAnchor: markerAnchor
                    });

                    const marker = L.marker([lat, lng], { icon: customIcon });
                    
                    marker.on('click', (e) => {
                        // Highlight selected marker
                        document.querySelectorAll('.custom-marker-single').forEach(el => el.classList.remove('selected'));
                        e.target.getElement().classList.add('selected');
                        
                        selectedAdIsRealEstate = isRealEstate;
                        showFloatingDetail(ad);
                        
                        // Center map on marker with conditional zoom level
                        if (isRealEstate) {
                            searchMapInstance.setView([lat - 0.01, lng], 15);
                        } else {
                            searchMapInstance.setView([lat - 0.02, lng], 13);
                        }
                    });

                    markersClusterGroup.addLayer(marker);
                }
            });

            searchMapInstance.addLayer(markersClusterGroup);

            if (markersClusterGroup.getLayers().length > 0) {
                searchMapInstance.fitBounds(markersClusterGroup.getBounds().pad(0.1));
            }

            // Force zoom limits on non-real-estate items to keep map vague
            searchMapInstance.on('zoomend', () => {
                if (!selectedAdIsRealEstate && searchMapInstance.getZoom() > 13) {
                    searchMapInstance.setZoom(13);
                }
            });

            // Close floating card on map click
            searchMapInstance.on('click', (e) => {
                if (e.originalEvent.target.id === 'searchMap') {
                    hideFloatingDetail();
                    selectedAdIsRealEstate = true; // Reset zoom limit when closing card
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
            
            const image = ad.image ? (ad.image.startsWith('http') ? ad.image : '/' + ad.image.replace(/^\//, '')) : null;
            const isCars = (ad.is_cars !== undefined) ? ad.is_cars : isAdCarsJS(ad);
            const imageSrc = image ? image : (isCars ? '/car_placeholder.svg' : null);
            
            let statusLabel = 'جاري';
            if (ad.type === 'hybrid_auction') {
                const bStatus = ad.bid_status || ad.status;
                if (bStatus === 'upcoming') statusLabel = 'لم تبدأ المزايدة';
                else if (bStatus === 'active') statusLabel = 'جاري';
                else if (bStatus === 'postponed') statusLabel = 'مؤجل';
                else if (bStatus === 'not_sold') statusLabel = 'لم يتم البيع';
                else if (bStatus === 'sold') statusLabel = 'تم البيع';
                else if (bStatus === 'finished') statusLabel = 'منتهي';
            } else {
                if (ad.status === 'active') statusLabel = 'قائم';
                else if (ad.status === 'upcoming') statusLabel = 'قادم';
                else statusLabel = 'منتهي';
            }

            let priceLabel = 'السعر الحالي';
            if (ad.type === 'direct_purchase') {
                priceLabel = 'سعر الشراء';
            } else if (ad.type === 'hybrid_auction') {
                priceLabel = 'السعر الابتدائي';
            }

            let typeLabel = 'إلكتروني';
            if (ad.type === 'electronic_auction') typeLabel = 'إلكتروني';
            else if (ad.type === 'hybrid_auction') typeLabel = 'هجين';
            else if (ad.type === 'direct_purchase') typeLabel = 'شراء فوري';
            else if (ad.type === 'tender') typeLabel = 'مناقصة';

            let bidTypeLabel = 'مزايدة';
            if (ad.type === 'direct_purchase') bidTypeLabel = 'عرض';
            else if (ad.type === 'tender') bidTypeLabel = 'مناقصة';

            detailBox.innerHTML = `
                <div class="p-ad-card" style="box-shadow: 0 12px 36px rgba(0,0,0,0.15); width: 330px; margin: 0; background: white; border-radius: 24px; overflow: hidden; border: 1px solid #E2E8F0;">
                    <div class="p-card-image" style="height: 180px; position: relative;">
                        <a href="/ads/${ad.id}" class="h-full w-full block bg-gray-50">
                            ${imageSrc ? `<img src="${imageSrc}" alt="${ad.title}" style="width: 100%; height: 100%; object-fit: ${image ? 'cover' : 'contain'}; ${image ? '' : 'padding: 1.5rem;'}">` : `<div class="flex items-center justify-center h-full w-full text-gray-300"><i class="fa-solid fa-image text-4xl"></i></div>`}
                        </a>
                        
                        <div class="p-badges">
                            ${ad.is_featured ? '<span class="p-badge-pill featured"><i class="fa-solid fa-fire"></i> مميز</span>' : ''}
                            <span class="p-badge-pill type">${typeLabel}</span>
                            <span class="p-badge-pill status">${statusLabel}</span>
                        </div>
                    </div>

                    <div class="p-card-body" style="padding: 1.25rem;">
                        <span class="p-category-pill">${ad.category ? ad.category.name : 'غير مصنف'}</span>
                        <a href="/ads/${ad.id}" style="text-decoration: none; color: inherit;">
                            <h3 class="p-ad-title" style="font-size: 1.15rem; font-weight: 800; margin: 0.5rem 0; height: 3.1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: #1E293B;">${ad.title}</h3>
                        </a>
                        
                        <div class="p-price-row" style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem;">
                            <span class="p-price-label" style="font-size: 0.85rem; color: #64748B; font-weight: 700;">${priceLabel}</span>
                            <div class="flex items-center gap-1 text-primary">
                                <span class="text-xl font-black">${formatPrice(ad.price)}</span>
                                <svg class="w-5 h-5 inline-block" viewBox="0 0 43 45" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block;"><path d="M41.5311 34.2238C42.2985 32.5248 42.8057 30.6803 43 28.7464L30.351 31.4333V26.268L41.5308 23.8951C42.2981 22.1962 42.8053 20.3516 42.9996 18.4177L30.3506 21.1024V2.52648C28.4124 3.6134 26.6911 5.06021 25.2919 6.76682V22.1767L20.2331 23.2506V0C18.2949 1.08654 16.5736 2.53374 15.1744 4.24034V24.3242L3.85537 26.7265C3.08804 28.4254 2.58044 30.27 2.38574 32.2039L15.1744 29.4894V35.9942L1.46886 38.9031C0.701532 40.602 0.194317 42.4466 0 44.3805L14.3458 41.3356C15.5136 41.093 16.5174 40.4034 17.1699 39.4544L19.8009 35.5586V35.5579C20.074 35.1548 20.2331 34.6689 20.2331 34.1454V28.4155L25.2919 27.3416V37.6721L41.5308 34.223L41.5311 34.2238Z" fill="currentColor"/></svg>
                            </div>
                        </div>

                        <div class="p-stats-row" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; border-top: 1px solid #F1F5F9; padding-top: 0.75rem;">
                            <div class="p-stat-box" style="display: flex; align-items: center; gap: 4px; font-size: 0.85rem; color: #64748B;">
                                <i class="fa-solid fa-eye"></i>
                                <span>${ad.views_count || 0} مشاهدة</span>
                            </div>
                            <div class="p-stat-box" style="display: flex; align-items: center; gap: 4px; font-size: 0.85rem; color: #64748B;">
                                <i class="fa-solid fa-gavel"></i>
                                <span>${ad.bids_count || 0} ${bidTypeLabel}</span>
                            </div>
                        </div>

                        <div class="p-card-footer" style="margin-top: 1.25rem;">
                            <a href="/ads/${ad.id}" class="btn btn-p-primary" style="display: block; width: 100%; text-align: center; text-decoration: none; padding: 0.75rem; border-radius: 12px; font-weight: 800;">
                                ${ad.type === 'direct_purchase' ? 'شراء' : ad.type === 'hybrid_auction' ? 'المشاركة في المزاد' : ad.type === 'tender' ? 'تقديم عرض' : 'زايد'}
                            </a>
                        </div>
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
                
                // Small delay to ensure modal is visible for Leaflet
                setTimeout(() => {
                    const savedLat = {{ Auth::check() ? (Auth::user()->latitude ?? 'null') : (session('user_latitude') ?? 'null') }};
                    const savedLng = {{ Auth::check() ? (Auth::user()->longitude ?? 'null') : (session('user_longitude') ?? 'null') }};
                    
                    if (!locationMapInstance) {
                        locationMapInstance = L.map('locationMap').setView([savedLat || 24.7136, savedLng || 46.6753], savedLat ? 13 : 6);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(locationMapInstance);
                        
                        currentLocationMarker = L.marker([savedLat || 24.7136, savedLng || 46.6753], { draggable: true }).addTo(locationMapInstance);
                        
                        if (!savedLat && navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(pos => {
                                const lat = pos.coords.latitude;
                                const lng = pos.coords.longitude;
                                if (lat >= 16 && lat <= 32.5 && lng >= 34 && lng <= 56) {
                                    locationMapInstance.setView([lat, lng], 13);
                                    currentLocationMarker.setLatLng([lat, lng]);
                                }
                            });
                        }
                    } else {
                        locationMapInstance.invalidateSize();
                    }
                }, 300);
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
                    const isSaudi = data.address && (data.address.country_code === 'sa' || (data.address.country && data.address.country.includes('السعودية')));
                    if (!isSaudi) {
                        alert("الرجاء اختيار موقع داخل المملكة العربية السعودية فقط.");
                        if(btn) { btn.innerHTML = 'تأكيد هذا الموقع'; btn.disabled = false; }
                        return;
                    }

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
                body: JSON.stringify({ latitude: lat, longitude: lng, address: address, city: city })
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
                
                if (drawer.classList.contains('active')) {
                    ['notificationDropdown', 'cartDropdown', 'profileDropdown', 'addAdDropdown', 'addAdDropdownGuest'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = 'none';
                    });
                }
            }
        };

        window.toggleNavbarSearch = function() {
            // Already on search page, maybe just focus input
            const mainSearch = document.querySelector('.main-search-input');
            if (mainSearch) mainSearch.focus();
        };

        window.toggleNotifications = function() {
            const dropdown = document.getElementById('notificationDropdown');
            if (!dropdown) return;
            const cartDropdown = document.getElementById('cartDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if (profileDropdown) {
                profileDropdown.style.display = 'none';
                profileDropdown.classList.remove('active');
            }
            if (cartDropdown) cartDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';
            
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        };

        window.toggleCart = function() {
            const dropdown = document.getElementById('cartDropdown');
            if (!dropdown) return;
            const notifDropdown = document.getElementById('notificationDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if (profileDropdown) {
                profileDropdown.style.display = 'none';
                profileDropdown.classList.remove('active');
            }
            if (notifDropdown) notifDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';
            
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        };

        // Countdown timers
        function updateCountdowns() {
            const containers = document.querySelectorAll('[data-end]');
            containers.forEach(container => {
                const endDateStr = container.getAttribute('data-end');
                if (!endDateStr) return;
                
                const endDate = new Date(endDateStr).getTime();
                const now = new Date().getTime();
                const diff = endDate - now;
                
                const timerEl = container.querySelector('.countdown-timer');
                if (!timerEl) return;
                
                if (diff <= 0) {
                    timerEl.innerHTML = '<span class="text-red-500 font-bold">منتهي</span>';
                    return;
                }
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                if (container.classList.contains('sidebar-countdown')) {
                    // Update specific spans for sidebar
                    const dEl = timerEl.querySelector('.days');
                    const hEl = timerEl.querySelector('.hours');
                    const mEl = timerEl.querySelector('.minutes');
                    const sEl = timerEl.querySelector('.seconds');
                    
                    if(dEl) dEl.innerText = days.toString().padStart(2, '0');
                    if(hEl) hEl.innerText = hours.toString().padStart(2, '0');
                    if(mEl) mEl.innerText = minutes.toString().padStart(2, '0');
                    if(sEl) sEl.innerText = seconds.toString().padStart(2, '0');
                } else {
                    // Standard text format for cards
                    timerEl.innerText = `${days}ي ${hours}س ${minutes}د ${seconds}ث`;
                }
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();

        window.filterDistrictsAndSubmit = function() {
            const citySelect = document.getElementById('citySelect');
            const districtSelect = document.getElementById('districtSelect');
            if (citySelect && districtSelect) {
                districtSelect.value = "";
                citySelect.form.submit();
            }
        };

        function updateDistrictDropdownOptions() {
            const citySelect = document.getElementById('citySelect');
            const districtSelect = document.getElementById('districtSelect');
            if (!citySelect || !districtSelect) return;

            const selectedCityId = citySelect.value;
            const options = districtSelect.querySelectorAll('option');

            options.forEach(opt => {
                const optRegionId = opt.getAttribute('data-region-id');
                if (!optRegionId) {
                    opt.style.display = "";
                    return;
                }

                if (!selectedCityId) {
                    opt.style.display = "";
                } else if (optRegionId == selectedCityId) {
                    opt.style.display = "";
                } else {
                    opt.style.display = "none";
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateDistrictDropdownOptions();

            const initialView = "{{ request('view', 'grid') }}";
            if (initialView !== 'grid') {
                setView(initialView);
            }
        });

        // Global Auth Guard
        // isLoggedIn is handled globally or at the end of the script

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

        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};

        function showLoginRequiredAlert() {
            Swal.fire({
                title: 'تسجيل الدخول مطلوب',
                text: 'يرجى تسجيل الدخول أولاً لتتمكن من إضافة إعلانك والوصول لكامل المميزات.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'تسجيل الدخول',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#1A73E8',
                cancelButtonColor: '#94A3B8',
                customClass: {
                    popup: 'rounded-[32px] p-8',
                    confirmButton: 'rounded-xl px-8 py-3 font-bold',
                    cancelButton: 'rounded-xl px-8 py-3 font-bold',
                    title: 'font-black text-2xl',
                    htmlContainer: 'font-bold text-gray-500'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    openAccountModal();
                }
            });
        }

        window.onclick = function(event) {
            const locModal = document.getElementById('locationModal');
            const authModal = document.getElementById('authRequiredModal');
            if (event.target == locModal) locModal.style.display = 'none';
            if (event.target == authModal) hideAuthModal();

            const addAdDropdown = document.getElementById('addAdDropdown');
            const addAdBtn = document.getElementById('addAdBtn');
            const addAdDropdownGuest = document.getElementById('addAdDropdownGuest');
            const addAdBtnGuest = document.getElementById('addAdBtnGuest');

            if (addAdDropdown && addAdBtn && !addAdDropdown.contains(event.target) && !addAdBtn.contains(event.target)) {
                addAdDropdown.style.display = 'none';
            }
            if (addAdDropdownGuest && addAdBtnGuest && !addAdDropdownGuest.contains(event.target) && !addAdBtnGuest.contains(event.target)) {
                addAdDropdownGuest.style.display = 'none';
            }

            const profileDropdown = document.getElementById('profileDropdown');
            const userProfileBtn = document.getElementById('userProfileBtn');
            if (profileDropdown && userProfileBtn && !profileDropdown.contains(event.target) && !userProfileBtn.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
        }

        window.toggleProfileDropdown = function() {
            const dropdown = document.getElementById('profileDropdown');
            if(!dropdown) return;
            
            const notifDropdown = document.getElementById('notificationDropdown');
            const cartDropdown = document.getElementById('cartDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if (notifDropdown) notifDropdown.style.display = 'none';
            if (cartDropdown) cartDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';
            
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
                dropdown.classList.remove('active');
            } else {
                dropdown.style.display = 'block';
                dropdown.classList.add('active');
            }
        };

        window.toggleAddAdDropdown = function() {
            const dropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            if(!dropdown) return;
            
            ['notificationDropdown', 'cartDropdown', 'profileDropdown'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            
            dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
        };
    </script>
    @include('partials.add-ad-modal')
    @include('partials.account-type-modal')
    @stack('scripts')
</body>
</html>
