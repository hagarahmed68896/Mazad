@extends('layouts.main')

@section('title', $ad->title)

@section('content')
<div class="ad-details-page py-8 bg-white" dir="rtl">
    <!-- Main Content Container -->
    <div class="container px-4 md:px-16 mx-auto">
        <!-- Breadcrumbs & Actions Row -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <nav class="text-gray-500 text-sm font-medium">
                <ol class="inline-flex items-center space-x-reverse space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a></li>
                    <li><i class="fa-solid fa-chevron-left text-[10px] mx-1 opacity-50"></i></li>
                    <li><a href="{{ route('search', ['category' => $ad->category_id]) }}" class="hover:text-primary transition-colors">{{ $ad->category->name ?? '' }}</a></li>
                    <li><i class="fa-solid fa-chevron-left text-[10px] mx-1 opacity-50"></i></li>
                    <li class="text-gray-900 font-bold">{{ $ad->title }}</li>
                </ol>
            </nav>
            @php
                $is_favorited = Auth::check() && Auth::user()->favorites()->where('ad_id', $ad->id)->exists();
                $is_look_later = Auth::check() && Auth::user()->lookLaters()->where('ad_id', $ad->id)->exists();

                $has_joined = Auth::check() && \App\Models\Bid::where('ad_id', $ad->id)->where('user_id', Auth::id())->where('type', 'deposit')->exists();
                $my_active_bid = Auth::check() ? \App\Models\Bid::where('ad_id', $ad->id)->where('user_id', Auth::id())->where('type', 'bid')->where('status', 'active')->first() : null;
                $my_active_tender = Auth::check() ? \App\Models\Bid::where('ad_id', $ad->id)->where('user_id', Auth::id())->where('type', 'tender')->where('status', 'active')->first() : null;

                // Global Price & Fee Calculations (Safe lookup to prevent 500 errors)
                $comm_rate = (float) (\App\Models\Setting::where('key', 'commission_rate')->value('value') ?? 2.5);
                if($comm_rate > 50) $comm_rate = 2.5; 
                
                $vat_rate = 15; 
                $admin_fees = (float) (\App\Models\Setting::where('key', 'admin_fees')->value('value') ?? 19);
                $transfer_fees = (float) (\App\Models\Setting::where('key', 'transfer_fees')->value('value') ?? 500);

                $saai_val = $ad->price * ($comm_rate / 100);
                $vat_val = $saai_val * ($vat_rate / 100);
                $total_price = $ad->price + $saai_val + $vat_val + $admin_fees + $transfer_fees;
                $total_purchase_price = $ad->price + $admin_fees + $transfer_fees;
            @endphp
            <div class="flex gap-2">
                @if((!Auth::check() || Auth::id() != $ad->user_id) && (!Auth::check() || Auth::user()->account_type !== 'partner'))
                <button onclick="requireAuth(() => toggleGlobalWishlist(event, {{ $ad->id }}))" id="wishlistBtn" class="flex items-center gap-2 px-5 py-2.5 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-all text-sm font-bold text-gray-700">
                    <i class="{{ $is_favorited ? 'fa-solid' : 'fa-regular' }} fa-heart wishlist-icon-{{ $ad->id }}" style="{{ $is_favorited ? 'color: #EF4444 !important;' : 'color: #1A73E8;' }}"></i> 
                    <span id="wishlistText">{{ $is_favorited ? 'في المفضلة' : 'أضف للمفضلة' }}</span>
                </button>

                <button onclick="requireAuth(() => toggleGlobalLookLater(event, {{ $ad->id }}))" id="lookLaterBtn" class="flex items-center gap-2 px-5 py-2.5 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-all text-sm font-bold text-gray-700">
                    <i class="{{ $is_look_later ? 'fa-solid' : 'fa-regular' }} fa-bookmark look-later-icon-{{ $ad->id }}" style="{{ $is_look_later ? 'color: #1A73E8 !important;' : 'color: #1A73E8;' }}"></i> 
                    <span id="lookLaterText">{{ $is_look_later ? 'محفوظ للاطلاع لاحقاً' : 'حفظ للاطلاع لاحقاً' }}</span>
                </button>
                @endif
                <button onclick="shareAd()" class="flex items-center gap-2 px-5 py-2.5 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-all text-sm font-bold text-gray-700">
                    <i class="fa-solid fa-share-nodes text-primary"></i> مشاركة
                </button>
                @if(Auth::check() && Auth::id() == $ad->user_id)
                <a href="{{ route('ads.edit', $ad->id) }}" class="flex items-center gap-2 px-5 py-2.5 bg-white rounded-2xl border border-primary hover:shadow-md transition-all text-sm font-bold text-primary">
                    <i class="fa-solid fa-pen-to-square"></i> تعديل الإعلان
                </a>
                @endif
            </div>
        </div>

        <!-- Ad Title Header -->
        <div class="mb-8">
            <div class="flex flex-wrap items-center gap-3 mb-2">
                <h1 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">{{ $ad->title }}</h1>
                <div class="flex gap-2">
                    <span class="bg-[#F59E0B20] text-[#F59E0B] px-4 py-1 rounded-full text-xs font-bold border border-[#F59E0B30]">
                        @if($ad->type == 'electronic_auction') إلكتروني 
                        @elseif($ad->type == 'hybrid_auction') هجين
                        @elseif($ad->type == 'direct_purchase') شراء فوري
                        @else مناقصة @endif
                    </span>
                    @if($ad->is_featured)
                    <span class="bg-[#EF4444] text-white px-4 py-1 rounded-full text-xs font-bold flex items-center gap-1 shadow-lg shadow-red-200">
                        <i class="fa-solid fa-fire"></i> مميز
                    </span>
                    @endif
                </div>
            </div>
        </div>

        @php 
            $additionalImages = $ad->images;
            $allImages = collect([$ad->image])
                ->merge($additionalImages->pluck('image'))
                ->filter()
                ->unique()
                ->map(fn($img) => Str::startsWith($img, 'http') ? $img : '/' . ltrim($img, '/'))
                ->values();
        @endphp

        <!-- Gallery Grid -->
        <div class="grid grid-cols-1 {{ $allImages->count() > 1 ? 'lg:grid-cols-3' : '' }} gap-5 mb-10">
            <!-- Main Image -->
            <a href="{{ route('ads.gallery', ['id' => $ad->id, 'index' => 0]) }}" class="{{ $allImages->count() > 1 ? 'lg:col-span-2' : '' }} aspect-[16/10] lg:aspect-auto lg:h-[450px] rounded-[32px] overflow-hidden border border-gray-100 shadow-sm cursor-pointer group relative bg-slate-50">
                @if($ad->image)
                    <img id="mainAdImage" src="{{ Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/') }}" class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-105">
                @else
                    <div class="w-full h-full bg-gray-50 flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-image text-6xl"></i>
                    </div>
                @endif
                <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <i class="fa-solid fa-expand text-white text-3xl drop-shadow-lg"></i>
                </div>
            </a>
            @if($allImages->count() > 1)
            <!-- Secondary Images -->
            <div class="hidden lg:flex flex-col gap-5 h-[450px]">
                @if($allImages->count() > 1)
                    <a href="{{ route('ads.gallery', ['id' => $ad->id, 'index' => 1]) }}" class="{{ $allImages->count() > 2 ? 'h-1/2' : 'h-full' }} w-full rounded-[32px] overflow-hidden border border-gray-100 shadow-sm cursor-pointer group relative bg-slate-50">
                        <img src="{{ $allImages[1] }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                        </div>
                    </a>
                @endif

                @if($allImages->count() > 2)
                    <a href="{{ route('ads.gallery', ['id' => $ad->id, 'index' => 2]) }}" class="h-1/2 w-full rounded-[32px] overflow-hidden border border-gray-100 shadow-sm relative group cursor-pointer bg-slate-50">
                        <img src="{{ $allImages[2] }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px] flex items-center justify-center transition-all group-hover:bg-black/50">
                            <div class="bg-white/95 backdrop-blur px-5 py-2.5 rounded-2xl flex items-center gap-2.5 shadow-2xl transform transition-all group-hover:scale-110">
                                <i class="fa-solid fa-images text-gray-900 text-lg"></i>
                                <span class="text-gray-900 font-black text-base">{{ count($allImages) }} صور</span>
                            </div>
                        </div>
                    </a>
                @endif
            </div>
            @endif
        </div>

        <!-- Sub Title & Category Row -->
        <div class="mb-12">
            @if($ad->category)
                <span class="bg-blue-50 text-primary px-5 py-1.5 rounded-xl text-sm font-bold mb-4 inline-block border border-blue-100">{{ $ad->category->name }}</span>
            @endif
            <h2 class="text-2xl md:text-4xl font-black text-gray-900 mb-4">{{ $ad->title }}</h2>
            <div class="flex items-center gap-3">
                <span class="text-2xl md:text-[48px] font-black text-primary">{{ number_format($ad->price) }}</span>
                <div class="relative flex items-center justify-center">
                    <img src="{{ asset('images/Vector-blue.svg') }}" class="w-10 h-10 md:w-12 md:h-12">
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (Full Width) -->
        <div class="flex gap-3 mb-10 overflow-x-auto no-scrollbar py-2">
            <button onclick="switchTab('description')" id="btn-description" class="tab-btn active px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-primary text-white shadow-xl shadow-blue-100">نبذة عن الإعلان</button>
            <button onclick="switchTab('details')" id="btn-details" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20">التفاصيل</button>
            <button onclick="switchTab('location')" id="btn-location" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20">العنوان ووقت المعاينة</button>
            <button onclick="switchTab('seller')" id="btn-seller" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20"> بيانات المعلن</button>
            <button onclick="switchTab('reviews')" id="btn-reviews" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20">التقييمات</button>
            <button onclick="switchTab('questions')" id="btn-questions" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20">الأسئلة والأجوبة</button>
            <button onclick="switchTab('attachments')" id="btn-attachments" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20">المرفقات</button>
        </div>

        <!-- Main Content Area with Sticky Sidebar -->
        <div class="flex flex-col lg:flex-row gap-10">
            <!-- Right: Content (70%) -->
            <div class="w-full lg:w-[68%]">
                <!-- Tab Panels -->
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 md:p-12 mb-20 min-h-[500px]">
                    <!-- Description Panel -->
                    <div id="tab-description" class="tab-content transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            نبذة عن الإعلان
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="text-gray-600 leading-loose text-lg font-medium">
                            {{ $ad->description }}
                        </div>
                    </div>

                    <!-- Details Panel -->
                    <div id="tab-details" class="tab-content hidden transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            المواصفات والتفاصيل
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($ad->adProperties as $ap)
                                @if(!str_contains($ap->property->label, 'معاينة'))
                                    @php
                                        $isOwnerOrAdmin = auth()->check() && (auth()->id() == $ad->user_id || auth('admin')->check());
                                        $shouldShow = !$ap->is_hidden || $isOwnerOrAdmin;
                                    @endphp
                                    @if($shouldShow)
                                        <div class="flex justify-between items-center p-6 bg-gray-50 rounded-2xl border border-gray-100/50">
                                            <span class="text-gray-500 font-bold">
                                                {{ $ap->property->label }}
                                                @if($ap->is_hidden)
                                                    <span class="text-xs text-rose-500 font-bold">(مخفي للمستخدمين)</span>
                                                @endif
                                            </span>
                                            <span class="text-gray-900 font-black">{{ $ap->value }}</span>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                            <div class="flex justify-between items-center p-6 bg-gray-50 rounded-2xl border border-gray-100/50">
                                <span class="text-gray-500 font-bold">المدينة</span>
                                <span class="text-gray-900 font-black">
                                    @if($ad->city)
                                        {{ $ad->city->region ? $ad->city->region->name . ' - ' : '' }}{{ $ad->city->name }}
                                    @elseif($ad->address)
                                        {{ $ad->address }}
                                    @else
                                        غير محدد
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Location Panel -->
                    <div id="tab-location" class="tab-content hidden transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            الموقع الجغرافي والمعاينة
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="space-y-6">
                            <div class="flex items-center gap-5 p-7 bg-primary/5 rounded-[32px] border border-primary/10">
                                <div class="w-16 h-16 bg-primary text-white rounded-2xl flex items-center justify-center text-2xl shadow-xl shadow-blue-100">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 font-bold mb-1">العنوان بالتفصيل</div>
                                    <div class="font-black text-xl text-gray-900">{{ $ad->address }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-5 p-7 bg-amber-50/50 rounded-[32px] border border-amber-100/50">
                                <div class="w-16 h-16 bg-amber-500 text-white rounded-2xl flex items-center justify-center text-2xl shadow-xl shadow-amber-100">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 font-bold mb-1">وقت المعاينة</div>
                                    @if($ad->inspection_end_date && $ad->inspection_date)
                                        <div class="font-black text-lg text-gray-900">
                                            {{ \Carbon\Carbon::parse($ad->inspection_date)->translatedFormat('l j M') }}
                                            <span class="text-primary mx-1">→</span>
                                            {{ \Carbon\Carbon::parse($ad->inspection_end_date)->translatedFormat('l j M') }}
                                        </div>
                                        @if($ad->inspection_info)
                                        <div class="text-sm text-gray-500 font-bold mt-1">{{ $ad->inspection_info }}</div>
                                        @else
                                        <div class="text-sm text-gray-500 font-bold mt-1">
                                            من {{ \Carbon\Carbon::parse($ad->inspection_date)->translatedFormat('g:i A') }}
                                            حتى {{ \Carbon\Carbon::parse($ad->inspection_end_date)->translatedFormat('g:i A') }}
                                            يومياً
                                        </div>
                                        @endif
                                    @elseif($ad->inspection_info)
                                        <div class="font-black text-xl text-gray-900">{{ $ad->inspection_info }}</div>
                                    @elseif($ad->inspection_date)
                                        <div class="font-black text-xl text-gray-900">
                                            {{ \Carbon\Carbon::parse($ad->inspection_date)->translatedFormat('l j M Y - g:i A') }}
                                        </div>
                                    @else
                                        <div class="font-black text-xl text-gray-900">يتم التنسيق مع المعلن</div>
                                    @endif
                                </div>
                            </div>
                            <div id="adMap" class="h-[450px] bg-gray-100 rounded-[40px] overflow-hidden relative border-[12px] border-white shadow-2xl z-0">
                                 <!-- Map will be initialized here -->
                                 <div class="absolute bottom-6 right-6 z-[1000] bg-white/95 backdrop-blur-md px-10 py-4 rounded-full shadow-2xl font-black text-lg border border-primary/10">
                                    <i class="fa-solid fa-location-dot text-primary ml-2"></i> {{ $ad->address }}
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Panel -->
                    <div id="tab-attachments" class="tab-content hidden transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            المرفقات
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="space-y-4">
                            @forelse($ad->attachments as $file)
                            <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[28px] hover:bg-gray-100/80 transition-all border border-gray-100/50 group">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-gray-100 relative">
                                        @if(strtolower($file->file_type) == 'pdf')
                                            <i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded-md">PDF</span>
                                        @else
                                            <i class="fa-solid fa-file text-primary text-2xl"></i>
                                            <span class="absolute -top-1 -right-1 bg-primary text-white text-[8px] font-black px-1.5 py-0.5 rounded-md uppercase">{{ $file->file_type }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black text-gray-900 mb-0.5">{{ $file->file_name }}</h4>
                                        <p class="text-gray-400 font-bold text-xs">{{ $file->file_size }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset($file->file_path) }}" download class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-gray-400 hover:text-primary hover:shadow-md transition-all border border-gray-100">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                            @empty
                            <div class="py-20 border-2 border-dashed border-gray-100 rounded-[32px] flex flex-col items-center justify-center text-gray-400">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-folder-open text-3xl"></i>
                                </div>
                                <p class="font-bold text-lg text-gray-500">لا توجد ملفات مرفقة بهذا الإعلان</p>
                                <p class="text-sm">سيتم إضافة الملفات من قبل المعلن قريباً</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Seller Info Panel -->
                    @php
                        $seller = $ad->user;
                        // Check if seller is a representative (has a parent company)
                        $parentCompany = $seller->parent_id ? $seller->company()->first() : null;
                        $isRepresentative = ($seller->account_type === 'representative' || $seller->account_type === 'delegate') && $parentCompany;
                        if (!$isRepresentative && $seller->parent_id && $parentCompany) {
                            $isRepresentative = true;
                        }
                        if (Auth::check() && Auth::id() == $ad->user_id) {
                            $reviews = $ad->reviews()->latest()->get();
                        } else {
                            $reviews = $ad->reviews()->where('is_visible', true)->latest()->get();
                        }
                        $totalReviews = $reviews->count();
                        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0;
                        
                        $starCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                        foreach($reviews as $r) {
                            $rating = (int)$r->rating;
                            if(isset($starCounts[$rating])) $starCounts[$rating]++;
                        }
                        
                        $sellerCategories = \App\Models\Category::whereIn('id', $seller->ads->pluck('category_id'))->limit(5)->get();
                    @endphp
                    <div id="tab-seller" class="tab-content hidden transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            بيانات المعلن
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="bg-white rounded-[32px] p-8 border border-gray-100 shadow-sm">
                            <div class="flex items-start gap-6 mb-8">
                                <a href="{{ route('users.profile', $seller->id) }}" class="relative group cursor-pointer">
                                    <img src="{{ $seller->avatar ? asset($seller->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($seller->name).'&background=1A73E8&color=fff&size=128' }}" class="w-24 h-24 rounded-[24px] object-cover shadow-lg border-4 border-white transition-transform group-hover:scale-105">
                                </a>
                                <div class="flex-1">
                                    <a href="{{ route('users.profile', $seller->id) }}" class="hover:text-primary transition-colors">
                                        <h4 class="text-2xl font-black text-gray-900 mb-1">{{ $seller->name }}</h4>
                                    </a>
                                    @if($isRepresentative && $parentCompany)
                                        <p class="text-gray-400 font-bold mb-2" style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                                            <span style="background:#EBF3FD; color:#1A73E8; border-radius:8px; padding:2px 10px; font-size:0.82rem; font-weight:800; display:inline-flex; align-items:center; gap:0.3rem;">
                                                <i class="fa-solid fa-building" style="font-size:0.75rem;"></i>
                                                مندوب شركة
                                            </span>
                                            <a href="{{ route('users.profile', $parentCompany->id) }}" style="color:#1A73E8; font-weight:900; text-decoration:none; font-size:0.95rem;" class="hover:underline">
                                                {{ $parentCompany->company_name ?? $parentCompany->name }}
                                            </a>
                                        </p>
                                    @elseif($seller->account_type == 'company')
                                        <p class="text-gray-400 font-bold mb-3">{{ $seller->company_name ?? 'شركة معتمدة' }}</p>
                                    @else
                                        <p class="text-gray-400 font-bold mb-3">عضو فردي</p>
                                    @endif
                                    <a href="{{ route('users.profile', $seller->id) }}" class="text-primary font-black text-sm hover:underline flex items-center gap-2">
                                        عرض جميع الإعلانات <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mb-8 text-gray-500 font-medium leading-relaxed">
                                <p class="line-clamp-3">
                                    @if($isRepresentative && $parentCompany)
                                        {{ $seller->name }} مندوب معتمد لدى شركة {{ $parentCompany->company_name ?? $parentCompany->name }}، 
                                        عضو نشط في منصة مزاد منذ {{ $seller->created_at->diffForHumans() }}.
                                        قام بإضافة {{ $seller->ads()->count() }} إعلان حتى الآن،
                                        ويتمتع بسمعة طيبة بناءً على تقييمات العملاء السابقة.
                                    @else
                                        {{ $seller->name }} عضو نشط في منصة مزاد {{ $seller->created_at->diffForHumans() }}. 
                                        قام بإضافة {{ $seller->ads()->count() }} إعلان حتى الآن، 
                                        ويتمتع بسمعة طيبة بناءً على تقييمات العملاء السابقة.
                                    @endif
                                </p>
                                <button class="text-primary font-black text-sm mt-2">المزيد</button>
                            </div>

                            @if(!Auth::check() || Auth::id() != $ad->user_id)
                            <form id="startChatForm" action="{{ route('chats.start') }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="ad_id" value="{{ $ad->id }}">
                            </form>
                            <button type="button" onclick="requireAuth(() => document.getElementById('startChatForm').submit())" class="w-full bg-primary text-white font-black py-5 rounded-[20px] hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center justify-center gap-3 text-lg mb-8">
                                <i class="fa-solid fa-comment-dots"></i> الدردشة
                            </button>
                            @endif

                            <div>
                                <h5 class="text-gray-900 font-black mb-4 flex items-center gap-2">
                                    الفئات لهذا المعلن
                                </h5>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($sellerCategories as $sCat)
                                        <span class="bg-blue-50 text-primary px-5 py-2 rounded-xl text-sm font-bold border border-blue-100">{{ $sCat->name }}</span>
                                    @empty
                                        <span class="text-gray-400 text-sm italic">لا توجد فئات محددة</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Panel -->
                    <div id="tab-reviews" class="tab-content hidden transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            التقييمات
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="bg-white rounded-[32px] p-8 border border-gray-100 shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10 items-center">
                                <div class="text-center md:text-right">
                                    <div class="text-6xl font-black text-gray-900 mb-2">{{ $avgRating }}</div>
                                    <div class="flex items-center justify-center md:justify-start gap-1 text-yellow-400 mb-2">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="fa-{{ $i <= $avgRating ? 'solid' : 'regular' }} fa-star"></i>
                                        @endfor
                                    </div>
                                    <p class="text-gray-400 font-bold">{{ $totalReviews }} تقييم</p>
                                </div>
                                <div class="space-y-3">
                                    @foreach([5,4,3,2,1] as $star)
                                    @php
                                        $percent = $totalReviews > 0 ? round(($starCounts[$star] / $totalReviews) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-4">
                                        <span class="text-gray-400 font-bold text-xs w-8 text-left">%{{ $percent }}</span>
                                        <div class="flex-1 h-2.5 bg-gray-50 rounded-full overflow-hidden">
                                            <div class="h-full bg-gray-900 rounded-full" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-[30px]">
                                            <span class="text-gray-900 font-black text-sm">{{ $star }}</span>
                                            <i class="fa-solid fa-star text-yellow-400 text-[10px]"></i>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="space-y-4">
                                @forelse($reviews->take(5) as $review)
                                <div class="p-6 bg-gray-50/50 rounded-[24px] border border-gray-100 relative group transition-all hover:bg-white hover:shadow-md">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-primary text-white rounded-xl flex items-center justify-center font-black">
                                                {{ mb_substr($review->reviewer->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h5 class="text-gray-900 font-black">{{ $review->reviewer->name }}</h5>
                                                    @if(Auth::check() && Auth::id() == $ad->user_id)
                                                        @if(!$review->is_visible)
                                                            <span id="review-status-badge-{{ $review->id }}" class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-bold ring-1 ring-amber-100 mr-2">مخفي</span>
                                                        @else
                                                            <span id="review-status-badge-{{ $review->id }}" class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold ring-1 ring-emerald-100 mr-2">منشور</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="flex text-yellow-400 text-[10px] mt-1">
                                                    @for($i=1; $i<=5; $i++)
                                                        <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-gray-400 text-xs font-bold">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-500 text-sm font-medium">{{ $review->comment }}</p>

                                    <!-- Owner Reply Display -->
                                    @if($review->reply)
                                    <div class="mt-4 mr-6 p-4 bg-primary/5 rounded-[20px] border border-primary/10 relative">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-black text-xs text-primary flex items-center gap-2">
                                                <i class="fa-solid fa-reply-all"></i> ردك (المعلن):
                                            </span>
                                            @if(Auth::check() && Auth::id() == $ad->user_id)
                                                <button onclick="showReplyForm({{ $review->id }})" class="text-[10px] text-gray-400 hover:text-primary transition-all font-bold">تعديل الرد</button>
                                            @endif
                                        </div>
                                        <p class="text-gray-700 text-xs font-bold leading-relaxed">{{ $review->reply }}</p>
                                    </div>
                                    @endif

                                    <!-- Owner Actions Panel (Publish/Hide and Reply) -->
                                    @if(Auth::check() && Auth::id() == $ad->user_id)
                                        <div class="mt-4 flex flex-wrap items-center gap-3 pt-4 border-t border-gray-100">
                                            <!-- Visibility Toggle Button -->
                                            <button onclick="toggleReviewVisibility({{ $review->id }}, this)" class="text-xs font-black px-4 py-2.5 rounded-xl border {{ $review->is_visible ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }} transition-all flex items-center gap-1.5">
                                                <i class="fa-solid {{ $review->is_visible ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                <span>{{ $review->is_visible ? 'إخفاء التقييم' : 'نشر التقييم' }}</span>
                                            </button>

                                            <!-- Reply Button -->
                                            @if(!$review->reply)
                                                <button onclick="showReplyForm({{ $review->id }})" class="text-xs font-black px-4 py-2.5 rounded-xl border border-primary text-primary hover:bg-primary/5 transition-all flex items-center gap-1.5">
                                                    <i class="fa-solid fa-reply"></i>
                                                    <span>الرد على التقييم</span>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Reply Form (Hidden by default, used for both Add and Edit) -->
                                        <div id="reply-form-{{ $review->id }}" class="hidden mt-4 bg-white p-5 rounded-[20px] border border-gray-100 shadow-sm animate-in fade-in slide-in-from-top-3 duration-200">
                                            <form action="{{ route('reviews.reply', $review->id) }}" method="POST">
                                                @csrf
                                                <h6 class="font-black text-gray-900 mb-2 text-sm">{{ $review->reply ? 'تعديل الرد على التقييم:' : 'اكتب ردك على التقييم:' }}</h6>
                                                <textarea name="reply" rows="3" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium" placeholder="اكتب ردك هنا..." required>{{ $review->reply }}</textarea>
                                                <div class="flex justify-end gap-2 mt-3">
                                                    <button type="button" onclick="hideReplyForm({{ $review->id }})" class="px-4 py-2 text-xs font-bold text-gray-500 hover:bg-gray-100 rounded-lg">إلغاء</button>
                                                    <button type="submit" class="bg-primary text-white px-5 py-2 text-xs font-black rounded-lg shadow hover:bg-blue-700 transition-all">إرسال الرد</button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                @empty
                                <div class="text-center py-10 text-gray-400 font-bold">لا توجد تقييمات لهذا المعلن حتى الآن</div>
                                @endforelse
                            </div>
                            @if($totalReviews > 5)
                            <div class="mt-10 flex justify-center">
                                <button class="bg-primary text-white px-10 py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                                    عرض المزيد
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Questions & Answers Panel -->
                    <div id="tab-questions" class="tab-content hidden transition-all duration-500">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-2xl font-black relative inline-block">
                                الأسئلة والأجوبة
                                <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                            </h3>
                            <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm font-bold">{{ $ad->questions->count() }} سؤال</span>
                        </div>

                        @if(!Auth::check() || Auth::id() != $ad->user_id)
                        <!-- Ask Question Form -->
                        <div class="bg-primary/5 rounded-[32px] p-8 border border-primary/10 mb-10">
                            <h4 class="text-lg font-black text-gray-900 mb-4">لديك استفسار؟ اسأل المعلن مباشرة</h4>
                            <form id="questionForm" action="{{ route('ads.question.store', $ad->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="relative">
                                    <textarea name="question" rows="3" placeholder="اكتب سؤالك هنا بالتفصيل..." class="w-full bg-white border border-gray-100 rounded-[20px] p-5 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium transition-all" required></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" onclick="requireAuth(() => document.getElementById('questionForm').submit())" class="bg-primary text-white px-8 py-3 rounded-xl font-black shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-2">
                                        إرسال السؤال <i class="fa-solid fa-paper-plane text-xs"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        <!-- Questions List -->
                        <div class="space-y-6">
                            @php
                                if (Auth::check() && Auth::id() == $ad->user_id) {
                                    $adQuestions = $ad->questions()->latest()->get();
                                } else {
                                    $adQuestions = $ad->questions()->where('is_visible', true)->latest()->get();
                                }
                            @endphp
                            @forelse($adQuestions as $q)
                            <!-- Question Item -->
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div class="flex-1 bg-gray-50 rounded-[24px] p-6 border border-gray-100 relative">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-gray-900">{{ $q->user->name }}</span>
                                                @if(Auth::check() && Auth::id() == $ad->user_id)
                                                    @if(!$q->is_visible)
                                                        <span id="question-status-badge-{{ $q->id }}" class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-bold ring-1 ring-amber-100 mr-2">مخفي</span>
                                                    @else
                                                        <span id="question-status-badge-{{ $q->id }}" class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold ring-1 ring-emerald-100 mr-2">منشور</span>
                                                    @endif
                                                @endif
                                            </div>
                                            <span class="text-xs text-gray-400 font-bold">{{ $q->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-600 font-medium leading-relaxed">{{ $q->question }}</p>
                                        <!-- Triangle Pointer -->
                                        <div class="absolute top-6 -right-2 w-4 h-4 bg-gray-50 border-t border-r border-gray-100 rotate-45"></div>
                                    </div>
                                </div>
                                
                                @if($q->answer)
                                <!-- Answer -->
                                <div class="flex gap-4 pr-12">
                                    <div class="flex-1 bg-primary/5 rounded-[24px] p-6 border border-primary/10 relative">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-black text-primary flex items-center gap-2">
                                                <i class="fa-solid fa-reply-all"></i> رد المعلن:
                                            </span>
                                            <span class="text-xs text-primary/60 font-bold">{{ $q->answered_at ? $q->answered_at->diffForHumans() : '' }}</span>
                                            @if(Auth::check() && Auth::id() == $ad->user_id)
                                                <button onclick="showQuestionReplyForm({{ $q->id }})" class="text-[10px] text-gray-400 hover:text-primary transition-all font-bold">تعديل الرد</button>
                                            @endif
                                        </div>
                                        <p class="text-gray-700 font-bold leading-relaxed">{{ $q->answer }}</p>
                                    </div>
                                    <div class="w-12 h-12 rounded-2xl bg-primary flex items-center justify-center text-white shadow-lg shadow-blue-100">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                </div>
                                @else
                                    @if(Auth::check() && Auth::id() == $ad->user_id)
                                    <!-- No reply yet, but owner can reply -->
                                    <div class="pr-16 mt-2">
                                        <button onclick="showQuestionReplyForm({{ $q->id }})" class="text-xs font-black px-4 py-2.5 rounded-xl border border-primary text-primary hover:bg-primary/5 transition-all flex items-center gap-1.5">
                                            <i class="fa-solid fa-reply"></i>
                                            <span>الرد على السؤال</span>
                                        </button>
                                    </div>
                                    @else
                                    <div class="pr-16">
                                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-[11px] text-amber-600 font-bold inline-flex items-center gap-2">
                                            <i class="fa-solid fa-clock-rotate-left"></i> في انتظار رد المعلن
                                        </div>
                                    </div>
                                    @endif
                                @endif

                                @if(Auth::check() && Auth::id() == $ad->user_id)
                                    <div class="mt-2 flex flex-wrap items-center gap-3 pr-16">
                                        <!-- Visibility Toggle Button for Question -->
                                        <button onclick="toggleQuestionVisibility({{ $q->id }}, this)" class="text-xs font-black px-4 py-2 rounded-xl border {{ $q->is_visible ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }} transition-all flex items-center gap-1.5">
                                            <i class="fa-solid {{ $q->is_visible ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            <span>{{ $q->is_visible ? 'إخفاء السؤال' : 'نشر السؤال' }}</span>
                                        </button>
                                    </div>

                                    <!-- Question Reply Form (Hidden by default) -->
                                    <div id="question-reply-form-{{ $q->id }}" class="hidden mt-3 pr-16 bg-white p-5 rounded-[20px] border border-gray-100 shadow-sm animate-in fade-in slide-in-from-top-3 duration-200">
                                        <form action="{{ route('questions.reply', $q->id) }}" method="POST">
                                            @csrf
                                            <h6 class="font-black text-gray-900 mb-2 text-sm">{{ $q->answer ? 'تعديل الرد على السؤال:' : 'اكتب ردك على السؤال:' }}</h6>
                                            <textarea name="answer" rows="3" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium" placeholder="اكتب ردك هنا..." required>{{ $q->answer }}</textarea>
                                            <div class="flex justify-end gap-2 mt-3">
                                                <button type="button" onclick="hideQuestionReplyForm({{ $q->id }})" class="px-4 py-2 text-xs font-bold text-gray-500 hover:bg-gray-100 rounded-lg">إلغاء</button>
                                                <button type="submit" class="bg-primary text-white px-5 py-2 text-xs font-black rounded-lg shadow hover:bg-blue-700 transition-all">إرسال الرد</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            
                            @if(!$loop->last)
                            <hr class="border-gray-50">
                            @endif

                            @empty
                            <div class="text-center py-20 bg-gray-50 rounded-[40px] border-2 border-dashed border-gray-100">
                                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                                    <i class="fa-solid fa-comments-question text-3xl"></i>
                                </div>
                                <p class="text-gray-400 font-bold text-lg">لا توجد أسئلة حتى الآن</p>
                                <p class="text-gray-400 text-sm">كن أول من يسأل عن هذا الإعلان!</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Left: Sidebar -->
            <div class="w-full lg:w-[32%]">
                <div class="sticky top-28">
                    <div class="bg-white rounded-[32px] p-6 shadow-xl shadow-blue-900/5 border border-gray-100 mb-8 overflow-hidden">
                        @php
                            $buyerOrder = null;
                            if(Auth::check()) {
                                $buyerOrder = \App\Models\Order::where('ad_id', $ad->id)
                                    ->where('buyer_id', Auth::id())
                                    ->first();
                            }
                        @endphp

                        @if($buyerOrder)
                            <!-- BUYER SPECIFIC CARD (Matching Image 1) -->
                            @php
                                $b_order_status = $buyerOrder->status; // pending_payment, paid, receiving, received
                                
                                $b_status_label = 'قائم';
                                $b_status_class = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                
                                if($b_order_status == 'pending_payment') {
                                    $b_status_label = 'قيد الدفع';
                                    $b_status_class = 'bg-amber-50 text-amber-600 border-amber-100';
                                } elseif($b_order_status == 'paid') {
                                    $b_status_label = 'تم الدفع';
                                    $b_status_class = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                } elseif($b_order_status == 'receiving') {
                                    $b_status_label = 'قيد الاستلام';
                                    $b_status_class = 'bg-orange-50 text-orange-500 border-orange-100';
                                } elseif($b_order_status == 'received') {
                                    $b_status_label = 'تم الاستلام';
                                    $b_status_class = 'bg-blue-50 text-blue-600 border-blue-100';
                                }
                            @endphp

                            <!-- Stats Table -->
                            <div class="border border-gray-100 rounded-2xl overflow-hidden mb-6">
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">حالة الاعلان</span>
                                    <span class="{{ $b_status_class }} px-4 py-1 rounded-full text-xs font-black border">{{ $b_status_label }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">المشاهدات</span>
                                    <span class="text-gray-900 font-black text-[15px] text-left">{{ number_format($ad->views_count) }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">المزايدات</span>
                                    <span class="text-gray-900 font-black text-[15px] text-left">{{ number_format($ad->bids()->where('type', '!=', 'deposit')->count()) }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">المفضلة</span>
                                    <span class="text-gray-900 font-black text-[15px] text-left">{{ number_format($ad->favorites()->count()) }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">السعر النهائي</span>
                                    <div class="flex items-center gap-1 justify-end text-primary">
                                        <span class="font-black text-lg">{{ number_format($buyerOrder->final_price) }}</span>
                                        <x-riyal-icon class="w-4 h-4 text-primary" />
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Code box if status is receiving -->
                            @if($b_order_status == 'receiving')
                                <div class="p-4 bg-orange-50 border border-orange-100 rounded-2xl flex items-center justify-between text-right mb-6 animate-in fade-in duration-300" style="background-color: #FFF7ED; border-color: #FED7AA; color: #EA580C;" onclick="event.stopPropagation()">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-copy cursor-pointer text-lg text-orange-500 hover:text-orange-700 transition-colors" onclick="copyAdDeliveryCode('{{ $buyerOrder->delivery_code ?? '839201' }}')" title="نسخ كود التسليم"></i>
                                        <span class="text-base font-black tracking-wider" style="font-family: monospace;">{{ $buyerOrder->delivery_code ?? '839201' }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-orange-700">كود التسليم</span>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                @if($b_order_status == 'pending_payment')
                                    <button type="button" onclick="openCardPaymentModal('{{ $buyerOrder->order_number }}', '{{ number_format($buyerOrder->final_price) }}', {{ $buyerOrder->id }})" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg">
                                        اتمام الدفع
                                    </button>
                                @elseif($b_order_status == 'receiving')
                                    <button type="button" onclick="openAdDeliveryModal('{{ $buyerOrder->delivery_code ?? '839201' }}')" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg">
                                        أرسل كود التسليم
                                    </button>
                                @elseif($b_order_status == 'received')
                                    @if($buyerOrder->review)
                                        <button type="button" disabled class="w-full bg-gray-100 text-gray-400 font-bold py-4 rounded-xl border border-gray-200 flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                            <span>تم التقييم بنجاح</span>
                                        </button>
                                    @else
                                        <button type="button" onclick="openAdRatingModal('{{ $seller->id ?? '' }}', '{{ $seller->name ?? 'البائع' }}')" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg">
                                            أقيم تجربتك
                                        </button>
                                    @endif
                                @endif
                            </div>

                        @else
                            <!-- Stats Table Section -->
                            <div class="border border-gray-100 rounded-2xl overflow-hidden mb-6">
                                @php
                                    // General Ad Status
                                    $ad_status_label = 'قائم';
                                    $ad_status_class = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                    if($ad->status == 'upcoming') {
                                        $ad_status_label = 'قادم';
                                        $ad_status_class = 'bg-blue-50 text-blue-600 border-blue-100';
                                    } elseif($ad->status == 'finished') {
                                        $ad_status_label = 'منتهي';
                                        $ad_status_class = 'bg-rose-50 text-rose-500 border-rose-100';
                                    }

                                    // Auction Specific Status
                                    $auction_status_label = null;
                                    $auction_status_class = '';
                                    $b_status = $ad->bid_status ?? $ad->status;
                                    
                                    if($ad->type == 'hybrid_auction' || $ad->type == 'electronic_auction') {
                                        if($b_status == 'upcoming') {
                                            $auction_status_label = 'لم تبدأ المزايدة';
                                            $auction_status_class = 'bg-gray-100 text-gray-500 border-gray-200';
                                        } elseif($b_status == 'active') {
                                            $auction_status_label = 'جاري';
                                            $auction_status_class = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                        } elseif($b_status == 'postponed') {
                                            $auction_status_label = 'مؤجل';
                                            $auction_status_class = 'bg-amber-50 text-amber-600 border-amber-100';
                                        } elseif($b_status == 'not_sold') {
                                            $auction_status_label = 'لم يتم البيع';
                                            $auction_status_class = 'bg-rose-50 text-rose-600 border-rose-100';
                                        } elseif($b_status == 'sold' || $b_status == 'finished') {
                                            $auction_status_label = 'تم البيع';
                                            $auction_status_class = 'bg-emerald-50 text-emerald-500 border-emerald-100';
                                        }
                                    }

                                @endphp

                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">حالة الاعلان</span>
                                    <span class="{{ $ad_status_class }} px-4 py-1 rounded-full text-xs font-black border">{{ $ad_status_label }}</span>
                                </div>

                                @if($auction_status_label)
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">حالة المزاد</span>
                                    <span class="{{ $auction_status_class }} px-4 py-1 rounded-full text-xs font-black border">{{ $auction_status_label }}</span>
                                </div>
                                @endif

                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4 sidebar-countdown" data-end="{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->toIso8601String() : '' }}">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">ينتهي في</span>
                                    <div class="text-primary font-black text-sm countdown-timer flex items-center gap-1 justify-end" dir="ltr" style="font-family: 'Courier New', Courier, monospace;">
                                        <div class="flex items-center gap-0.5"><span class="days">00</span><span>d</span></div>
                                        <span class="text-gray-300">:</span>
                                        <div class="flex items-center gap-0.5"><span class="hours">00</span><span>h</span></div>
                                        <span class="text-gray-300">:</span>
                                        <div class="flex items-center gap-0.5"><span class="minutes">00</span><span>m</span></div>
                                        <span class="text-gray-300">:</span>
                                        <div class="flex items-center gap-0.5"><span class="seconds">00</span><span>s</span></div>
                                    </div>
                                </div>

                                @if($ad->inspection_info)
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">وقت المعاينة</span>
                                    <span class="text-gray-900 font-black text-[15px] text-left">{{ $ad->inspection_info }}</span>
                                </div>
                                @endif
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">المشاهدات</span>
                                    <span class="text-gray-900 font-black text-[15px] text-left">{{ number_format($ad->views_count) }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">
                                        @if($ad->type == 'tender')
                                            المناقصات
                                        @elseif($ad->type == 'direct_purchase')
                                            العروض
                                        @else
                                            المزايدات
                                        @endif
                                    </span>
                                    <button onclick="showBidsModal()" class="text-primary font-black text-[15px] underline text-left hover:text-blue-700 transition-colors">{{ number_format($ad->bids()->where('type', '!=', 'deposit')->count()) }}</button>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 border-b border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">المفضلة</span>
                                    <span class="text-gray-900 font-black text-[15px] text-left">{{ number_format($ad->favorites()->count()) }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 {{ $ad->type == 'direct_purchase' ? '' : 'border-b' }} border-gray-50 gap-4">
                                    <span class="text-gray-900 font-black text-sm whitespace-nowrap">
                                        @if($ad->type == 'tender')
                                            السعر الحالي
                                        @elseif($ad->type == 'direct_purchase')
                                            سعر الشراء
                                        @elseif($b_status == 'sold' || $b_status == 'finished')
                                            السعر النهائي
                                        @else
                                            السعر الحالي
                                        @endif
                                    </span>
                                    <div class="flex items-center gap-1 justify-end text-gray-900">
                                        <span class="font-black text-lg">{{ number_format($ad->price) }}</span>
                                        <x-riyal-icon class="w-4 h-4" />
                                    </div>
                                </div>
                                
                                @if($ad->type != 'direct_purchase' || ($b_status == 'sold' || $b_status == 'finished'))
                                <div class="grid grid-cols-[1fr_auto] items-center p-4 bg-gray-50/30 gap-4">
                                    <span class="text-gray-900 font-black text-base whitespace-nowrap">السعر الإجمالي</span>
                                    <div class="flex items-center gap-2 justify-end">
                                        <i onclick="showPriceModal()" class="fa-solid fa-circle-info text-gray-400 text-sm cursor-pointer hover:text-primary transition-colors"></i>
                                        <div class="flex items-center gap-1 text-primary">
                                            <span class="font-black text-xl">{{ number_format($total_price) }}</span>
                                            <x-riyal-icon class="w-5 h-5" />
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($ad->type == 'direct_purchase' && (!Auth::check() || !Auth::user()->isSameTeam($ad->user_id)))
                            <!-- Quantity Selector -->
                            <div class="mb-6 space-y-3">
                                <div class="flex justify-between items-center px-1">
                                    <span class="text-gray-900 font-black">الكمية</span>
                                </div>
                                <div class="flex items-center gap-4 bg-gray-50/80 rounded-2xl p-4 border border-gray-100">
                                    <div class="flex items-center gap-3 bg-white rounded-xl border border-gray-100 p-1">
                                        <button onclick="updateQty(1)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-primary text-primary hover:bg-primary hover:text-white transition-all"><i class="fa-solid fa-plus"></i></button>
                                        <span id="qtyDisplay" class="font-black text-lg min-w-[20px] text-center">1</span>
                                        <button onclick="updateQty(-1)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 transition-all"><i class="fa-solid fa-minus"></i></button>
                                    </div>
                                    <div class="flex-1 flex items-center gap-1 justify-end text-gray-900">
                                        <span id="totalPriceDisplay" class="font-black text-2xl" data-base="{{ $ad->price }}">{{ number_format($ad->price) }}</span>
                                        <x-riyal-icon class="w-6 h-6" />
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Action Buttons -->
                            @php
                                $is_closed = $ad->status == 'finished' || (isset($b_status) && ($b_status == 'sold' || $b_status == 'finished'));
                            @endphp

                            @if(!$is_closed && (!Auth::check() || !Auth::user()->isSameTeam($ad->user_id)))
                            <div class="space-y-3">
                                @if($ad->type == 'electronic_auction' || $ad->type == 'hybrid_auction')
                                    @if(!$has_joined && $b_status != 'upcoming')
                                        <button onclick="requireAuth(showBidModal)" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg mb-3">
                                            اشترك في المزاد الآن
                                        </button>
                                    @elseif($b_status == 'upcoming')
                                        <button onclick="requireAuth(showBidModal)" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg mb-3">
                                            اشترك في المزاد (قريباً)
                                        </button>
                                    @else
                                        <!-- Joined or Active: Show Unified Bidding Button -->
                                        <button onclick="requireAuth(showBidModal)" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg mb-3">
                                            زايد الآن
                                        </button>
                                    @endif

                                    <button onclick="requireAuth(showPurchaseModal)" class="w-full border-2 border-primary text-primary font-black py-4 rounded-xl hover:bg-primary/5 transition-all text-lg mb-3">
                                        اشترِ الآن
                                    </button>
                                    <button onclick="requireAuth(() => addToCart({{ $ad->id }}))" class="w-full border-2 border-primary text-primary font-black py-4 rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 text-lg">
                                         أضف الي السلة <i class="fa-solid fa-cart-shopping text-sm"></i>
                                    </button>
                                @elseif($ad->type == 'tender')
                                    <button onclick="requireAuth(showTenderModal)" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg">
                                        ناقص الآن
                                    </button>
                                @else
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <button onclick="requireAuth(showPurchaseModal)" class="bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-100 text-lg order-2">اشترِ الآن</button>
                                            <button onclick="requireAuth(showOfferModal)" class="border-2 border-primary text-primary font-black py-4 rounded-xl hover:bg-primary/5 transition-all text-lg order-1">قدم عرضك</button>
                                        </div>
                                        <button onclick="requireAuth(() => addToCart({{ $ad->id }}))" class="w-full border-2 border-primary text-primary font-black py-4 rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 text-lg">
                                             أضف الي السلة <i class="fa-solid fa-cart-shopping text-sm"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @elseif(Auth::id() == $ad->user_id)
                                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-center">
                                    <p class="text-primary font-black mb-4">أنت صاحب هذا الإعلان</p>
                                    <a href="{{ route('ads.edit', $ad->id) }}" class="w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-3">
                                        <i class="fa-solid fa-pen-to-square"></i> تعديل الإعلان
                                    </a>
                                </div>
                            @elseif(Auth::check() && Auth::user()->isSameTeam($ad->user_id))
                                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-center">
                                    <p class="text-primary font-black mb-4">هذا الإعلان تابع لشركتك أو أحد مناديبك.</p>
                                </div>
                            @endif
                        @endif
                    </div> <!-- End Card -->
                </div> <!-- End Sticky -->
            </div> <!-- End Left: Sidebar -->
        </div> <!-- End Main Flex -->
    @php /* Moved container close to after related ads */ @endphp

        <!-- 1. Price Breakdown Modal (Info only - Image 1) -->
        <div id="priceModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-md rounded-[40px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-2xl font-black text-gray-900">السعر الإجمالي</h3>
                    <button onclick="hidePriceModal()" class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-8">
                    <div class="flex justify-between text-gray-400 font-bold text-xs mb-6 px-2">
                        <span>الاسم</span>
                        <span>القيمة</span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <span class="text-gray-600 font-bold">السعر الحالي</span>
                            <div class="flex items-center gap-1">
                                <span class="font-black text-gray-900 text-lg">{{ number_format($ad->price) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col">
                                <span class="text-gray-600 font-bold">سعي ({{ $comm_rate }}%)</span>
                                <span class="text-[10px] text-gray-400 mt-0.5">شامل ضريبة القيمة المضافة {{ $vat_rate }}%</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="font-black text-gray-900 text-lg">{{ number_format($saai_val + $vat_val) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <span class="text-gray-600 font-bold">مصاريف إدارية</span>
                            <div class="flex items-center gap-1">
                                <span class="font-black text-gray-900 text-lg">{{ number_format($admin_fees) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <span class="text-gray-600 font-bold">نقل الملكية</span>
                            <div class="flex items-center gap-1">
                                <span class="font-black text-gray-900 text-lg">{{ number_format($transfer_fees) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-5 bg-blue-50/50 rounded-[24px] border border-blue-100/50 flex justify-between items-center">
                            <span class="text-gray-900 font-black text-lg">الإجمالي</span>
                            <div class="flex items-center gap-1 text-primary">
                                <span class="font-black text-3xl">{{ number_format($total_price) }}</span>
                                <x-riyal-icon class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-gray-50/30 flex justify-center">
                    <button onclick="hidePriceModal()" class="w-full bg-white border border-gray-200 text-gray-500 font-bold py-5 rounded-[20px] hover:bg-gray-50 transition-all text-lg">إغلاق</button>
                </div>
            </div>
        </div>

        <!-- 2. Purchase Modal (Submit order - Image 2) -->
        <div id="purchaseModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-md rounded-[40px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-2xl font-black text-gray-900">اشترِ الآن</h3>
                    <button onclick="hidePurchaseModal()" class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-8">
                    <div class="flex justify-between text-gray-400 font-bold text-xs mb-6 px-2">
                        <span>الاسم</span>
                        <span>القيمة</span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <span class="text-gray-600 font-bold">سعر الشراء</span>
                            <div class="flex items-center gap-1">
                                <span id="purchaseModalPrice" class="font-black text-gray-900 text-lg">{{ number_format($ad->price) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <span class="text-gray-600 font-bold">مصاريف إدارية</span>
                            <div class="flex items-center gap-1">
                                <span class="font-black text-gray-900 text-lg">{{ number_format($admin_fees) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                            <span class="text-gray-600 font-bold">نقل الملكية</span>
                            <div class="flex items-center gap-1">
                                <span class="font-black text-gray-900 text-lg">{{ number_format($transfer_fees) }}</span>
                                <div class="relative flex items-center justify-center">
                                    <x-riyal-icon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-5 bg-blue-50/50 rounded-[24px] border border-blue-100/50 flex justify-between items-center">
                            <span class="text-gray-900 font-black text-lg">الإجمالي</span>
                            <div class="flex items-center gap-1 text-primary">
                                <span id="purchaseModalTotal" class="font-black text-3xl">{{ number_format($total_purchase_price) }}</span>
                                <x-riyal-icon class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Step 1 Buttons --}}
                <div class="px-8 pb-8 flex gap-4" id="purchaseStep1Btns">
                    <button type="button" onclick="showPaymentStep()"
                            class="flex-1 bg-primary text-white font-black py-5 rounded-[20px] hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 text-lg">
                        متابعة للدفع
                    </button>
                    <button type="button" onclick="hidePurchaseModal()"
                            class="flex-1 bg-white border border-gray-200 text-gray-500 font-bold py-5 rounded-[20px] hover:bg-gray-50 transition-all text-lg">إلغاء</button>
                </div>

                {{-- Step 2: Payment Card Step --}}
                <div id="purchasePaymentStep" class="hidden px-8 pb-8 pt-6 border-t border-gray-100">
                    <h4 class="text-xl font-black text-gray-900 mb-5 text-right">تفاصيل الدفع</h4>

                    {{-- Card brand selector --}}
                    <div class="flex gap-4 mb-5">
                        <div id="pm-master" onclick="selectPurchasePayment('card')"
                             class="flex-1 border-2 border-primary bg-blue-50/10 rounded-2xl p-4 cursor-pointer flex justify-center items-center h-14 transition-all duration-300">
                            <img src="{{ asset('images/image 10.png') }}" class="h-7 object-contain" alt="Mastercard">
                        </div>
                        <div id="pm-mada" onclick="selectPurchasePayment('mada')"
                             class="flex-1 border-2 border-gray-100 rounded-2xl p-4 cursor-pointer flex justify-center items-center h-14 transition-all duration-300">
                            <img src="{{ asset('images/images 2.svg') }}" class="h-5 object-contain" alt="mada">
                        </div>
                    </div>

                    {{-- Card fields --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-1.5 text-right text-sm">اسم البطاقة</label>
                            <input type="text" id="pm_card_name" placeholder="أدخل اسم البطاقة"
                                   class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-1.5 text-right text-sm">رقم البطاقة</label>
                            <input type="text" id="pm_card_number" placeholder="أدخل رقم البطاقة" maxlength="19"
                                   class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 font-bold mb-1.5 text-right text-sm">CVV</label>
                                <input type="text" id="pm_card_cvv" placeholder="CVV" maxlength="4"
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-center">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-1.5 text-right text-sm">تاريخ الانتهاء</label>
                                <input type="text" id="pm_card_expiry" placeholder="شهر/سنة"
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-center">
                            </div>
                        </div>
                    </div>

                    {{-- Hidden form for submit --}}
                    <form action="{{ route('orders.store', $ad->id) }}" method="POST" id="directBuyForm" class="mt-5 flex flex-col gap-3">
                        @csrf
                        <input type="hidden" name="quantity" id="directBuyQtyInput" value="1">
                        <input type="hidden" name="payment_method" id="directBuyPaymentMethod" value="card">
                        <button type="button" onclick="submitDirectPurchase()"
                                class="w-full bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-base flex items-center justify-center gap-2">
                            <i class="fa-solid fa-lock text-sm"></i> إتمام الدفع
                        </button>
                        <button type="button" onclick="hidePaymentStep()"
                                class="w-full bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all text-sm">
                            العودة لملخص الطلب
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bids History Modal -->
        <div id="bidsModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-2xl rounded-[28px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300" dir="rtl">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-50">
                    <button onclick="hideBidsModal()" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <h3 class="text-xl font-black text-gray-900">
                        @if($ad->type == 'tender')
                            سجل المناقصات
                        @elseif($ad->type == 'direct_purchase')
                            سجل العروض
                        @else
                            سجل المزايدات
                        @endif
                    </h3>
                    <div class="px-3 py-1 bg-blue-50 text-primary rounded-full text-sm font-black border border-blue-100">
                        {{ $ad->bids()->where('type', '!=', 'deposit')->count() }} 
                        @if($ad->type == 'tender')
                            مناقصة
                        @elseif($ad->type == 'direct_purchase')
                            عرض
                        @else
                            مزايدة
                        @endif
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-y-auto max-h-[500px]">
                    @php 
                        $bids = $ad->bids()->where('type', '!=', 'deposit')->with('user')->orderByDesc('amount')->get(); 
                        $winner_bid_id = ($ad->type == 'tender') ? ($bids->last()->id ?? null) : ($bids->first()->id ?? null);
                    @endphp
                    @if($bids->count() > 0)
                    <table class="w-full text-sm text-right">
                        <thead class="bg-[#F8FAFC] text-[#6F6F6F] font-black text-[13px] sticky top-0">
                            <tr>
                                <th class="px-6 py-4 rounded-r-xl">الاسم</th>
                                <th class="px-6 py-4 text-center">المبلغ</th>
                                <th class="px-6 py-4 text-center rounded-l-xl">الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bids as $index => $bid)
                            @php $is_winner = ($bid->id === $winner_bid_id); @endphp
                            <tr class="border-b border-gray-50 hover:bg-blue-50/30 transition-colors {{ $is_winner ? 'bg-blue-50/50' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($is_winner)
                                            <div class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fa-solid fa-crown text-white text-[10px]"></i>
                                            </div>
                                        @else
                                            <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <span class="text-gray-500 font-black text-[10px]">{{ $index + 1 }}</span>
                                            </div>
                                        @endif
                                        <span class="font-black {{ $is_winner ? 'text-primary' : 'text-gray-700' }}">
                                            {{ $bid->user->id }}-{{ $bid->user->name ?? 'مجهول' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5 {{ $is_winner ? 'text-primary' : 'text-gray-700' }}">
                                        <span class="font-black">{{ number_format($bid->amount) }}</span>
                                        <x-riyal-icon class="w-4 h-4" />
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center {{ $is_winner ? 'text-primary/60' : 'text-gray-400' }} font-bold text-xs" dir="ltr">
                                    {{ $bid->created_at->format('Y/m/d') }} - {{ $bid->created_at->format('h:i:s A') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <i class="fa-solid fa-gavel text-4xl mb-3 opacity-30"></i>
                        <p class="font-bold">
                            لا توجد 
                            @if($ad->type == 'tender')
                                مناقصات
                            @elseif($ad->type == 'direct_purchase')
                                عروض
                            @else
                                مزايدات
                            @endif 
                            حتى الآن
                        </p>
                    </div>
                    @endif
                </div>

   
            </div>
        </div>



        <!-- Unified Bid Modal (اشترك في المزاد) -->
        <div id="bidModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-md rounded-[28px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300" dir="rtl">
                <div class="p-6 flex justify-start">
                    <button onclick="hideBidModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('bids.store', $ad->id) }}" method="POST" class="px-8 pb-8 text-center" id="bidForm">
                    @csrf
                    <input type="hidden" name="type" value="bid">
                    <h3 class="text-2xl font-black text-gray-900 mb-6">اشترك في المزاد</h3>

                    @if(!$has_joined)
                        <!-- Deposit Amount Box -->
                        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-center justify-center gap-2 text-primary">
                            <span class="text-gray-900 font-bold text-base">قيمة العربون:</span>
                            <span class="font-black text-2xl">{{ number_format($ad->deposit_amount > 0 ? $ad->deposit_amount : 1000) }}</span>
                            <x-riyal-icon class="w-6 h-6 text-primary" />
                        </div>
                    @endif

                    <!-- Bid Amount with +/- Buttons -->
                    <div class="bg-gray-50/80 rounded-3xl p-6 mb-6">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <button type="button" id="btnDecreaseBid" class="w-14 h-14 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-400 transition-all" disabled>
                                <i class="fa-solid fa-minus text-xl"></i>
                            </button>
                            
                            <div class="flex-1 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-900 mb-1">
                                    <span id="bidAmountDisplay" class="font-black text-4xl">{{ number_format($ad->price + ($ad->bid_increment ?? 500)) }}</span>
                                    <x-riyal-icon class="w-8 h-8 text-primary" />
                                </div>
                                <span class="text-xs text-gray-400 font-bold block">قيمة التزايد: {{ number_format($ad->bid_increment ?? 500) }} ر.س</span>
                            </div>

                            <button type="button" id="btnIncreaseBid" class="w-14 h-14 flex items-center justify-center rounded-xl bg-white border border-primary text-primary hover:bg-primary hover:text-white transition-all">
                                <i class="fa-solid fa-plus text-xl"></i>
                            </button>
                        </div>
                        <input type="hidden" name="amount" id="bidAmountInput" value="{{ $ad->price + ($ad->bid_increment ?? 500) }}">
                    </div>

                    <!-- Ceiling / Max Limit Box -->
                    <div class="text-right mb-6">
                        <label class="block text-gray-700 font-bold mb-2 pr-2 text-sm">الحد الأعلى للمزايدة</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r border-gray-100 px-4 my-2">
                                <x-riyal-icon class="w-5 h-5 text-gray-900" />
                            </div>
                            <input type="number" name="max_limit" id="bidMaxLimitInput" value="{{ $my_active_bid ? $my_active_bid->max_limit : '' }}" placeholder="أدخل الحد الأعلى للمزايدة" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 pr-4 pl-16 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all text-base">
                        </div>
                        @if($my_active_bid && $my_active_bid->max_limit > $my_active_bid->amount)
                            <span class="text-xs text-green-600 font-bold block mt-2 pr-2">الحد الأعلى النشط حالياً: {{ number_format($my_active_bid->max_limit) }} ر.س</span>
                        @endif
                        <p class="text-[12px] text-gray-400 font-medium leading-relaxed mt-2 px-1">
                            سيقوم النظام بالمزايدة بالنيابة عنك حتى تصل السلعة إلى القيمة المدخلة. لا يمكن لخاصية المزايدة التلقائية ضمان فوزك في المزاد، خاصة إذا قام مستخدم آخر بمزايدة أعلى.
                        </p>
                    </div>

                    <!-- Notification Toggle Switch -->
                    <div class="flex items-center justify-between mb-8 px-2">
                        <span class="text-sm font-bold text-gray-700">استلم إشعار عند اقتراب المزايدة من الحد الأعلى</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_near_limit" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg">
                            {{ !$has_joined ? 'اشترك الآن' : 'تأكيد المزايدة' }}
                        </button>
                        <button type="button" onclick="hideBidModal()" class="flex-1 bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all text-lg">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Offer Modal (قدم عرضك) -->
        <div id="offerModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-md rounded-[28px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300" dir="rtl">
                <div class="p-6 flex justify-start">
                    <button onclick="hideOfferModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('bids.store', $ad->id) }}" method="POST" class="px-8 pb-8 text-center">
                    @csrf
                    <input type="hidden" name="type" value="offer">
                    <h3 class="text-3xl font-black text-gray-900 mb-2">قدم عرضك</h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed mb-6">
                        هل أنت متأكد من تقديم هذا العرض؟ سيخصم مبلغ العربون لإتمام العملية.
                    </p>

                    <!-- Deposit Box -->
                    <div class="bg-gray-50 rounded-2xl p-4 mb-6 flex items-center justify-center gap-2 text-gray-900">
                        <span class="font-bold">قيمة العربون:</span>
                        <span class="font-black text-xl text-primary">{{ number_format($ad->deposit_amount > 0 ? $ad->deposit_amount : 1000) }}</span>
                        <x-riyal-icon class="w-5 h-5 text-primary" />
                    </div>

                    <!-- Offer Amount -->
                    <div class="text-right mb-6">
                        <label class="block text-gray-700 font-bold mb-2 pr-2">قيمة العرض</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r border-gray-100 px-4 my-2">
                                <x-riyal-icon class="w-5 h-5 text-gray-900" />
                            </div>
                            <input type="number" name="amount" required placeholder="أدخل قيمة العرض" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 pr-4 pl-16 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all">
                        </div>
                    </div>

                    <!-- Offer Validity -->
                    <div class="text-right mb-8">
                        <label class="block text-gray-700 font-bold mb-2 pr-2">مدة صلاحية العرض</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r border-gray-100 px-4 my-2">
                                <span class="text-gray-400 font-bold">أيام</span>
                            </div>
                            <input type="number" name="validity_days" required placeholder="أدخل مدة صلاحية العرض" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 pr-4 pl-16 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">تأكيد</button>
                        <button type="button" onclick="hideOfferModal()" class="flex-1 bg-gray-200 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-300 transition-all">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Unified Tender Modal (ناقص الآن) -->
        <div id="tenderModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-md rounded-[28px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300" dir="rtl">
                <div class="p-6 flex justify-start">
                    <button onclick="hideTenderModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('bids.store', $ad->id) }}" method="POST" class="px-8 pb-8 text-center" id="tenderForm">
                    @csrf
                    <input type="hidden" name="type" value="tender">
                    <h3 class="text-2xl font-black text-gray-900 mb-6">اشتراك في المناقصة</h3>
                    
                    <!-- Deposit Amount Box -->
                    <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-center justify-center gap-2 text-primary">
                        <span class="text-gray-900 font-bold text-base">قيمة العربون:</span>
                        <span class="font-black text-2xl text-primary">{{ number_format($ad->deposit_amount > 0 ? $ad->deposit_amount : 1000) }}</span>
                        <x-riyal-icon class="w-6 h-6 text-primary" />
                    </div>

                    <!-- Bidding UI (Decrement/Increment) -->
                    <div class="bg-gray-50/80 rounded-3xl p-6 mb-6">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <button type="button" id="btnDecreaseTender" class="w-14 h-14 flex items-center justify-center rounded-2xl bg-white border border-primary text-primary hover:bg-primary hover:text-white transition-all">
                                <i class="fa-solid fa-minus text-xl"></i>
                            </button>
                            
                            <div class="flex-1 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-900 mb-1">
                                    <span id="tenderAmountDisplay" class="font-black text-4xl">0</span>
                                    <x-riyal-icon class="w-8 h-8 text-primary" />
                                </div>
                                <span class="text-xs text-gray-400 font-bold block">قيمة التناقص: {{ number_format($ad->bid_increment ?? 500) }} ر.س</span>
                            </div>

                            <button type="button" id="btnIncreaseTender" class="w-14 h-14 flex items-center justify-center rounded-2xl bg-white border border-gray-200 text-gray-400 transition-all" disabled>
                                <i class="fa-solid fa-plus text-xl"></i>
                            </button>
                        </div>
                        <input type="hidden" name="amount" id="tenderAmountInput">
                    </div>

                    <!-- Min Limit Input (Auto-Tender Floor) -->
                    <div class="text-right mb-6">
                        <label class="block text-gray-700 font-bold mb-2 pr-2 text-sm">الحد الأدنى للمناقصة</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r border-gray-100 px-4 my-2">
                                <x-riyal-icon class="w-5 h-5 text-gray-900" />
                            </div>
                            <input type="number" name="min_limit" id="tenderMinLimitInput" value="{{ $my_active_tender ? $my_active_tender->min_limit : '' }}" placeholder="أدخل الحد الأدنى للمناقصة" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 pr-4 pl-16 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all text-base">
                        </div>
                        @if($my_active_tender && $my_active_tender->min_limit < $my_active_tender->amount)
                            <span class="text-xs text-green-600 font-bold block mt-2 pr-2">الحد الأدنى النشط حالياً: {{ number_format($my_active_tender->min_limit) }} ر.س</span>
                        @endif
                        <p class="text-[12px] text-gray-400 font-medium leading-relaxed mt-2 px-1">
                            سيقوم النظام بتقديم عروض أقل بالنيابة عنك حتى تصل السلعة إلى الحد الأدنى المدخل. لا يمكن لخاصية المناقصة التلقائية العكسية ضمان فوزك بالمناقصة.
                        </p>
                    </div>

                    <!-- Notification Toggle Switch -->
                    <div class="flex items-center justify-between mb-8 px-2">
                        <span class="text-sm font-bold text-gray-700">استلم إشعار عند اقتراب المناقصة من الحد الأدنى</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_near_limit" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-lg">تأكيد المناقصة</button>
                        <button type="button" onclick="hideTenderModal()" class="flex-1 bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all text-lg">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Auth Required Modal -->
        <div id="authRequiredModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/20 backdrop-blur-xl transition-all">
            <div class="bg-white w-full max-w-md rounded-[28px] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] animate-in fade-in zoom-in duration-300" dir="rtl">
                <div class="p-6 flex justify-start">
                    <button onclick="hideAuthModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <div class="px-8 pb-10 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-user-lock text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-2">تسجيل الدخول مطلوب</h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed mb-8">
                        يرجى تسجيل الدخول لتتمكن من المزايدة أو تنفيذ هذه العملية.
                    </p>

                        <div class="flex flex-col gap-3">
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="w-full bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-center">تسجيل الدخول</a>
                            <a href="{{ route('register.type', ['redirect' => url()->current()]) }}" class="w-full bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all text-center">إنشاء حساب جديد</a>
                        </div>
                </div>
            </div>
        </div>

            @if($services && $services->count() > 0)
            <!-- Suggested Services Section -->
            <div class="mt-32 mb-20">
                <div class="flex items-center justify-between mb-12">
                    <div class="flex items-center gap-4">
                        <span class="w-2 h-10 bg-primary rounded-full"></span>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900">خدمات مقترحة لهذا المنتج</h2>
                    </div>
                    <a href="{{ route('ads.services', $ad->id) }}" class="text-primary font-black flex items-center gap-2 hover:gap-4 transition-all">
                        عرض الكل <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>

                <!-- Swiped Container -->
                <div class="relative group">
                    <div id="servicesSlider" class="flex gap-6 overflow-x-auto no-scrollbar scroll-smooth pb-8">
                        @foreach($services as $service)
                            <div class="min-w-[280px] md:min-w-[320px] lg:min-w-[350px] flex-shrink-0">
                                @include('partials.service-card', ['service' => $service])
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Navigation -->
                    <button onclick="document.getElementById('servicesSlider').scrollLeft += 400" class="absolute -right-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white shadow-xl rounded-full hidden lg:flex items-center justify-center text-gray-400 hover:text-primary hover:scale-110 transition-all z-10 border border-gray-100">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <button onclick="document.getElementById('servicesSlider').scrollLeft -= 400" class="absolute -left-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white shadow-xl rounded-full hidden lg:flex items-center justify-center text-gray-400 hover:text-primary hover:scale-110 transition-all z-10 border border-gray-100">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                </div>
            </div>
            @endif

            @if($relatedAds && $relatedAds->count() > 0)
            <!-- You May Also Like Section -->
            <div class="mt-32 mb-20">
                <div class="flex items-center justify-between mb-12">
                    <div class="flex items-center gap-4">
                        <span class="w-2 h-10 bg-primary rounded-full"></span>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900">قد يعجبك أيضًا</h2>
                    </div>
                    <a href="{{ route('search', ['category' => $ad->category_id]) }}" class="text-primary font-black flex items-center gap-2 hover:gap-4 transition-all">
                        عرض الكل <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>

                <!-- Swiped Container -->
                <div class="relative group">
                    <div id="relatedAdsSlider" class="flex gap-6 overflow-x-auto no-scrollbar scroll-smooth pb-8">
                        @foreach($relatedAds as $rad)
                            <div class="min-w-[280px] md:min-w-[320px] lg:min-w-[350px] flex-shrink-0">
                                @include('partials.ad-card', ['ad' => $rad])
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Navigation (Optional but nice) -->
                    <button onclick="document.getElementById('relatedAdsSlider').scrollLeft += 400" class="absolute -right-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white shadow-xl rounded-full hidden lg:flex items-center justify-center text-gray-400 hover:text-primary hover:scale-110 transition-all z-10 border border-gray-100">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <button onclick="document.getElementById('relatedAdsSlider').scrollLeft -= 400" class="absolute -left-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white shadow-xl rounded-full hidden lg:flex items-center justify-center text-gray-400 hover:text-primary hover:scale-110 transition-all z-10 border border-gray-100">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                </div>
            </div>
            @endif
        </div> <!-- End Main Content Container -->
    </div> <!-- End Page -->

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Map Marker Custom Styling */
    .leaflet-container { font-family: inherit; }
    .leaflet-popup-content-wrapper { border-radius: 12px; padding: 5px; }
    .leaflet-popup-content { font-weight: bold; }
    
    .tab-btn { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .tab-content { animation: slideUpFade 0.6s ease-out; }
    
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    let map;
    function initMap() {
        if (map) return;
        const lat = {{ $ad->lat ?? 24.7136 }};
        const lng = {{ $ad->lng ?? 46.6753 }};
        
        map = L.map('adMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>{{ $ad->title }}</b><br>{{ $ad->address }}')
            .openPopup();
    }

    function switchTab(tabId) {
        // Hide all
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        
        // Show target
        const target = document.getElementById('tab-' + tabId);
        target.classList.remove('hidden');

        if (tabId === 'location') {
            setTimeout(() => {
                initMap();
                if (map) map.invalidateSize();
            }, 100);
        }
        
        // Buttons Update
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('bg-primary', 'text-white', 'shadow-xl', 'shadow-blue-100', 'active');
            b.classList.add('bg-white', 'text-gray-500', 'border-gray-100');
        });
        
        const btn = document.getElementById('btn-' + tabId);
        btn.classList.add('bg-primary', 'text-white', 'shadow-xl', 'shadow-blue-100', 'active');
        btn.classList.remove('bg-white', 'text-gray-500', 'border-gray-100');
    }


    // Authentication Check
    function requireAuth(callback) {
        @auth
            if (typeof callback === 'function') {
                callback();
            } else {
                // If no callback, we might just want to check (e.g. for cart)
                window.location.href = '#'; // Or some default action
            }
        @else
            const modal = document.getElementById('authRequiredModal');
            if(modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        @endauth
    }

    function hideAuthModal() {
        const modal = document.getElementById('authRequiredModal');
        if(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    // Modal Logic
    function showPriceModal() {
        const modal = document.getElementById('priceModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function hidePriceModal() {
        const modal = document.getElementById('priceModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Purchase Modal Logic
    const adminFees = {{ $admin_fees ?? 19 }};
    const transferFees = {{ $transfer_fees ?? 500 }};
    function showPurchaseModal() {
        const modal = document.getElementById('purchaseModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Update hidden quantity input
        const qtyInput = document.getElementById('directBuyQtyInput');
        if (qtyInput) {
            qtyInput.value = typeof currentQty !== 'undefined' ? currentQty : 1;
        }

        // Update modal displays if quantity selector exists
        const purchaseModalPrice = document.getElementById('purchaseModalPrice');
        const purchaseModalTotal = document.getElementById('purchaseModalTotal');
        if (purchaseModalPrice && purchaseModalTotal) {
            const qty = typeof currentQty !== 'undefined' ? currentQty : 1;
            purchaseModalPrice.innerText = new Intl.NumberFormat().format(qty * basePrice);
            purchaseModalTotal.innerText = new Intl.NumberFormat().format(qty * basePrice + adminFees + transferFees);
        }
    }

    function hidePurchaseModal() {
        const modal = document.getElementById('purchaseModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        // Reset to step 1 on close
        hidePaymentStep();
    }

    // Purchase Modal - Payment Step Functions
    let selectedPurchasePayment = 'card';

    function showPaymentStep() {
        document.getElementById('purchaseStep1Btns').classList.add('hidden');
        document.getElementById('purchasePaymentStep').classList.remove('hidden');
    }

    function hidePaymentStep() {
        document.getElementById('purchasePaymentStep').classList.add('hidden');
        document.getElementById('purchaseStep1Btns').classList.remove('hidden');
        // Clear fields
        ['pm_card_name', 'pm_card_number', 'pm_card_cvv', 'pm_card_expiry'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        // Reset to card selected
        selectPurchasePayment('card');
    }

    function selectPurchasePayment(method) {
        selectedPurchasePayment = method;
        const master = document.getElementById('pm-master');
        const mada   = document.getElementById('pm-mada');
        if (method === 'card') {
            master.className = 'flex-1 border-2 border-primary bg-blue-50/10 rounded-2xl p-4 cursor-pointer flex justify-center items-center h-14 transition-all duration-300';
            mada.className   = 'flex-1 border-2 border-gray-100 rounded-2xl p-4 cursor-pointer flex justify-center items-center h-14 transition-all duration-300';
        } else {
            mada.className   = 'flex-1 border-2 border-primary bg-blue-50/10 rounded-2xl p-4 cursor-pointer flex justify-center items-center h-14 transition-all duration-300';
            master.className = 'flex-1 border-2 border-gray-100 rounded-2xl p-4 cursor-pointer flex justify-center items-center h-14 transition-all duration-300';
        }
        const pmInput = document.getElementById('directBuyPaymentMethod');
        if (pmInput) pmInput.value = method;
    }

    function submitDirectPurchase() {
        const name   = document.getElementById('pm_card_name').value.trim();
        const number = document.getElementById('pm_card_number').value.trim();
        const cvv    = document.getElementById('pm_card_cvv').value.trim();
        const expiry = document.getElementById('pm_card_expiry').value.trim();

        if (!name || !number || !cvv || !expiry) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى ملء جميع حقول البطاقة البنكية.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
            return;
        }
        if (number.replace(/\s/g, '').length < 15) {
            Swal.fire({ icon: 'warning', title: 'رقم البطاقة غير صحيح', text: 'يرجى إدخال رقم بطاقة بنكية صالح.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
            return;
        }
        if (cvv.length < 3) {
            Swal.fire({ icon: 'warning', title: 'رمز CVV غير صحيح', text: 'يرجى إدخال رمز CVV المكون من 3 أو 4 أرقام.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
            return;
        }

        Swal.fire({ title: 'جاري معالجة الدفع...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        document.getElementById('directBuyForm').submit();
    }

    // Close on click outside
    window.onclick = function(event) {
        const modals = ['priceModal', 'authRequiredModal', 'bidsModal', 'purchaseModal', 'tenderModal', 'bidModal', 'offerModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                // Call the appropriate hide function
                if (id === 'priceModal') hidePriceModal();
                if (id === 'authRequiredModal') hideAuthModal();
                if (id === 'bidsModal') hideBidsModal();
                if (id === 'purchaseModal') hidePurchaseModal();
                if (id === 'tenderModal') hideTenderModal();
                if (id === 'bidModal') hideBidModal();
                if (id === 'offerModal') hideOfferModal();
            }
        });
    }

    function showAutoTenderModal() {
        showTenderModal();
    }

    function hideAutoTenderModal() {
        hideTenderModal();
    }

    function toggleWishlistUI(action) {
        const text = document.getElementById('wishlistText');
        if (text) {
            text.innerText = (action === 'added') ? 'في المفضلة' : 'أضف للمفضلة';
        }
    }

    // Bids Modal
    function showBidsModal() {
        const modal = document.getElementById('bidsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function hideBidsModal() {
        const modal = document.getElementById('bidsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }



    // Join Auction Modal Logic
    function showJoinAuctionModal() {
        showBidModal();
    }

    @if(session('success'))
        setTimeout(() => Swal.fire({
            icon: 'success',
            title: 'تمت العملية بنجاح',
            text: "{{ session('success') }}",
            confirmButtonColor: '#1A73E8',
            confirmButtonText: 'موافق',
            timer: 4000,
            timerProgressBar: true,
        }), 300);
    @endif
    @if(session('error'))
        setTimeout(() => {
            const toast = document.createElement('div');
            toast.className = 'fixed top-10 left-1/2 -translate-x-1/2 z-[10000] flex items-center gap-3 bg-[#EF4444] text-white px-6 py-3 rounded-full shadow-2xl animate-in slide-in-from-top duration-500 font-bold whitespace-nowrap';
            toast.innerHTML = `
                <i class="fa-solid fa-circle-exclamation text-white text-lg"></i>
                <span>{{ session('error') }}</span>
            `;
            document.body.appendChild(toast);
            
            // Re-open the modal if it was an offer
            @if(old('type') == 'offer')
                showOfferModal();
            @endif

            setTimeout(() => {
                toast.classList.add('animate-out', 'fade-out', 'slide-out-to-top');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }, 300);
    @endif

    function hideJoinAuctionModal() {
        hideBidModal();
    }

    function showOfferModal() {
        const modal = document.getElementById('offerModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function hideOfferModal() {
        const modal = document.getElementById('offerModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Tender Modal Logic
    let currentTenderAmount = {{ $ad->price - ($ad->bid_increment ?? 500) }};
    const tenderStep = {{ $ad->bid_increment ?? 500 }};
    
    function showTenderModal() {
        const modal = document.getElementById('tenderModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        currentTenderAmount = {{ $ad->price - ($ad->bid_increment ?? 500) }};
        updateTenderDisplay();
    }

    function hideTenderModal() {
        const modal = document.getElementById('tenderModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function updateTenderDisplay() {
        const display = document.getElementById('tenderAmountDisplay');
        const input = document.getElementById('tenderAmountInput');
        if (display) display.innerText = new Intl.NumberFormat().format(currentTenderAmount);
        if (input) input.value = currentTenderAmount;
    }

    const btnDecreaseTender = document.getElementById('btnDecreaseTender');
    const btnIncreaseTender = document.getElementById('btnIncreaseTender');

    if (btnDecreaseTender && btnIncreaseTender) {
        btnDecreaseTender.addEventListener('click', () => {
            currentTenderAmount -= tenderStep;
            updateTenderDisplay();
        });
        btnIncreaseTender.addEventListener('click', () => {
            if (currentTenderAmount + tenderStep < {{ $ad->price }}) {
                currentTenderAmount += tenderStep;
                updateTenderDisplay();
            }
        });
    }

    // Bid Counter Logic
    let currentBid = {{ $ad->price + 500 }};
    const bidStep = 500;
    const minBid = {{ $ad->price + 500 }};
    
    const btnDecreaseBid = document.getElementById('btnDecreaseBid');
    const btnIncreaseBid = document.getElementById('btnIncreaseBid');
    const bidAmountDisplay = document.getElementById('bidAmountDisplay');
    
    if(btnDecreaseBid && btnIncreaseBid && bidAmountDisplay) {
        btnIncreaseBid.addEventListener('click', () => {
            currentBid += bidStep;
            updateBidDisplay();
        });

        btnDecreaseBid.addEventListener('click', () => {
            if (currentBid > minBid) {
                currentBid -= bidStep;
                updateBidDisplay();
            }
        });

        function updateBidDisplay() {
            bidAmountDisplay.innerText = new Intl.NumberFormat().format(currentBid);
            const bidAmountInput = document.getElementById('bidAmountInput');
            if (bidAmountInput) {
                bidAmountInput.value = currentBid;
            }
            
            // Enable/Disable minus button
            if (currentBid <= minBid) {
                btnDecreaseBid.disabled = true;
                btnDecreaseBid.classList.remove('border-primary', 'text-primary');
                btnDecreaseBid.classList.add('border-gray-200', 'text-gray-400');
            } else {
                btnDecreaseBid.disabled = false;
                btnDecreaseBid.classList.add('border-primary', 'text-primary');
                btnDecreaseBid.classList.remove('border-gray-200', 'text-gray-400');
            }
        }
    }


    // Quantity Logic (Global Scope)
    let currentQty = 1;
    const basePrice = {{ $ad->price ?? 0 }};
    function updateQty(val) {
        currentQty = Math.max(1, currentQty + val);
        const qtyDisplay = document.getElementById('qtyDisplay');
        const priceDisplay = document.getElementById('totalPriceDisplay');
        if(qtyDisplay) qtyDisplay.innerText = currentQty;
        if(priceDisplay) priceDisplay.innerText = new Intl.NumberFormat().format(currentQty * basePrice);
    }
    // Bid Modal Logic
    function showBidModal() {
        const modal = document.getElementById('bidModal');
        if(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            
            // Reset to minimum bid
            currentBid = {{ $ad->price + ($ad->bid_increment ?? 500) }};
            updateBidDisplay();
        }
    }

    function hideBidModal() {
        const modal = document.getElementById('bidModal');
        if(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    // Auto Bid Modal Logic
    function showAutoBidModal() {
        const modal = document.getElementById('autoBidModal');
        if(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function hideAutoBidModal() {
        const modal = document.getElementById('autoBidModal');
        if(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    function scrollRelated(direction) {
        const container = document.getElementById('relatedAdsContainer');
        if(!container) return;
        const scrollAmount = container.offsetWidth * 0.8;
        if(direction === 'right') {
            container.scrollLeft += scrollAmount;
        } else {
            container.scrollLeft -= scrollAmount;
        }
    }


    // Share Functionality
    function shareAd() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $ad->title }}',
                text: 'شاهد هذا الإعلان على منصة مزاد',
                url: window.location.href
            }).then(() => {
                console.log('Successful share');
            }).catch((error) => {
                console.log('Error sharing', error);
            });
        } else {
            const el = document.createElement('textarea');
            el.value = window.location.href;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            
            Swal.fire({
                icon: 'success',
                title: 'تم نسخ الرابط',
                text: 'تم نسخ رابط الإعلان إلى الحافظة بنجاح',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }

    // Toggle UI for Wishlist (called by global logic)
    function toggleWishlistUI(action) {
        const adId = {{ $ad->id }};
        const wishlistText = document.getElementById('wishlistText');
        if (wishlistText) {
            wishlistText.innerText = action === 'added' ? 'في المفضلة' : 'أضف للمفضلة';
        }
    }

    // Add to Cart Logic
    function addToCart(adId) {
        const qty = typeof currentQty !== 'undefined' ? currentQty : 1;
        
        fetch("{{ route('cart.add') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ad_id: adId,
                quantity: qty
            })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'تمت الإضافة',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                
                // Update cart count if exists
                const cartCount = document.getElementById('cartCount');
                if (cartCount) {
                    cartCount.innerText = data.cart_count;
                    cartCount.style.display = 'flex';
                    cartCount.classList.remove('hidden');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'عذراً',
                    text: data.message || (data.errors ? Object.values(data.errors)[0][0] : 'حدث خطأ غير متوقع')
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'فشل الاتصال بالخادم، يرجى المحاولة لاحقاً'
            });
        });
    }

    window.showReplyForm = function(reviewId) {
        document.getElementById('reply-form-' + reviewId).classList.remove('hidden');
    };

    window.hideReplyForm = function(reviewId) {
        document.getElementById('reply-form-' + reviewId).classList.add('hidden');
    };

    window.toggleReviewVisibility = function(reviewId, btn) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'هل ترغب في تغيير حالة ظهور هذا التقييم؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، قم بالتغيير',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#d33',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/reviews/${reviewId}/toggle-visibility`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم بنجاح',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[32px]' }
                        });
                        
                        // Update button state and classes dynamically
                        const span = btn.querySelector('span');
                        const icon = btn.querySelector('i');
                        const statusBadge = document.getElementById('review-status-badge-' + reviewId);
                        
                        if (data.is_visible) {
                            btn.className = "text-xs font-black px-4 py-2.5 rounded-xl border border-amber-200 text-amber-600 hover:bg-amber-50 transition-all flex items-center gap-1.5";
                            span.innerText = "إخفاء التقييم";
                            icon.className = "fa-solid fa-eye-slash";
                            if (statusBadge) {
                                statusBadge.className = "px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold ring-1 ring-emerald-100 mr-2";
                                statusBadge.innerText = "منشور";
                            }
                        } else {
                            btn.className = "text-xs font-black px-4 py-2.5 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-all flex items-center gap-1.5";
                            span.innerText = "نشر التقييم";
                            icon.className = "fa-solid fa-eye";
                            if (statusBadge) {
                                statusBadge.className = "px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-bold ring-1 ring-amber-100 mr-2";
                                statusBadge.innerText = "مخفي";
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: data.message || 'حدث خطأ ما',
                            customClass: { popup: 'rounded-[32px]' }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'فشل الاتصال بالخادم، يرجى المحاولة لاحقاً',
                        customClass: { popup: 'rounded-[32px]' }
                    });
                });
            }
        });
    };

    window.showQuestionReplyForm = function(qId) {
        document.getElementById('question-reply-form-' + qId).classList.remove('hidden');
    };

    window.hideQuestionReplyForm = function(qId) {
        document.getElementById('question-reply-form-' + qId).classList.add('hidden');
    };

    window.toggleQuestionVisibility = function(qId, btn) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'هل ترغب في تغيير حالة ظهور هذا السؤال؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، قم بالتغيير',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#d33',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/questions/${qId}/toggle-visibility`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم بنجاح',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[32px]' }
                        });
                        
                        // Update button state and classes dynamically
                        const span = btn.querySelector('span');
                        const icon = btn.querySelector('i');
                        const statusBadge = document.getElementById('question-status-badge-' + qId);
                        
                        if (data.is_visible) {
                            btn.className = "text-xs font-black px-4 py-2 rounded-xl border border-amber-200 text-amber-600 hover:bg-amber-50 transition-all flex items-center gap-1.5";
                            span.innerText = "إخفاء السؤال";
                            icon.className = "fa-solid fa-eye-slash";
                            if (statusBadge) {
                                statusBadge.className = "px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold ring-1 ring-emerald-100 mr-2";
                                statusBadge.innerText = "منشور";
                            }
                        } else {
                            btn.className = "text-xs font-black px-4 py-2 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-all flex items-center gap-1.5";
                            span.innerText = "نشر السؤال";
                            icon.className = "fa-solid fa-eye";
                            if (statusBadge) {
                                statusBadge.className = "px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-bold ring-1 ring-amber-100 mr-2";
                                statusBadge.innerText = "مخفي";
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: data.message || 'حدث خطأ ما',
                            customClass: { popup: 'rounded-[32px]' }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'فشل الاتصال بالخادم، يرجى المحاولة لاحقاً',
                        customClass: { popup: 'rounded-[32px]' }
                    });
                });
            }
        });
    };

    // Buyer interactive card action functions
    window.copyAdDeliveryCode = function(code) {
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

    window.openCardPaymentModal = function(orderNumber, price, orderId) {
        Swal.fire({
            title: '<span style="font-size: 1.35rem; font-weight: 900; color: #1E293B;">تفاصيل الدفع</span>',
            html: `
                <div style="direction: rtl; text-align: right;">
                    <!-- Order summary -->
                    <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:16px; padding:14px; margin-bottom:20px;">
                        <p style="font-size:11px; color:#6B7280; font-weight:700; margin:0 0 2px;">رقم الطلب:</p>
                        <p style="font-size:13px; font-weight:900; color:#1A73E8; margin:0 0 8px;">#${orderNumber}</p>
                        <p style="font-size:11px; color:#6B7280; font-weight:700; margin:0 0 2px;">الإجمالي المطلوب سداده:</p>
                        <p style="font-size:18px; font-weight:900; color:#111827; margin:0;">${price} ريال سعودي</p>
                    </div>

                    <!-- Card brand selector -->
                    <div style="display:flex; gap:12px; margin-bottom:18px;">
                        <div id="swal-pm-card" onclick="swalSelectPayment('card')"
                             style="flex:1; border:2px solid #1A73E8; background:#EFF6FF; border-radius:14px; padding:10px; cursor:pointer; display:flex; justify-content:center; align-items:center; height:52px; transition:all .3s;">
                            <img src="/images/image 10.png" style="height:28px; object-fit:contain;" alt="Mastercard">
                        </div>
                        <div id="swal-pm-mada" onclick="swalSelectPayment('mada')"
                             style="flex:1; border:2px solid #E5E7EB; border-radius:14px; padding:10px; cursor:pointer; display:flex; justify-content:center; align-items:center; height:52px; transition:all .3s;">
                            <img src="/images/images 2.svg" style="height:22px; object-fit:contain;" alt="mada">
                        </div>
                    </div>

                    <!-- Card fields -->
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">اسم البطاقة</label>
                            <input type="text" id="swal_card_name" placeholder="أدخل اسم البطاقة"
                                   style="width:100%; background:#F9FAFB; border:1px solid #F3F4F6; border-radius:14px; padding:12px 14px; font-weight:700; font-size:13px; outline:none; box-sizing:border-box; text-align:right;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">رقم البطاقة</label>
                            <input type="text" id="swal_card_number" placeholder="أدخل رقم البطاقة" maxlength="19"
                                   style="width:100%; background:#F9FAFB; border:1px solid #F3F4F6; border-radius:14px; padding:12px 14px; font-weight:700; font-size:13px; outline:none; box-sizing:border-box; text-align:right;">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">CVV</label>
                                <input type="text" id="swal_card_cvv" placeholder="CVV" maxlength="4"
                                       style="width:100%; background:#F9FAFB; border:1px solid #F3F4F6; border-radius:14px; padding:12px 14px; font-weight:700; font-size:13px; outline:none; box-sizing:border-box; text-align:center;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">تاريخ الانتهاء</label>
                                <input type="text" id="swal_card_expiry" placeholder="شهر/سنة"
                                       style="width:100%; background:#F9FAFB; border:1px solid #F3F4F6; border-radius:14px; padding:12px 14px; font-weight:700; font-size:13px; outline:none; box-sizing:border-box; text-align:center;">
                            </div>
                        </div>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: '<i class="fa-solid fa-lock" style="margin-left:6px; font-size:12px;"></i> إتمام الدفع',
            confirmButtonColor: '#1A73E8',
            showCancelButton: true,
            cancelButtonText: 'إلغاء',
            cancelButtonColor: '#E5E7EB',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full text-right',
                confirmButton: 'rounded-2xl px-8 py-3.5 font-bold text-sm bg-primary border-0',
                cancelButton: 'rounded-2xl px-8 py-3.5 font-bold text-sm text-slate-500 border-0 bg-gray-100'
            },
            didOpen: () => {
                window._swalPaymentMethod = 'card';
            },
            preConfirm: () => {
                const name   = document.getElementById('swal_card_name').value.trim();
                const number = document.getElementById('swal_card_number').value.trim();
                const cvv    = document.getElementById('swal_card_cvv').value.trim();
                const expiry = document.getElementById('swal_card_expiry').value.trim();
                if (!name || !number || !cvv || !expiry) {
                    Swal.showValidationMessage('يرجى ملء جميع حقول البطاقة البنكية.');
                    return false;
                }
                if (number.replace(/\s/g, '').length < 15) {
                    Swal.showValidationMessage('يرجى إدخال رقم بطاقة بنكية صالح.');
                    return false;
                }
                if (cvv.length < 3) {
                    Swal.showValidationMessage('يرجى إدخال رمز CVV المكون من 3 أو 4 أرقام.');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري معالجة الدفع...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(`/orders/${orderId}/submit-payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ payment_method: window._swalPaymentMethod ?? 'card' })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم إرسال تأكيد الدفع!',
                            text: 'سيقوم الفريق بمراجعة طلبك وسيتم التواصل معك قريباً.',
                            confirmButtonText: 'حسناً',
                            confirmButtonColor: '#1A73E8',
                            customClass: { popup: 'rounded-[32px]', confirmButton: 'rounded-2xl px-8 py-3.5 font-bold text-sm bg-primary border-0' }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: data.message ?? 'حدث خطأ، يرجى المحاولة لاحقاً.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
                    }
                }).catch(() => {
                    Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: 'فشل الاتصال بالخادم، يرجى المحاولة لاحقاً.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
                });
            }
        });
    };

    window.swalSelectPayment = function(method) {
        window._swalPaymentMethod = method;
        const card = document.getElementById('swal-pm-card');
        const mada = document.getElementById('swal-pm-mada');
        if (!card || !mada) return;
        if (method === 'card') {
            card.style.border = '2px solid #1A73E8'; card.style.background = '#EFF6FF';
            mada.style.border = '2px solid #E5E7EB'; mada.style.background = '';
        } else {
            mada.style.border = '2px solid #1A73E8'; mada.style.background = '#EFF6FF';
            card.style.border = '2px solid #E5E7EB'; card.style.background = '';
        }
    };

    window.openAdDeliveryModal = function(code) {
        const digits = String(code).split('');
        while (digits.length < 6) {
            digits.push('');
        }
        
        Swal.fire({
            title: `<span style="font-size: 1.35rem; font-weight: 900; color: #1E293B;">إرسال كود التسليم</span>`,
            html: `
                <div class="text-center" style="direction: rtl;">
                    <p class="text-xs font-bold text-slate-500 leading-relaxed mb-6">
                        سيتم إرسال كود التسليم إلى البائع لتوثيق عملية الاستلام. قدم الكود للبائع عند تسليم السلعة.
                    </p>
                    <div class="flex justify-center gap-2 my-6" style="direction: ltr;">
                        ${digits.map(d => `
                            <div class="w-12 h-14 bg-slate-50 border-2 border-slate-200 rounded-xl flex items-center justify-center font-black text-xl text-slate-800" style="box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">${d}</div>
                        `).join('')}
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'إرسال',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#E2E8F0',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full text-right',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold text-sm bg-primary border-0',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold text-sm text-slate-500 border-0 bg-slate-200'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                navigator.clipboard.writeText(code).then(() => {
                    Swal.fire({
                        title: 'تم بنجاح!',
                        text: 'تم نسخ كود التسليم وإرساله بنجاح إلى البائع لتأكيد الاستلام.',
                        icon: 'success',
                        timer: 2500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-[32px]' }
                    });
                });
            }
        });
    };

    window.openAdRatingModal = function(sellerId, sellerName) {
        let currentRating = 5;
        
        Swal.fire({
            title: `<span style="font-size: 1.35rem; font-weight: 900; color: #1E293B;">قيّم تجربتك</span>`,
            html: `
                <style>
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
                <div class="text-center" style="direction: rtl;">
                    <p class="text-xs font-bold text-slate-400 mb-4">شارك رأيك وساعد الآخرين في اختيار الأفضل</p>
                    <div class="swal2-stars" id="star-rating-container">
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="1"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="2"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="3"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="4"></i>
                        <i class="fa-solid fa-star swal2-star-item selected" data-index="5"></i>
                    </div>
                    <div class="text-right mt-4 mb-2 pr-1">
                        <label class="text-xs font-black text-slate-800">اكتب رأيك</label>
                    </div>
                    <textarea id="swal-rating-comment" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 focus:outline-none focus:border-blue-500 text-sm font-bold" rows="3" placeholder="مشاركة رأيك يساعدنا على التجربة"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'إرسال',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#E2E8F0',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full text-right',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold text-sm bg-primary border-0',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold text-sm text-slate-500 border-0 bg-slate-200'
            },
            didOpen: () => {
                const starItems = document.querySelectorAll('.swal2-star-item');
                starItems.forEach(star => {
                    star.addEventListener('click', (e) => {
                        const index = parseInt(star.getAttribute('data-index'));
                        currentRating = index;
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
                const form = document.getElementById('purchasesReviewForm');
                if(form) {
                    document.getElementById('review_seller_id').value = sellerId;
                    document.getElementById('review_rating_val').value = result.value.rating;
                    document.getElementById('review_comment_val').value = result.value.comment;
                    
                    Swal.fire({
                        title: 'جاري تسجيل التقييم...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            }
        });
    };
</script>

<!-- Review submission form (executed via JS dynamically) -->
<form id="purchasesReviewForm" action="{{ route('reviews.store') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="rated_user_id" id="review_seller_id">
    <input type="hidden" name="rating" id="review_rating_val">
    <input type="hidden" name="comment" id="review_comment_val">
</form>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
