@extends('layouts.main')

@section('title', 'لوحة تحكم الشريك | مزاد')

@push('styles')
<style>
    .partner-banner {
        background: linear-gradient(135deg, #1A73E8 0%, #1557B0 100%) !important;
    }
    .partner-slide-item {
        transition: opacity 0.5s ease-in-out;
    }
    .partner-slide-item.active-slide {
        opacity: 1 !important;
        pointer-events: auto;
    }
    .partner-slide-item.hidden {
        opacity: 0 !important;
        pointer-events: none;
        display: flex !important;
    }
    .partner-ad-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.06) !important;
    }
    .partner-ad-card a:hover {
        background-color: #1557B0 !important;
    }
    .partner-ad-card button:hover {
        background-color: #E2E8F0 !important;
        color: #1E293B !important;
    }

    /* List View Styling */
    #adsContainer.list-view {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
    #adsContainer.list-view .partner-ad-card {
        flex-direction: row !important;
        height: 200px !important;
        align-items: stretch !important;
    }
    #adsContainer.list-view .ad-image-container {
        width: 280px !important;
        height: 100% !important;
        flex-shrink: 0 !important;
    }
    #adsContainer.list-view .ad-body-container {
        padding: 1.5rem 2rem !important;
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    @media (max-width: 768px) {
        #adsContainer.list-view .partner-ad-card {
            flex-direction: column !important;
            height: auto !important;
        }
        #adsContainer.list-view .ad-image-container {
            width: 100% !important;
            height: 200px !important;
        }
    }

    /* Sort Option Highlights */
    .sort-option-item:hover {
        background-color: #F8FAFC !important;
        color: #1A73E8 !important;
    }
    .sort-option-item.active-sort {
        background-color: #E8F0FE !important;
        color: #1A73E8 !important;
    }

    /* Chevron rotation */
    #dropdownChevron.rotated {
        transform: rotate(180deg);
    }

    /* Slide-out Sidebar Drawer Styling */
    .filter-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 12000;
        backdrop-filter: blur(4px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .filter-drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .filter-drawer-content {
        position: fixed;
        top: 0;
        bottom: 0;
        right: -420px; /* Hidden off-screen in RTL */
        width: 100%;
        max-width: 400px;
        background: white;
        z-index: 12001;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 2.5rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        direction: rtl;
        font-family: 'Cairo', sans-serif;
    }
    .filter-drawer-content.active {
        right: 0; /* Slide in to the right edge */
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
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-10" style="direction: rtl; text-align: right; font-family: 'Cairo', sans-serif;">
    
    @php
        $partnerSlides = [];
        if (isset($banners) && $banners->count() > 0) {
            foreach($banners as $b) {
                if (is_array($b->items) && count($b->items) > 0) {
                    foreach($b->items as $item) {
                        $partnerSlides[] = [
                            'title' => $item['title'] ?? $b->title,
                            'description' => $item['description'] ?? $b->description,
                            'image' => $item['image'] ?? $b->image,
                            'button_text' => $item['button_text'] ?? $b->button_text,
                            'show_button' => $item['show_button'] ?? true,
                            'url' => $item['url'] ?? $b->url ?? route('services.create')
                        ];
                    }
                } else {
                    $partnerSlides[] = [
                        'title' => $b->title,
                        'description' => $b->description,
                        'image' => $b->image,
                        'button_text' => $b->button_text,
                        'show_button' => true,
                        'url' => $b->url ?? route('services.create')
                    ];
                }
            }
        }

        // Fallback slide
        if (empty($partnerSlides)) {
            $partnerSlides[] = [
                'title' => 'كن شريكًا ووسع نطاق خدماتك',
                'description' => 'وصل خدمتك مباشرة للمستخدمين المهتمين بالسلعة. اعرض خدماتك مثل الفحص، الشحن أو أي خدمة متخصصة، وخلي إعلانك يظهر بجانب المنتجات ذات الصلة لفرص أعلى وتحويل أسرع.',
                'image' => 'images/hero_car.png',
                'button_text' => '',
                'show_button' => false,
                'url' => route('services.create')
            ];
        }
    @endphp

    <!-- Partner Welcome Banner Slider -->
    <div class="partner-banner relative mb-12 overflow-hidden flex flex-col md:flex-row items-center justify-between" style="border-radius: 30px; padding: 0; color: white; min-height: 280px; box-shadow: 0 15px 35px rgba(26, 115, 232, 0.15);">
        <div style="position: absolute; inset: 0; width: 100%; height: 100%;" id="partnerBannerWrapper">
            @foreach($partnerSlides as $index => $item)
                <div class="partner-slide-item {{ $index === 0 ? 'active-slide' : 'hidden' }}" data-index="{{ $index }}" style="position: absolute; inset: 0; padding: 3rem 4rem; display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
                    <!-- Right side: Text details -->
                    <div class="w-full md:w-3/5 z-10" style="margin-bottom: 2rem; md:margin-bottom: 0; display: flex; flex-direction: column; justify-content: center; height: 100%;">
                        <h1 style="font-size: 2.2rem; font-weight: 900; margin-bottom: 1rem; line-height: 1.3;">{{ $item['title'] }}</h1>
                        <p style="font-size: 1rem; opacity: 0.95; line-height: 1.8; font-weight: 600; max-width: 580px;">
                            {{ $item['description'] }}
                        </p>
                        @if(!empty($item['show_button']) && !empty($item['button_text']))
                            <div style="margin-top: 1.5rem;">
                                <a href="{{ $item['url'] }}" style="display: inline-block; padding: 0.75rem 2rem; background: white; color: #1A73E8; border-radius: 12px; font-weight: 800; text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">{{ $item['button_text'] }}</a>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Left side: Image -->
                    <div class="relative w-full md:w-2/5 flex items-center justify-center md:justify-end z-10" style="height: 100%;">
                        @php
                            $imgPath = $item['image'];
                            if (!str_starts_with($imgPath, 'http') && !str_starts_with($imgPath, 'images/')) {
                                $imgPath = asset($imgPath);
                            } elseif (str_starts_with($imgPath, 'images/')) {
                                $imgPath = asset($imgPath);
                            }
                        @endphp
                        <img src="{{ $imgPath }}" alt="Partner Banner Image" style="max-height: 240px; {{ $imgPath == asset('images/hero_car.png') ? 'transform: rotate(90deg);' : '' }} object-fit: contain; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.3));" onerror="this.src='{{ asset('images/login-illustration.png') }}'; this.style.transform='none';">
                    </div>
                </div>
            @endforeach
        </div>

        @if(count($partnerSlides) > 1)
            <!-- Slider Indicators & Controls -->
            <div style="position: absolute; bottom: 2rem; right: 4rem; display: flex; align-items: center; gap: 0.5rem; z-index: 20;">
                @foreach($partnerSlides as $index => $item)
                    <span class="partner-dot {{ $index === 0 ? 'active' : '' }}" onclick="goToPartnerSlide({{ $index }})" style="width: {{ $index === 0 ? '24px' : '8px' }}; height: 8px; background: {{ $index === 0 ? 'white' : 'rgba(255,255,255,0.4)' }}; border-radius: 8px; cursor: pointer; transition: all 0.3s;"></span>
                @endforeach
            </div>
            <div style="position: absolute; bottom: 2rem; left: 4rem; display: flex; gap: 0.75rem; z-index: 20;">
                <button type="button" onclick="prevPartnerSlide()" style="width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.6); background: transparent; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer;"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" onclick="nextPartnerSlide()" style="width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.6); background: transparent; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer;"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        @endif
    </div>

    <!-- Section Title -->
    <div class="flex flex-col gap-4 mb-6">
        <h2 style="font-size: 1.9rem; font-weight: 900; color: #1C1C1C; margin: 0;">الخدمات</h2>
        
        <div class="filter-tabs-container">
            <a href="{{ route('ads.my', ['status' => 'all'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'all' ? 'active' : '' }}">الكل</a>
            <a href="{{ route('ads.my', ['status' => 'active'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'active' ? 'active' : '' }}">نشطة</a>
            <a href="{{ route('ads.my', ['status' => 'pending'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">قيد المراجعة</a>
            <a href="{{ route('ads.my', ['status' => 'rejected'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">المرفوضة</a>
        </div>
    </div>

    @if($ads->isEmpty() && !request('q') && !request('services') && $status === 'all')
        <!-- Custom Empty State matching design -->
        <div class="empty-state-container" style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #E2E8F0; max-width: 700px; margin: 0 auto 4rem;">
            <div style="width: 140px; height: 140px; background-color: #F1F5F9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 4px solid #F8FAFC;">
                <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <circle cx="10" cy="13" r="3" stroke="#475569" stroke-width="2.5"></circle>
                    <line x1="12" y1="15" x2="16" y2="19" stroke="#475569" stroke-width="2.5"></line>
                </svg>
            </div>
            
            <h3 style="font-size: 1.4rem; font-weight: 800; color: #1C1C1C; margin-bottom: 0.75rem;">ما عندك إعلانات بعد</h3>
            <p style="font-size: 0.95rem; color: #64748B; font-weight: 600; line-height: 1.8; max-width: 450px; margin: 0 auto 2.5rem;">
                أضف إعلانك الأول وابدأ البيع بكل سهولة. خلي الخدمات توصل للمستخدمين المهتمين.
            </p>
            
            <a href="{{ route('ads.create') }}" style="display: inline-block; padding: 1rem 3rem; background-color: #1A73E8; color: white; border-radius: 16px; font-weight: 800; font-size: 1.05rem; text-decoration: none; box-shadow: 0 10px 20px rgba(26, 115, 232, 0.25); transition: all 0.3s ease;">
                أضف إعلانك
            </a>
        </div>
    @else
        <!-- Filter and Search controls matching the design -->
        <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-8" style="background: white; padding: 1rem 1.5rem; border-radius: 20px; border: 1.5px solid #E2E8F0;">
            <!-- Right: Search input & Filter Button -->
            <div class="flex items-center gap-3 flex-1 max-w-[550px]">
                <!-- Filter Toggle Button (Sliders Icon) -->
                <button type="button" onclick="openPartnerFilterModal()" class="hover:bg-slate-50 transition-colors flex items-center justify-center" style="width: 46px; height: 46px; border: 1.5px solid #E2E8F0; border-radius: 14px; background: white; color: #475569; cursor: pointer;">
                    <i class="fa-solid fa-sliders" style="font-size: 1.15rem;"></i>
                </button>

                <!-- Search Input Form -->
                <form action="{{ route('ads.my') }}" method="GET" style="position: relative; width: 100%; margin: 0;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    @if(request('services'))
                        @foreach((array)request('services') as $svc)
                            <input type="hidden" name="services[]" value="{{ $svc }}">
                        @endforeach
                    @endif
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث عن..." style="width: 100%; height: 46px; padding-right: 3rem; padding-left: 1rem; border: 1.5px solid #E2E8F0; border-radius: 14px; font-weight: 700; font-size: 0.9rem; background-color: #F8FAFC; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#1A73E8'" onblur="this.style.borderColor='#E2E8F0'">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; top: 50%; transform: translateY(-50%); right: 1.25rem; color: #94A3B8;"></i>
                </form>
            </div>

            <!-- Left: Layout controls & Sort Dropdown -->
            <div class="flex items-center gap-3 justify-end">
                <button type="button" onclick="toggleLayout('grid')" id="gridToggleBtn" class="layout-toggle-btn active" style="width: 42px; height: 42px; border: 1.5px solid #E2E8F0; border-radius: 12px; background: white; color: #1A73E8; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                    <i class="fa-solid fa-table-cells-large"></i>
                </button>
                <button type="button" onclick="toggleLayout('list')" id="listToggleBtn" class="layout-toggle-btn" style="width: 42px; height: 42px; border: 1.5px solid #E2E8F0; border-radius: 12px; background: white; color: #64748B; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                
                <!-- Sort Dropdown Container -->
                <div style="position: relative;" id="sortDropdownContainer">
                    <button type="button" onclick="toggleSortDropdown()" style="display: flex; align-items: center; gap: 0.5rem; height: 42px; padding: 0 1.25rem; border: 1.5px solid #E2E8F0; border-radius: 12px; background: white; color: #64748B; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                        <span>الترتيب: 
                            @if(request('sort') === 'price_high')
                                الأعلى سعراً
                            @elseif(request('sort') === 'price_low')
                                الأقل سعراً
                            @else
                                الأحدث
                            @endif
                        </span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; margin-right: 0.25rem;"></i>
                    </button>
                    <!-- Floating Sort Menu -->
                    <div id="sortDropdownMenu" class="hidden" style="position: absolute; left: 0; top: calc(100% + 8px); width: 180px; background: white; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); z-index: 1000; overflow: hidden; animation: fadeIn 0.2s ease;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" style="display: block; padding: 0.85rem 1.25rem; color: #475569; font-weight: 700; font-size: 0.85rem; text-decoration: none; border-bottom: 1px solid #F1F5F9; transition: all 0.2s;" class="sort-option-item {{ !request('sort') || request('sort') === 'newest' ? 'active-sort' : '' }}">الأحدث</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" style="display: block; padding: 0.85rem 1.25rem; color: #475569; font-weight: 700; font-size: 0.85rem; text-decoration: none; border-bottom: 1px solid #F1F5F9; transition: all 0.2s;" class="sort-option-item {{ request('sort') === 'price_high' ? 'active-sort' : '' }}">الأعلى سعراً</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" style="display: block; padding: 0.85rem 1.25rem; color: #475569; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: all 0.2s;" class="sort-option-item {{ request('sort') === 'price_low' ? 'active-sort' : '' }}">الأقل سعراً</a>
                    </div>
                </div>
            </div>
        </div>

        @if($ads->isEmpty())
            <!-- Empty state specifically for NO RESULTS FOUND due to search/filter -->
            <div class="empty-state-container" style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 30px; border: 1.5px solid #E2E8F0; max-width: 700px; margin: 0 auto 4rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                <div style="width: 100px; height: 100px; background-color: #F8FAFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 3px solid #F1F5F9;">
                    <i class="fa-solid fa-magnifying-glass text-3xl" style="color: #94A3B8;"></i>
                </div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: #1C1C1C; margin-bottom: 0.5rem;">لم يتم العثور على أي نتائج</h3>
                <p style="font-size: 0.95rem; color: #64748B; font-weight: 600; margin-bottom: 2rem; max-width: 450px; margin-left: auto; margin-right: auto; line-height: 1.7;">
                    لم نجد أي خدمات تطابق بحثك أو معايير التصفية الحالية. جرب البحث بكلمات أخرى أو إعادة تعيين الفلاتر.
                </p>
                <button type="button" onclick="resetPartnerFilters()" style="padding: 0.85rem 2.5rem; background-color: #1A73E8; color: white; border: none; border-radius: 14px; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 10px 20px rgba(26,115,232,0.2); transition: all 0.2s;" class="hover:bg-[#1557B0]">
                    إعادة تعيين التصفية
                </button>
            </div>
        @else
            <!-- Custom Partner Services Grid -->
            <div id="adsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($ads as $ad)
                    <div class="partner-ad-card bg-white" style="border: 1px solid #E2E8F0; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 10px 25px rgba(0,0,0,0.02); transition: all 0.3s ease; position: relative;">
                        
                        <!-- Ad Image with Price Badge -->
                        <a href="{{ route('services.show', $ad->id) }}" class="ad-image-container" style="position: relative; height: 200px; width: 100%; overflow: hidden; background: #F8FAFC; display: block;">
                            <img src="{{ $ad->image ? asset($ad->image) : asset('images/login-illustration.png') }}" alt="{{ $ad->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            
                            <!-- Status Badge -->
                            <div style="position: absolute; top: 1rem; left: 1rem; z-index: 10;">
                                @if(($ad->status ?? 'pending') === 'active')
                                    <span style="background-color: #DEF7EC; color: #03543F; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 800; font-size: 0.8rem; display: inline-flex; align-items: center; border: 1px solid #BCF0DA;">نشط</span>
                                @elseif(($ad->status ?? 'pending') === 'pending')
                                    <span style="background-color: #FEF3C7; color: #92400E; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 800; font-size: 0.8rem; display: inline-flex; align-items: center; border: 1px solid #FDE68A;">قيد المراجعة</span>
                                @elseif(($ad->status ?? 'pending') === 'rejected')
                                    <span style="background-color: #FDE8E8; color: #9B1C1C; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 800; font-size: 0.8rem; display: inline-flex; align-items: center; border: 1px solid #FBD5D5;">مرفوض</span>
                                @endif
                            </div>

                            <!-- Price Badge -->
                            <span style="position: absolute; bottom: 1rem; right: 1rem; background-color: #E8F1FE; color: #1A73E8; padding: 0.5rem 1rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(26,115,232,0.15); display: inline-flex; align-items: center; gap: 0.25rem;">
                                {{ number_format($ad->price) }}
                                <x-riyal-icon class="w-4 h-4" />
                            </span>
                        </a>

                        <!-- Card Body -->
                        <div class="ad-body-container" style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 style="font-size: 1.15rem; font-weight: 900; color: #1C1C1C; margin-bottom: 0.5rem; line-height: 1.4;">
                                    <a href="{{ route('services.show', $ad->id) }}" class="hover:text-[#1A73E8] transition-colors duration-200" style="color: inherit; text-decoration: none;">
                                        {{ $ad->title }}
                                    </a>
                                </h3>
                                <p style="font-size: 0.85rem; color: #64748B; font-weight: 600; line-height: 1.7; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 1.5rem;">
                                    {{ $ad->description ?: 'لا يوجد وصف متاح لهذه الخدمة.' }}
                                </p>
                            </div>

                            <!-- Card Footer Action Buttons -->
                            <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: auto;">
                                <!-- Edit Button -->
                                <a href="{{ route('services.edit', $ad->id) }}" style="flex: 2; text-align: center; padding: 0.8rem; background-color: #1A73E8; color: white; border-radius: 14px; font-weight: 800; font-size: 0.95rem; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: background 0.2s;">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>تعديل</span>
                                </a>
                                
                                <!-- Delete Button -->
                                <form id="delete-form-{{ $ad->id }}" action="{{ route('services.destroy', $ad->id) }}" method="POST" style="flex: 1; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeletePartnerAd({{ $ad->id }})" style="width: 100%; padding: 0.8rem; background-color: #F1F5F9; color: #475569; border: none; border-radius: 14px; font-weight: 800; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s;">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span>حذف</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($ads->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $ads->links() }}
                </div>
            @endif
        @endif
    @endif
</div>

<!-- Partner Services Filter Side Drawer -->
<div id="partnerFilterDrawerOverlay" class="filter-drawer-overlay" onclick="closePartnerFilterModal()"></div>
<div id="partnerFilterDrawer" class="filter-drawer-content">
    <!-- Top Part -->
    <div>
        <!-- Header: Close X and Title -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <!-- Close icon on the left (RTL) -->
            <button type="button" onclick="closePartnerFilterModal()" style="background: transparent; border: none; font-size: 1.4rem; color: #475569; cursor: pointer; transition: color 0.2s;" class="hover:text-red-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <!-- Title on the right (RTL) -->
            <h3 style="font-size: 1.4rem; font-weight: 900; color: #1C1C1C; margin: 0;">التصفية</h3>
        </div>

        <form id="partnerFilterForm" action="{{ route('ads.my') }}" method="GET" style="display: flex; flex-direction: column;">
            <input type="hidden" name="status" value="{{ $status }}">
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

            <!-- Services Section -->
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.95rem; font-weight: 800; color: #1C1C1C; margin-bottom: 0.75rem;">الخدمات</label>
                
                <!-- Fake select dropdown header -->
                <div onclick="toggleServicesList()" style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #E2E8F0; padding: 0.85rem 1.25rem; border-radius: 14px; background: white; color: #94A3B8; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.75rem; cursor: pointer; user-select: none;">
                    <span id="selectedServicesText" style="color: #64748B;">حدد الخدمات</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; transition: transform 0.2s;" id="dropdownChevron"></i>
                </div>

                <!-- Custom Checkboxes Container -->
                <div id="filterServicesContainer" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($filterServices ?? [] as $serviceName)
                    <label class="filter-service-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.25rem; cursor: pointer; user-select: none;">
                        <span style="font-size: 0.9rem; font-weight: 700; color: #475569;">{{ $serviceName }}</span>
                        <input type="checkbox" name="services[]" value="{{ $serviceName }}" class="filter-svc-input" style="width: 20px; height: 20px; border-radius: 6px; border: 2.5px solid #CBD5E1; accent-color: #1A73E8; cursor: pointer;" onchange="updateSelectedText()" {{ in_array($serviceName, (array)request('services')) ? 'checked' : '' }}>
                    </label>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <!-- Bottom Part (Actions) -->
    <div style="display: flex; gap: 0.75rem; margin-top: auto; border-top: 1px solid #F1F5F9; padding-top: 1.5rem;">
        <button type="submit" form="partnerFilterForm" style="flex: 1; padding: 0.95rem; border-radius: 14px; font-weight: 800; font-size: 1rem; background: #1A73E8; border: none; color: white; cursor: pointer; transition: background 0.2s;" class="hover:bg-[#1557B0]">تطبيق</button>
        <button type="button" onclick="resetPartnerFilters()" style="flex: 1; padding: 0.95rem; border-radius: 14px; font-weight: 800; font-size: 1rem; background: #E2E8F0; border: none; color: #475569; cursor: pointer; transition: background 0.2s;" class="hover:bg-slate-300">إعادة تعيين</button>
    </div>
</div>

<script>
    // Layout Toggle Script (Grid / List)
    function toggleLayout(view) {
        const container = document.getElementById('adsContainer');
        const gridBtn = document.getElementById('gridToggleBtn');
        const listBtn = document.getElementById('listToggleBtn');

        if (!container) return;

        if (view === 'list') {
            container.classList.add('list-view');
            gridBtn.classList.remove('active');
            gridBtn.style.color = '#64748B';
            listBtn.classList.add('active');
            listBtn.style.color = '#1A73E8';
            localStorage.setItem('partner_layout_view', 'list');
        } else {
            container.classList.remove('list-view');
            gridBtn.classList.add('active');
            gridBtn.style.color = '#1A73E8';
            listBtn.classList.remove('active');
            listBtn.style.color = '#64748B';
            localStorage.setItem('partner_layout_view', 'grid');
        }
    }

    // Sort Dropdown Menu Toggle
    function toggleSortDropdown() {
        const menu = document.getElementById('sortDropdownMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    // Side Drawer Control Handlers
    function openPartnerFilterModal() {
        const overlay = document.getElementById('partnerFilterDrawerOverlay');
        const drawer = document.getElementById('partnerFilterDrawer');
        if (overlay && drawer) {
            overlay.classList.add('active');
            drawer.classList.add('active');
            updateSelectedText();
        }
    }

    function closePartnerFilterModal() {
        const overlay = document.getElementById('partnerFilterDrawerOverlay');
        const drawer = document.getElementById('partnerFilterDrawer');
        if (overlay && drawer) {
            overlay.classList.remove('active');
            drawer.classList.remove('active');
        }
    }

    // Custom Services inner checklist dropdown
    function toggleServicesList() {
        const list = document.getElementById('filterServicesContainer');
        const chevron = document.getElementById('dropdownChevron');
        if (list) {
            if (list.style.display === 'none') {
                list.style.display = 'flex';
                chevron.classList.add('rotated');
            } else {
                list.style.display = 'none';
                chevron.classList.remove('rotated');
            }
        }
    }

    // Update the selected services display text
    function updateSelectedText() {
        const checkboxes = document.querySelectorAll('.filter-svc-input');
        const labelText = document.getElementById('selectedServicesText');
        let selected = [];

        checkboxes.forEach(chk => {
            if (chk.checked) {
                selected.push(chk.value);
            }
        });

        if (labelText) {
            if (selected.length > 0) {
                labelText.textContent = selected.join('، ');
                labelText.style.color = '#1A73E8';
            } else {
                labelText.textContent = 'حدد الخدمات';
                labelText.style.color = '#64748B';
            }
        }
    }

    // Reset all checkboxes and submit form to clear filters
    function resetPartnerFilters() {
        const checkboxes = document.querySelectorAll('.filter-svc-input');
        checkboxes.forEach(chk => chk.checked = false);
        updateSelectedText();
        document.getElementById('partnerFilterForm').submit();
    }

    // Initialize layout and selections on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Layout from localStorage
        const savedView = localStorage.getItem('partner_layout_view') || 'grid';
        toggleLayout(savedView);

        // Update display text on filters
        updateSelectedText();

        // Close sort dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('sortDropdownContainer');
            const menu = document.getElementById('sortDropdownMenu');
            if (dropdown && !dropdown.contains(e.target) && menu) {
                menu.classList.add('hidden');
            }
        });
    });

    // Partner Slider Logic
    let currentPartnerSlide = 0;
    const partnerSlidesCount = {{ count($partnerSlides) }};

    function showPartnerSlide(n) {
        if (partnerSlidesCount <= 1) return;
        const slides = document.querySelectorAll('.partner-slide-item');
        const dots = document.querySelectorAll('.partner-dot');
        
        if (slides[currentPartnerSlide]) {
            slides[currentPartnerSlide].classList.remove('active-slide');
            slides[currentPartnerSlide].classList.add('hidden');
        }
        if (dots[currentPartnerSlide]) {
            dots[currentPartnerSlide].style.width = '8px';
            dots[currentPartnerSlide].style.background = 'rgba(255,255,255,0.4)';
        }

        currentPartnerSlide = (n + partnerSlidesCount) % partnerSlidesCount;

        if (slides[currentPartnerSlide]) {
            slides[currentPartnerSlide].classList.add('active-slide');
            slides[currentPartnerSlide].classList.remove('hidden');
        }
        if (dots[currentPartnerSlide]) {
            dots[currentPartnerSlide].style.width = '24px';
            dots[currentPartnerSlide].style.background = 'white';
        }
    }

    function nextPartnerSlide() { showPartnerSlide(currentPartnerSlide + 1); }
    function prevPartnerSlide() { showPartnerSlide(currentPartnerSlide - 1); }
    function goToPartnerSlide(n) { showPartnerSlide(n); }

    // Auto-slide every 5 seconds
    if (partnerSlidesCount > 1) {
        setInterval(nextPartnerSlide, 5000);
    }

    // SweetAlert2 Delete Confirmation
    function confirmDeletePartnerAd(adId) {
        Swal.fire({
            title: 'هل أنت متأكد من الحذف؟',
            text: 'لن تتمكن من استعادة هذا الإعلان بعد حذفه نهائياً!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'rounded-[24px]',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + adId).submit();
            }
        });
    }
</script>
@endsection
