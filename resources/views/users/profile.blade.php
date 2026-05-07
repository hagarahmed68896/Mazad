@extends('layouts.main')

@section('title', 'الملف الشخصي | ' . $user->name)

@push('styles')
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
</style>
@endpush

@section('content')
<div class="profile-page pb-20" dir="rtl">
    <!-- Profile Banner -->
    <div class="profile-banner bg-gray-900">
        @php
            $latestAd = $user->ads()->latest()->first();
            $coverImage = $user->cover ? asset($user->cover) : ($latestAd ? asset($latestAd->image) : asset('images/laptop.png'));
        @endphp
        <img src="{{ $coverImage }}" alt="cover">
        
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
                        <img src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=1A73E8&color=fff&size=256' }}" 
                             class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Info & Actions -->
                <div class="flex-1 text-center md:text-right">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                        <div>
                            <h1 class="text-3xl md:text-5xl font-black text-gray-900 mb-2">{{ $user->name }}</h1>
                            <p class="text-lg text-gray-400 font-bold flex items-center justify-center md:justify-start gap-2">
                                <i class="fa-solid fa-building text-primary/40"></i>
                                {{ $user->company_name ?? 'شركة أوطان للاستثمار' }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center gap-3">
                            <button class="bg-primary text-white px-10 py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-3">
                                <i class="fa-solid fa-comments"></i> الدردشة
                            </button>
                            <button onclick="shareProfile()" class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:text-primary hover:bg-blue-50 transition-all border border-gray-100">
                                <i class="fa-solid fa-share-nodes text-xl"></i>
                            </button>
                            <button class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:text-red-500 hover:bg-red-50 transition-all border border-gray-100">
                                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Row -->
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
                            {{ $user->bio ?? 'هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربى، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى إضافة إلى زيادة عدد الحروف التى يولدها التطبيق. إذا كنت تحتاج إلى عدد أكبر من الفقرات يتيح لك مولد النص العربى زيادة عدد الفقرات كما تريد.' }}
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
                                <button onclick="toggleProfileSidebar()" class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-2xl flex items-center justify-center text-gray-400 hover:text-primary hover:bg-blue-50 transition-all shrink-0">
                                    <i class="fa-solid fa-sliders"></i>
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
                                    <div class="relative">
                                        <select name="city" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm appearance-none">
                                            <option value="">كل المواقع</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-chevron-down absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                    </div>
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
                                    <h5 class="font-black text-gray-900">{{ $review->user->name }}</h5>
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
@endsection

@push('scripts')
<script>
    // Initialize gallery state on load
    document.addEventListener('DOMContentLoaded', () => {
        // Check if we should open the products tab (if filters are active)
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.has('q') || urlParams.has('type') || urlParams.has('city') || 
                          urlParams.has('category') || urlParams.has('ad_status') || urlParams.has('sort') ||
                          window.location.hash === '#tab-ads';
        
        if (hasFilters) {
            switchTab('ads');
            // If filters are active, keep the sidebar visible if it was opened
            if(localStorage.getItem('profileSidebarOpen') === 'true') {
                document.getElementById('profileSidebar').classList.remove('hidden');
            }
        }
    });

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
</script>
@endpush
