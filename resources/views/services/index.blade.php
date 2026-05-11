@extends('layouts.main')

@section('title', 'خدمات مقترحة - ' . ($ad->title ?? ''))

@section('content')
<div class="services-page bg-white min-h-screen bg-[#F8FAFC] pb-20" >
    <!-- Breadcrumbs Section (Matching Screenshot) -->
    <div class="container mx-auto px-4 md:px-16 pt-12 pb-6">
        <div class="flex flex-col gap-6">
            <!-- Breadcrumbs Pill -->
            <nav class="bg-primary px-6 py-3 w-auto md:w-fit rounded-full flex items-center gap-3 text-white text-sm font-bold shadow-lg shadow-primary/20">
                <a href="{{ route('home') }}" class="hover:text-blue-100 transition-colors">الرئيسية</a>
                <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                <a href="{{ route('search', ['category' => $ad->category_id]) }}" class="hover:text-blue-100 transition-colors">{{ $ad->category->name ?? 'سيارات' }}</a>
                <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                <a href="{{ route('ads.show', $ad->id) }}" class="hover:text-blue-100 transition-colors">{{ \Illuminate\Support\Str::limit($ad->title, 20) }}</a>
                <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                <span class="text-blue-100">خدمات مقترحة لهذا المنتج</span>
            </nav>

            <!-- Page Title -->
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">خدمات مقترحة لهذا المنتج</h1>
        </div>
    </div>

    <!-- Page Header (Action Bar) -->
    <div class="container mx-auto px-4 md:px-16 mb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <!-- Search & Filters Bar -->
            <div class=" p-2.5 rounded-[24px] flex items-center gap-3 w-full">
                <!-- Search Input Side (Right in RTL) -->
                <form action="{{ route('ads.services', $ad->id) }}" method="GET" class="relative flex-1">
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    @if(request('view')) <input type="hidden" name="view" value="{{ request('view') }}"> @endif
                    <i class="fa-solid fa-magnifying-glass absolute right-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث عن..." class="bg-gray-50 border-none rounded-2xl py-4 pr-12 pl-4 focus:ring-2 focus:ring-primary/20 font-bold text-sm">
                </form>
                
                <!-- Action Buttons Side (Left in RTL) -->
                <div class="flex items-center gap-3 pr-2 ">
                    <!-- View Mode Toggle -->
                    <div class="flex items-center gap-1.5 p-1 bg-gray-50 rounded-2xl">
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" 
                           class="w-11 h-11 flex items-center justify-center rounded-xl transition-all {{ request('view', 'grid') == 'grid' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600' }}">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" 
                           class="w-11 h-11 flex items-center justify-center rounded-xl transition-all {{ request('view') == 'list' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600' }}">
                            <i class="fa-solid fa-list-ul"></i>
                        </a>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="relative sort-dropdown-container">
                        <button onclick="document.getElementById('sortMenu').classList.toggle('hidden')" 
                                class="flex items-center gap-3 px-6 py-4 bg-gray-50 rounded-2xl text-gray-500 font-black text-sm hover:bg-gray-100 transition-all">
                            <i class="fa-solid fa-arrow-down-wide-short"></i>
                            <span>{{ request('sort') == 'price_high' ? 'الأعلى سعراً' : (request('sort') == 'price_low' ? 'الأقل سعراً' : 'الترتيب') }}</span>
                        </button>
                        
                        <div id="sortMenu" class="hidden absolute left-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 overflow-hidden animate-in fade-in zoom-in duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="flex items-center px-4 py-3 hover:bg-gray-50 text-sm font-bold text-gray-600 {{ request('sort', 'newest') == 'newest' ? 'text-primary bg-blue-50' : '' }}">الأحدث</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" class="flex items-center px-4 py-3 hover:bg-gray-50 text-sm font-bold text-gray-600 {{ request('sort') == 'price_high' ? 'text-primary bg-blue-50' : '' }}">الأعلى سعراً</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" class="flex items-center px-4 py-3 hover:bg-gray-50 text-sm font-bold text-gray-600 {{ request('sort') == 'price_low' ? 'text-primary bg-blue-50' : '' }}">الأقل سعراً</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Container -->
    <div class="container mx-auto px-4 md:px-16">
        @if($services->isEmpty())
            <div class="bg-white rounded-[40px] p-20 text-center border border-dashed border-gray-200 shadow-sm">
                <div class="w-24 h-24 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-wrench text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">لا توجد خدمات حالياً</h3>
                <p class="text-gray-400 font-bold">لم نتمكن من العثور على خدمات مقترحة لهذا المنتج في الوقت الحالي.</p>
                <a href="{{ route('ads.show', $ad->id) }}" class="inline-flex items-center gap-2 mt-8 px-8 py-4 bg-primary text-white rounded-2xl font-black hover:shadow-lg hover:shadow-primary/20 transition-all">
                    العودة للإعلان <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        @else
            <div id="servicesWrapper" class="{{ request('view') == 'list' ? 'flex flex-col gap-6' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8' }}">
                @foreach($services as $service)
                    <div class="service-item-container {{ request('view') == 'list' ? 'list-view-item' : '' }}">
                        @include('partials.service-card', ['service' => $service])
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-16 flex justify-center">
                {{ $services->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</div>

<style>
    /* List View Styling */
    .list-view-item .service-card {
        display: flex !important;
        flex-direction: row !important;
        height: 220px !important;
        max-width: 100% !important;
    }
    .list-view-item .service-image {
        width: 300px !important;
        height: 100% !important;
        flex-shrink: 0 !important;
    }
    .list-view-item .service-body {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        padding: 2rem !important;
    }
    .list-view-item .service-footer {
        padding: 0 2rem 2rem !important;
        border: none !important;
    }

    @media (max-width: 768px) {
        .list-view-item .service-card {
            flex-direction: column !important;
            height: auto !important;
        }
        .list-view-item .service-image {
            width: 100% !important;
            height: 200px !important;
        }
    }

    /* Animation */
    .animate-in {
        animation: animateIn 0.3s ease-out;
    }
    @keyframes animateIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const container = document.querySelector('.sort-dropdown-container');
        const menu = document.getElementById('sortMenu');
        if (container && !container.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>
@endsection
