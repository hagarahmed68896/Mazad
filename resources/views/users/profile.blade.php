@extends('layouts.main')

@section('title', 'الملف الشخصي | ' . $user->name)

@push('styles')
<!-- Leaflet (Map) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<style>
    .profile-page {
        background-color: #F8FAFC;
        min-height: 100vh;
    }

    .profile-banner {
        height: 350px;
        position: relative;
        overflow: hidden;
    }

    .profile-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-card {
        margin-top: -120px;
        position: relative;
        z-index: 10;
    }

    .tab-btn.active {
        background-color: #1A73E8;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.2);
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .info-card {
        background: white;
        border: 1px solid #F1F5F9;
        border-radius: 24px;
        padding: 20px;
        transition: all 0.3s;
    }

    .info-card:hover {
        border-color: #1A73E820;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
    }

    /* Map View Styles */
    .map-view-container {
        display: none;
        width: 100%;
        height: 600px;
        background: #fff;
        border-radius: 32px;
        overflow: hidden;
        border: 1px solid #F1F5F9;
        margin-bottom: 2rem;
        position: relative;
    }

    .map-view-container.active {
        display: block;
    }

    .map-ad-detail-floating {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        z-index: 1000;
        display: none;
        max-width: 400px;
        margin: 0 auto;
    }

    .map-ad-detail-floating.active {
        display: block;
        animation: slideUpFade 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes slideUpFade {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .custom-marker-cluster {
        background: #1A73E8;
        border: 3px solid rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        color: white;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 15px rgba(26, 115, 232, 0.4);
        font-size: 0.8rem;
    }

    .custom-marker-single {
        width: 20px;
        height: 20px;
        background: #1A73E8;
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(26, 115, 232, 0.5);
        cursor: pointer;
        transition: all 0.3s;
    }
    .custom-marker-single.selected {
        background: #EF4444;
        transform: scale(1.2);
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.6);
    }
</style>
@endpush

@section('content')
<div class="profile-page pb-20" dir="rtl">
    <!-- Success Message Alert -->
    @if(session('success'))
        <div class="fixed top-8 left-1/2 -translate-x-1/2 z-[200] animate-in slide-in-from-top duration-500" id="successAlert">
            <div class="bg-white border border-green-100 rounded-[24px] p-4 shadow-2xl flex items-center gap-4 min-w-[320px] border-b-4 border-b-green-500">
                <div class="w-12 h-12 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-gray-400 mb-0.5">تمت العملية بنجاح</p>
                    <p class="text-sm font-black text-gray-900">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-300 hover:text-gray-500 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('successAlert');
                if(alert) {
                    alert.classList.add('fade-out');
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        </script>
    @endif

    <!-- Profile Banner -->
    <div class="profile-banner bg-gray-900">
        @php
            $latestAd = $user->ads()->latest()->first();
            $user_cover = $user->cover ? (Str::startsWith($user->cover, 'http') ? $user->cover : '/' . ltrim($user->cover, '/')) : null;
            $ad_img = $latestAd && $latestAd->image ? (Str::startsWith($latestAd->image, 'http') ? $latestAd->image : '/' . ltrim($latestAd->image, '/')) : null;
            $coverImage = $user_cover ?? $ad_img;
        @endphp
        @if($coverImage)
            <img src="{{ $coverImage }}" alt="cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900"></div>
        @endif
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
        
        <!-- Breadcrumbs over banner -->
        <div class="absolute top-8 right-0 left-0">
            <div class="container mx-auto px-4 md:px-16">
                <nav class="flex bg-white/10 backdrop-blur-md w-fit px-6 py-2 rounded-full border border-white/20 text-xs font-bold text-white">
                    <ol class="inline-flex items-center space-x-reverse space-x-2">
                        <li><a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a></li>
                        <li><i class="fa-solid fa-chevron-left text-[8px] mx-1 opacity-50"></i></li>
                        <li><a href="#" class="hover:text-primary transition-colors">المعلنين</a></li>
                        <li><i class="fa-solid fa-chevron-left text-[8px] mx-1 opacity-50"></i></li>
                        <li class="opacity-80">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 md:px-16">
        <!-- Profile Main Card -->
        <div class="profile-card bg-white rounded-[40px] p-8 md:p-12 shadow-2xl shadow-blue-900/5 border border-white">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-10">
                <!-- Avatar -->
                <div class="relative">
                    <div class="w-40 h-40 md:w-48 md:h-48 rounded-[48px] overflow-hidden border-8 border-white shadow-2xl">
                        <img src="{{ $user->avatar ? (Str::startsWith($user->avatar, 'http') ? $user->avatar : '/' . ltrim($user->avatar, '/')) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=1A73E8&color=fff&size=256' }}" 
                             class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Info & Actions -->
                <div class="flex-1 text-center md:text-right">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                        <div>
                            <h1 class="text-3xl md:text-5xl font-black text-gray-900 mb-2">{{ $user->name }}</h1>
                            @php
                                $profileParentCompany = $user->parent_id ? $user->company()->first() : null;
                                $isProfileRep = $profileParentCompany !== null;
                            @endphp
                            @if($user->account_type == 'company' && $user->company_name)
                                <p class="text-lg text-gray-400 font-bold flex items-center justify-center md:justify-start gap-2">
                                    <i class="fa-solid fa-building text-primary/40"></i>
                                    {{ $user->company_name }}
                                </p>
                            @elseif($user->account_type == 'partner')
                                <p class="text-lg text-gray-400 font-bold flex items-center justify-center md:justify-start gap-2">
                                    <i class="fa-solid fa-handshake text-primary/40"></i>
                                    شريك المنصة
                                </p>
                            @elseif($isProfileRep && $profileParentCompany)
                                <p class="text-lg font-bold flex items-center justify-center md:justify-start gap-2 flex-wrap">
                                    <span style="background:#EBF3FD; color:#1A73E8; border-radius:8px; padding:3px 12px; font-size:0.82rem; font-weight:800; display:inline-flex; align-items:center; gap:0.3rem;">
                                        <i class="fa-solid fa-building" style="font-size:0.75rem;"></i>
                                        مندوب شركة
                                    </span>
                                    <a href="{{ route('users.profile', $profileParentCompany->id) }}" style="color:#1A73E8; font-weight:900; text-decoration:none;" class="hover:underline">
                                        {{ $profileParentCompany->company_name ?? $profileParentCompany->name }}
                                    </a>
                                </p>
                            @else
                                <p class="text-lg text-gray-400 font-bold flex items-center justify-center md:justify-start gap-2">
                                    <i class="fa-solid fa-user text-primary/40"></i>
                                    عضو فردي
                                </p>
                            @endif
                            @if(auth()->check() && auth()->id() == $user->parent_id)
                                <!-- Stats in single row under company name (For Company Owner View of Representative) -->
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-5 mt-4 text-sm font-black text-gray-700">
                                    <span class="flex items-center gap-2">
                                        <i class="fa-solid fa-boxes-stacked text-gray-400"></i>
                                        <span>{{ number_format($adsCount) }} إعلانات</span>
                                    </span>
                                    <span class="text-gray-300">|</span>
                                    <span class="flex items-center gap-2">
                                        <i class="fa-solid fa-handshake text-gray-400"></i>
                                        <span>{{ number_format($soldCount) }} منتجات تم بيعها</span>
                                    </span>
                                    <span class="text-gray-300">|</span>
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-star text-amber-400"></i>
                                        <span>{{ number_format($rating, 1) }} ({{ $reviewsCount }} تقييم)</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center justify-center gap-3">
                            @if(auth()->check() && auth()->id() == $user->parent_id)
                                <!-- Delete Button for Company Owner to delete member -->
                                <button onclick="deleteMember({{ $user->id }}, '{{ $user->name }}')" class="bg-gray-200/80 hover:bg-red-50 text-gray-700 hover:text-red-600 px-6 py-2.5 rounded-xl font-black transition-all flex items-center gap-2 border border-gray-300/40 shadow-sm cursor-pointer text-sm">
                                    <span>حذف</span>
                                    <i class="fa-regular fa-trash-can text-sm"></i>
                                </button>
                            @else
                                @if(!auth()->check() || auth()->id() != $user->id)
                                    @if(auth()->check())
                                        <a href="{{ route('chats.with', $user->id) }}" class="bg-primary text-white px-10 py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-3">
                                            <i class="fa-solid fa-comments"></i> الدردشة
                                        </a>
                                    @else
                                        <button type="button" onclick="requireAuth()" class="bg-primary text-white px-10 py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-3">
                                            <i class="fa-solid fa-comments"></i> الدردشة
                                        </button>
                                    @endif
                                @endif
                                <button onclick="shareProfile()" class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:text-primary hover:bg-blue-50 transition-all border border-gray-100">
                                    <i class="fa-solid fa-share-nodes text-xl"></i>
                                </button>
                                <button onclick="toggleReportModal()" class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:text-red-500 hover:bg-red-50 transition-all border border-gray-100" title="الإبلاغ عن هذا المستخدم">
                                    <i class="fa-solid fa-circle-exclamation text-xl"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if(!(auth()->check() && auth()->id() == $user->parent_id))
                        <!-- Stats Row (Only shown if NOT viewed by the parent company owner) -->
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-12">
                            <div class="text-center md:text-right">
                                <span class="block text-3xl font-black text-gray-900">{{ number_format($adsCount) }}</span>
                                <span class="text-sm font-bold text-gray-400">إعلان نشط</span>
                            </div>
                            <div class="w-px h-10 bg-gray-100 hidden md:block"></div>
                            <div class="text-center md:text-right">
                                <span class="block text-3xl font-black text-gray-900">{{ number_format($soldCount) }}</span>
                                <span class="text-sm font-bold text-gray-400">منتجات تم بيعها</span>
                            </div>
                            <div class="w-px h-10 bg-gray-100 hidden md:block"></div>
                            <div class="text-center md:text-right">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-3xl font-black text-gray-900">{{ number_format($rating, 1) }}</span>
                                    <i class="fa-solid fa-star text-amber-400"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-400">{{ $reviewsCount }} تقييم</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="mt-16">
            <div class="flex items-center gap-4 mb-10 overflow-x-auto no-scrollbar pb-2">
                <button onclick="switchTab('about')" id="btn-about" class="tab-btn active px-10 py-4 rounded-2xl font-black text-sm whitespace-nowrap transition-all flex items-center gap-3">
                    <i class="fa-solid fa-user-tie"></i> نبذة عن المعلن
                </button>
                <button onclick="switchTab('ads')" id="btn-ads" class="tab-btn bg-white text-gray-500 border border-gray-100 px-10 py-4 rounded-2xl font-black text-sm whitespace-nowrap transition-all flex items-center gap-3 ">
                    <i class="fa-solid fa-boxes-stacked"></i> المنتجات
                </button>
                <button onclick="switchTab('reviews')" id="btn-reviews" class="tab-btn bg-white text-gray-500 border border-gray-100 px-10 py-4 rounded-2xl font-black text-sm whitespace-nowrap transition-all flex items-center gap-3">
                    <i class="fa-solid fa-star-half-stroke"></i> التقييمات
                </button>
            </div>

            <!-- Tab Content: About -->
            <div id="tab-about" class="tab-pane animate-in fade-in slide-in-from-bottom duration-500">
                <div class="space-y-8">
                    <div class="bg-white rounded-[40px] p-10 border border-gray-50 shadow-sm">
                        <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-2 h-8 bg-primary rounded-full"></span>
                            نبذة عن المعلن
                        </h3>
                        <p class="text-gray-500 font-medium leading-loose text-lg">
                            {{ $user->bio }}
                        </p>
                    </div>
                    
                    <!-- Contact Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="info-card flex items-center gap-5">
                            <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                                <i class="fa-solid fa-location-dot text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 mb-1">العنوان</span>
                                <span class="font-black text-gray-900 text-sm">{{ $user->address ?? 'الرياض، السعودية' }}</span>
                            </div>
                        </div>
                        <div class="info-card flex items-center gap-5">
                            <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                                <i class="fa-solid fa-phone-volume text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 mb-1">رقم التواصل</span>
                                <span class="font-black text-gray-900 text-sm" dir="ltr">+966 {{ $user->phone ?? '0123456789' }}</span>
                            </div>
                        </div>
                        <div class="info-card flex items-center gap-5">
                            <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                                <i class="fa-solid fa-envelope text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 mb-1">البريد الإلكتروني</span>
                                <span class="font-black text-gray-900 text-sm">{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Ads -->
            <div id="tab-ads" class="tab-pane hidden animate-in fade-in slide-in-from-bottom duration-500">
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    <!-- Main Content -->
                    <div class="flex-1 space-y-6 order-1 lg:order-2 w-full">
                        <!-- Top Action Bar -->
                        <div class="bg-white rounded-[32px] p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-4 w-full md:w-auto flex-1">
                                <button onclick="toggleProfileSidebar()" class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-2xl flex items-center justify-center text-gray-400 hover:text-primary hover:bg-blue-50 transition-all shrink-0" title="تصفية">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>
                                <button type="button" onclick="setView('map')" id="mapViewBtn" class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-2xl flex items-center justify-center text-gray-400 hover:text-primary hover:bg-blue-50 transition-all shrink-0" title="عرض الخريطة">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </button>
                                <div class="relative flex-1 max-w-md">
                                    <input type="text" id="profileSearchInput" form="profileFilterForm" name="q" value="{{ request('q') }}" placeholder="بحث في منتجات المعلن..." class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3 pr-12 pl-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm transition-all">
                                    <i class="fa-solid fa-magnifying-glass absolute right-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <div class="relative flex-1 md:flex-none">
                                    <select form="profileFilterForm" name="sort" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3 px-10 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm appearance-none">
                                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>الأحدث أولاً</option>
                                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>الأعلى سعراً</option>
                                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>الأقل سعراً</option>
                                    </select>
                                    <i class="fa-solid fa-arrow-down-wide-short absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                    <i class="fa-solid fa-chevron-down absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Map View Container -->
                        <div id="mapViewContainer" class="map-view-container">
                            <div id="profileMap" style="width: 100%; height: 100%;"></div>
                            
                            <!-- Floating Ad Detail -->
                            <div id="mapAdDetail" class="map-ad-detail-floating">
                                <!-- Content will be injected via JS -->
                            </div>
                        </div>

                        <!-- Ads Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="profileAdsGrid">
                            @forelse($ads as $ad)
                                @include('partials.ad-card', ['ad' => $ad])
                            @empty
                                <div class="col-span-full text-center py-20 bg-white rounded-[40px] border-2 border-dashed border-gray-100">
                                    <i class="fa-solid fa-boxes-open text-5xl text-gray-200 mb-4"></i>
                                    <p class="text-gray-400 font-bold">لا توجد منتجات مطابقة لهذا البحث</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        <div class="mt-10">
                            {{ $ads->links() }}
                        </div>
                    </div>

                    <!-- Sidebar Filters -->
                    <aside id="profileSidebar" class="w-full lg:w-80 shrink-0 hidden order-2 lg:order-1 animate-in slide-in-from-right duration-300">
                        <div class="bg-white rounded-[32px] p-8 border border-gray-100 shadow-sm sticky top-24">
                            <div class="flex items-center justify-between mb-8">
                                <h3 class="text-lg font-black text-gray-900">تصفية النتائج</h3>
                                <a href="{{ route('users.profile', $user->id) }}" class="text-xs font-bold text-primary hover:underline">إعادة تعيين</a>
                            </div>

                            <form id="profileFilterForm" action="{{ route('users.profile', $user->id) }}#tab-ads" method="GET" class="space-y-6">
                                <!-- الموقع -->
                                <div class="space-y-3">
                                    <label class="text-sm font-black text-gray-900 block">الموقع</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="handleLocationClick()" class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 shrink-0">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </button>
                                        <div class="relative flex-1">
                                            <select name="city" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm appearance-none">
                                                <option value="">كل المواقع</option>
                                                @foreach($cities as $city)
                                                    <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                            <i class="fa-solid fa-chevron-down absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                        </div>
                                    </div>
                                    @php
                                        $currentAddr = Auth::check() ? Auth::user()->address : null;
                                        $selectedCity = request('city') ? $cities->firstWhere('id', request('city')) : null;
                                    @endphp
                                    
                                    @if($selectedCity)
                                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex items-center gap-3 mt-3 animate-in fade-in zoom-in duration-300">
                                            <i class="fa-solid fa-city text-gray-400"></i>
                                            <div class="flex-1 min-w-0">
                                                <span class="block text-[10px] font-bold text-gray-400">المنطقة المختارة:</span>
                                                <span class="block text-xs font-black text-gray-900 truncate">{{ $selectedCity->name }}</span>
                                            </div>
                                            <a href="{{ route('users.profile', $user->id) }}?city=&{{ http_build_query(request()->except(['city', 'page'])) }}#tab-ads" class="text-gray-400 hover:text-red-500 transition-colors">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        </div>
                                    @endif

                                    @if($currentAddr && !$selectedCity)
                                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100/50 flex items-center gap-3 mt-3 animate-in fade-in zoom-in duration-300">
                                            <input type="hidden" name="nearby" value="1" form="profileFilterForm">
                                            <i class="fa-solid fa-location-crosshairs text-primary"></i>
                                            <div class="flex-1 min-w-0">
                                                <span class="block text-[10px] font-bold text-gray-400">نتائج قريبة من:</span>
                                                <span class="block text-xs font-black text-gray-900 truncate" title="{{ $currentAddr }}">{{ $currentAddr }}</span>
                                            </div>
                                            <a href="{{ route('location.clear') }}" class="text-gray-400 hover:text-red-500 transition-colors">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- نوع الإعلان -->
                                <div class="space-y-3">
                                    <label class="text-sm font-black text-gray-900 block">نوع الإعلان</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['' => 'الكل', 'auction' => 'مزادات', 'direct_purchase' => 'شراء فوري', 'tender' => 'مناقصات'] as $val => $label)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="type" value="{{ $val }}" {{ request('type') == $val ? 'checked' : '' }} onchange="this.form.submit()" class="hidden peer">
                                                <span class="px-4 py-2 rounded-xl border border-gray-100 bg-gray-50 text-xs font-bold text-gray-500 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-all block">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- حالة الإعلان -->
                                <div class="space-y-3">
                                    <label class="text-sm font-black text-gray-900 block">حالة الإعلان</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['active' => 'قائم', 'upcoming' => 'قادم', 'finished' => 'منتهي'] as $val => $label)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="ad_status" value="{{ $val }}" {{ request('ad_status', 'active') == $val ? 'checked' : '' }} onchange="this.form.submit()" class="hidden peer">
                                                <span class="px-4 py-2 rounded-xl border border-gray-100 bg-gray-50 text-xs font-bold text-gray-500 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-all block">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- الفئات -->
                                <div class="space-y-3 pt-4 border-t border-gray-50">
                                    <label class="text-sm font-black text-gray-900 block">الفئات</label>
                                    <div class="space-y-2 max-h-60 overflow-y-auto no-scrollbar">
                                        @foreach($categories as $cat)
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()" class="w-5 h-5 rounded-lg border-gray-200 text-primary focus:ring-primary/20">
                                                <span class="text-sm font-bold text-gray-500 group-hover:text-primary transition-colors">{{ $cat->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Dynamic Category Properties -->
                                @if(isset($activeCategory) && $activeCategory->properties->count() > 0)
                                    <div class="pt-4 border-t border-gray-50 space-y-6 animate-in fade-in slide-in-from-top duration-300">
                                        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100/50 flex items-center justify-between">
                                            <span class="text-xs font-black text-primary">{{ $activeCategory->name }}</span>
                                            <a href="{{ request()->fullUrlWithQuery(['category' => null, 'props' => null]) }}#tab-ads" class="text-red-400 hover:text-red-600 transition-colors">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </a>
                                        </div>

                                        @foreach($activeCategory->properties->sortBy('sort_order') as $prop)
                                            <div class="space-y-3">
                                                <label class="text-sm font-black text-gray-900 block">{{ $prop->label }}</label>
                                                
                                                @if($prop->input_type == 'select')
                                                    <div class="relative">
                                                        <select name="props[{{ $prop->id }}]" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm appearance-none">
                                                            <option value="">الكل</option>
                                                            @foreach($prop->options ?? [] as $opt)
                                                                <option value="{{ $opt }}" {{ (request('props')[$prop->id] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                        <i class="fa-solid fa-chevron-down absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                                    </div>
                                                @elseif($prop->input_type == 'radio' || $prop->input_type == 'checkbox')
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($prop->options ?? [] as $opt)
                                                            <label class="cursor-pointer">
                                                                <input type="{{ $prop->input_type }}" name="props[{{ $prop->id }}]{{ $prop->input_type == 'checkbox' ? '[]' : '' }}" value="{{ $opt }}" 
                                                                    {{ in_array($opt, (array)(request('props')[$prop->id] ?? [])) ? 'checked' : '' }} onchange="this.form.submit()" class="hidden peer">
                                                                <span class="px-3 py-1.5 rounded-lg border border-gray-100 bg-white text-[11px] font-bold text-gray-400 peer-checked:bg-primary/10 peer-checked:text-primary peer-checked:border-primary/20 transition-all block">{{ $opt }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <input type="{{ $prop->input_type == 'price' ? 'number' : ($prop->input_type ?: 'text') }}" name="props[{{ $prop->id }}]" 
                                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm transition-all" 
                                                           placeholder="{{ $prop->placeholder }}" 
                                                           value="{{ is_array(request('props')[$prop->id] ?? '') ? '' : (request('props')[$prop->id] ?? '') }}" 
                                                           onchange="this.form.submit()">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </form>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- Tab Content: Reviews -->
            <div id="tab-reviews" class="tab-pane hidden animate-in fade-in slide-in-from-bottom duration-500">
                 <div class="max-w-4xl mx-auto space-y-6">
                    @forelse($user->reviewsReceived ?? [] as $review)
                        <div class="bg-white p-8 rounded-[32px] border border-gray-50 shadow-sm flex gap-6">
                             <div class="w-16 h-16 rounded-2xl bg-gray-100 flex-shrink-0 flex items-center justify-center text-gray-300">
                                <i class="fa-solid fa-user text-2xl"></i>
                             </div>
                             <div class="flex-1">
                                <div class="flex justify-between items-center mb-3">
                                    <h5 class="font-black text-gray-900">{{ $review->reviewer->name ?? $review->ratedUser->name ?? 'مجهول' }}</h5>
                                    <div class="flex items-center gap-1">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-100' }} text-xs"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-gray-500 font-medium leading-relaxed">{{ $review->comment }}</p>
                                <span class="block mt-4 text-[11px] font-bold text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                             </div>
                        </div>
                    @empty
                        <div class="text-center py-20 bg-white rounded-[40px] border-2 border-dashed border-gray-100">
                            <i class="fa-solid fa-star-half-stroke text-5xl text-gray-200 mb-4"></i>
                            <p class="text-gray-400 font-bold">لا توجد تقييمات لهذا المعلن بعد</p>
                        </div>
                    @endforelse
                 </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="toggleReportModal()"></div>
    <div class="bg-white rounded-[32px] w-full max-w-md p-8 relative z-10 animate-in zoom-in duration-300">
        <button onclick="toggleReportModal()" class="absolute top-6 left-6 text-gray-400 hover:text-gray-600 transition-all">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation text-3xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900">الإبلاغ عن هذا المستخدم</h3>
            <p class="text-gray-400 text-sm font-bold mt-2">يرجى كتابة سبب الإبلاغ بالتفصيل</p>
        </div>

        <form action="{{ route('users.report', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-3">
                <label class="text-sm font-black text-gray-900 block">سبب الإبلاغ</label>
                <textarea name="description" rows="4" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-5 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm transition-all" placeholder="اكتب تفاصيل البلاغ هنا..."></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" id="submitReportBtn" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> جاري الإرسال...'; this.classList.add('opacity-70', 'cursor-not-allowed');" class="flex-1 bg-primary text-white py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">إرسال</button>
                <button type="button" onclick="toggleReportModal()" class="flex-1 bg-gray-100 text-gray-400 py-4 rounded-2xl font-black hover:bg-gray-200 transition-all">إلغاء</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize gallery state on load
    document.addEventListener('DOMContentLoaded', () => {
        // Check if we should open the products tab (if filters are active or hash is present)
        const urlParams = new URLSearchParams(window.location.search);
        
        // If there's a specific hash, use it. Otherwise, if there are any query params, default to 'ads'
        const hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
            switchTab(hash.replace('#tab-', ''));
        } else if (window.location.search !== '') {
            switchTab('ads');
        }

        // If filters are active, keep the sidebar visible if it was opened
        if(localStorage.getItem('profileSidebarOpen') === 'true') {
            const sidebar = document.getElementById('profileSidebar');
            if (sidebar) sidebar.classList.remove('hidden');
        }
        
        // Ensure form submission preserves the tab hash
        const filterForm = document.getElementById('profileFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function() {
                const actionUrl = new URL(this.action);
                actionUrl.hash = 'tab-ads';
                this.action = actionUrl.toString();
            });
        }

        // Handle initial view (map vs grid)
        const initialView = "{{ request('view', 'grid') }}";
        if (initialView === 'map') {
            setView('map');
        }
    });

    let profileMapInstance = null;
    let markersClusterGroup = null;

    function setView(view) {
        const grid = document.getElementById('profileAdsGrid');
        const mapContainer = document.getElementById('mapViewContainer');
        const mapBtn = document.getElementById('mapViewBtn');
        
        if (view === 'map') {
            if (mapContainer.classList.contains('active')) {
                // Toggle off
                mapContainer.classList.remove('active');
                grid.classList.remove('hidden');
                mapBtn.classList.remove('bg-primary', 'text-white', 'shadow-lg');
                mapBtn.classList.add('bg-gray-50', 'text-gray-400');
                
                const url = new URL(window.location);
                url.searchParams.delete('view');
                window.history.pushState({}, '', url);
            } else {
                // Toggle on
                mapContainer.classList.add('active');
                grid.classList.add('hidden');
                mapBtn.classList.add('bg-primary', 'text-white', 'shadow-lg');
                mapBtn.classList.remove('bg-gray-50', 'text-gray-400');
                
                initProfileMap();
                
                const url = new URL(window.location);
                url.searchParams.set('view', 'map');
                window.history.pushState({}, '', url);
            }
        } else {
            mapContainer.classList.remove('active');
            grid.classList.remove('hidden');
            mapBtn.classList.remove('bg-primary', 'text-white', 'shadow-lg');
            mapBtn.classList.add('bg-gray-50', 'text-gray-400');
        }
    }

    function initProfileMap() {
        if (profileMapInstance) {
            profileMapInstance.invalidateSize();
            return;
        }

        // Default center (Saudi Arabia)
        profileMapInstance = L.map('profileMap', { zoomControl: false }).setView([24.7136, 46.6753], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(profileMapInstance);

        markersClusterGroup = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: `<span>${cluster.getChildCount()}</span>`,
                    className: 'custom-marker-cluster',
                    iconSize: L.point(40, 40)
                });
            }
        });

        const ads = @json($ads->items());
        
        ads.forEach(ad => {
            const lat = parseFloat(ad.lat);
            const lng = parseFloat(ad.lng);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const customIcon = L.divIcon({
                    className: 'custom-marker-single',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                const marker = L.marker([lat, lng], { icon: customIcon });
                
                marker.on('click', (e) => {
                    document.querySelectorAll('.custom-marker-single').forEach(el => el.classList.remove('selected'));
                    e.target.getElement().classList.add('selected');
                    showFloatingDetail(ad);
                    profileMapInstance.panTo([lat - 0.01, lng]);
                });

                markersClusterGroup.addLayer(marker);
            }
        });

        profileMapInstance.addLayer(markersClusterGroup);

        if (markersClusterGroup.getLayers().length > 0) {
            profileMapInstance.fitBounds(markersClusterGroup.getBounds().pad(0.1));
        }

        profileMapInstance.on('click', (e) => {
            if (e.originalEvent.target.id === 'profileMap') {
                hideFloatingDetail();
            }
        });
    }

    function showFloatingDetail(ad) {
        const detailBox = document.getElementById('mapAdDetail');
        if (!detailBox) return;

        const formatPrice = (p) => new Intl.NumberFormat().format(p);
        
        detailBox.innerHTML = `
            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-gray-100" dir="rtl">
                <div class="relative h-48 bg-gray-50 flex items-center justify-center">
                    ${ad.image ? `<img src="{{ asset('') }}${ad.image}" class="w-full h-full object-cover">` : `<i class="fa-solid fa-image text-4xl text-gray-200"></i>`}
                    <div class="absolute top-4 right-4 flex flex-col gap-2">
                        ${ad.is_featured ? '<span class="bg-red-500 text-white px-3 py-1 rounded-lg text-[10px] font-black shadow-lg">مميز</span>' : ''}
                        <span class="bg-primary text-white px-3 py-1 rounded-lg text-[10px] font-black shadow-lg">${ad.type === 'direct_purchase' ? 'شراء فوري' : 'مزاد'}</span>
                    </div>
                </div>
                <div class="p-5">
                    <span class="text-primary bg-blue-50 px-3 py-1 rounded-lg text-[10px] font-black">${ad.category ? ad.category.name : ''}</span>
                    <h4 class="mt-3 text-lg font-black text-gray-900 truncate">${ad.title}</h4>
                    <p class="text-gray-400 text-xs font-bold mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-primary"></i>
                        ${ad.city ? ad.city.name : 'السعودية'}
                    </p>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-50">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-gray-400">السعر</span>
                            <span class="text-xl font-black text-primary">${formatPrice(ad.price)} <small class="text-xs font-bold">ر.س</small></span>
                        </div>
                        <a href="/ads/${ad.id}" class="bg-primary text-white px-6 py-3 rounded-xl font-black text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">تفاصيل</a>
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

    function toggleProfileSidebar() {
        const sidebar = document.getElementById('profileSidebar');
        sidebar.classList.toggle('hidden');
        localStorage.setItem('profileSidebarOpen', !sidebar.classList.contains('hidden'));
    }

    function switchTab(tabId) {
        // Hide all panes
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
        // Show target pane
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        
        // Update buttons
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active', 'bg-primary', 'text-white', 'shadow-xl', 'shadow-blue-100');
            b.classList.add('bg-white', 'text-gray-500', 'border-gray-100');
        });
        
        const activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.add('active', 'bg-primary', 'text-white', 'shadow-xl', 'shadow-blue-100');
        activeBtn.classList.remove('bg-white', 'text-gray-500', 'border-gray-100');
    }

    function shareProfile() {
        if (navigator.share) {
            navigator.share({
                title: 'الملف الشخصي لـ {{ $user->name }}',
                text: 'شاهد إعلانات وعروض {{ $user->name }} على منصة مزاد',
                url: window.location.href
            });
        }
    }

    function toggleReportModal() {
        @if(!Auth::check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif
        const modal = document.getElementById('reportModal');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }
    function deleteMember(id, name) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: `سيتم حذف المندوب/العضو "${name}" بالكامل وإلغاء صلاحية وصوله للمنصة.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'rounded-[32px]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform AJAX delete request
                fetch(`/settings/team/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'تم الحذف!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[32px]' }
                        }).then(() => {
                            // Redirect to team management index
                            window.location.href = "{{ route('team.index') }}";
                        });
                    } else {
                        Swal.fire('خطأ', data.message || 'فشل حذف عضو الفريق', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('خطأ', 'حدث خلل في الاتصال بالخادم.', 'error');
                });
            }
        });
    }
</script>
@endpush
