@extends('layouts.main')

@section('title', $service->title)

@section('content')
@php
    $additionalImages = ($service instanceof \App\Models\Ad) ? $service->images : collect();
    $allImages = collect([$service->image])
        ->merge($additionalImages->pluck('image'))
        ->filter()
        ->unique()
        ->map(fn($img) => Str::startsWith($img, 'http') ? $img : '/' . ltrim($img, '/'))
        ->values();

    $city = $service->city ?? null;
    $address = ($service instanceof \App\Models\Ad) ? $service->address : null;
    $lat = ($service instanceof \App\Models\Ad) ? $service->lat : null;
    $lng = ($service instanceof \App\Models\Ad) ? $service->lng : null;

    $properties = ($service instanceof \App\Models\Ad) ? $service->adProperties : collect();
    $questions = ($service instanceof \App\Models\Ad) ? $service->questions()->where('is_visible', true)->latest()->get() : collect();
    $attachments = ($service instanceof \App\Models\Ad) ? $service->attachments : collect();
    $seller = $service->user;
@endphp

<div class="service-details-page min-h-screen bg-[#F8FAFC] pb-20" dir="rtl">
    <!-- Breadcrumbs Section -->
    <div class="container mx-auto px-4 md:px-16 pt-12 pb-6">
        <div class="flex flex-col gap-6">
            <!-- Breadcrumbs Pill & Actions Row -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <nav class="bg-primary px-6 py-3 w-auto md:w-fit rounded-full flex items-center gap-3 text-white text-sm font-bold shadow-lg shadow-primary/20">
                    <a href="{{ route('home') }}" class="hover:text-blue-100 transition-colors">الرئيسية</a>
                    <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                    <a href="{{ route('search', ['category' => $service->category_id]) }}" class="hover:text-blue-100 transition-colors">{{ $service->category->name ?? 'خدمات' }}</a>
                    <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                    <span class="text-blue-100">{{ $service->title }}</span>
                </nav>

                <div class="flex items-center gap-3">
                    @if(Auth::check() && $service->user_id === Auth::id())
                        <!-- Edit Button (Blue) -->
                        <a href="{{ route('ads.edit', $service->id) }}" class="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-2xl shadow-lg shadow-primary/20 font-bold hover:bg-blue-700 transition-all text-sm">
                            <i class="fa-solid fa-pen-to-square"></i>
                            تعديل الخدمة
                        </a>
                        <!-- Delete Button (Grey) -->
                        <form id="deleteServiceForm" action="{{ route('ads.destroy', $service->id) }}" method="POST" class="m-0 inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="openDeleteModal()" class="flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-500 rounded-2xl border border-slate-200 font-bold hover:bg-slate-200 hover:text-slate-700 transition-all text-sm shadow-sm">
                                <i class="fa-solid fa-trash-can"></i>
                                حذف
                            </button>
                        </form>
                    @else
                        <!-- Request Service Button -->
                        <form action="{{ route('chats.start') }}" method="POST" class="m-0 inline">
                            @csrf
                            @if($service instanceof \App\Models\Ad)
                                <input type="hidden" name="ad_id" value="{{ $service->id }}">
                            @else
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                            @endif
                            <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-2xl shadow-lg shadow-primary/20 font-bold hover:bg-blue-700 transition-all text-sm">
                                <i class="fa-solid fa-comment-dots"></i>
                                اطلب الخدمة
                            </button>
                        </form>

                        <button onclick="shareService()" class="flex items-center gap-2 px-5 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 text-gray-500 font-bold hover:bg-gray-50 transition-all">
                            <i class="fa-solid fa-share-nodes text-primary"></i>
                            مشاركة
                        </button>
                        <button onclick="openReportModal()" class="flex items-center gap-2 px-5 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 text-red-500 font-bold hover:bg-red-50 transition-all">
                            <i class="fa-solid fa-flag"></i>
                            الإبلاغ عن هذا المعلن
                        </button>
                    @endif
                </div>
            </div>

            <!-- Page Title -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">{{ $service->title }}</h1>
                    <div class="flex gap-2">
                        @if($service instanceof \App\Models\Ad && $service->is_featured)
                        <span class="bg-[#EF4444] text-white px-4 py-1 rounded-full text-xs font-bold flex items-center gap-1 shadow-lg shadow-red-200">
                            <i class="fa-solid fa-fire"></i> مميز
                        </span>
                        @endif
                    </div>
                </div>
                <!-- Price badge -->
                <div class="flex items-center gap-2 text-primary bg-primary/5 px-6 py-3 rounded-2xl border border-primary/10 w-fit">
                    <span class="text-3xl md:text-4xl font-black">{{ number_format($service->price) }}</span>
                    <x-riyal-icon class="w-8 h-8" />
                </div>
            </div>
        </div>
    </div>

    <!-- Main Gallery & Details Grid -->
    <div class="container mx-auto px-4 md:px-16">
        
        <!-- Gallery Section -->
        <div class="grid grid-cols-12 gap-4 h-[400px] md:h-[500px] mb-10">
            <!-- Main Large Image -->
            <a href="{{ route('services.gallery', ['id' => $service->id, 'index' => 0]) }}" class="{{ $allImages->count() > 1 ? 'col-span-12 md:col-span-8' : 'col-span-12' }} h-full rounded-[40px] overflow-hidden shadow-sm group relative bg-slate-50 border border-gray-100 shadow-sm cursor-pointer">
                @if($service->image)
                    <img src="{{ Str::startsWith($service->image, 'http') ? $service->image : '/' . ltrim($service->image, '/') }}" 
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                @else
                    <div class="w-full h-full bg-gray-50 flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-image text-6xl"></i>
                    </div>
                @endif
                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <i class="fa-solid fa-expand text-white text-3xl drop-shadow-lg"></i>
                </div>
            </a>
            @if($allImages->count() > 1)
            <!-- Side Small Images -->
            <div class="hidden md:flex md:col-span-4 flex-col gap-4 h-full">
                @if($allImages->count() > 1)
                    <a href="{{ route('services.gallery', ['id' => $service->id, 'index' => 1]) }}" class="flex-1 rounded-[32px] overflow-hidden shadow-sm group relative bg-slate-50 border border-gray-100 cursor-pointer">
                        <img src="{{ $allImages[1] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                        </div>
                    </a>
                @endif
                @if($allImages->count() > 2)
                    <a href="{{ route('services.gallery', ['id' => $service->id, 'index' => 2]) }}" class="flex-1 rounded-[32px] overflow-hidden shadow-sm relative group bg-slate-50 border border-gray-100 cursor-pointer">
                        <img src="{{ $allImages[2] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <!-- Float Badge in Center -->
                        <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px] flex items-center justify-center transition-all group-hover:bg-black/50">
                            <div class="bg-white/95 backdrop-blur-sm px-6 py-2.5 rounded-2xl font-bold text-slate-800 text-sm shadow-lg flex items-center gap-2.5 transform transition-all group-hover:scale-105 pointer-events-auto">
                                <i class="fa-solid fa-images text-slate-500"></i>
                                <span>{{ count($allImages) }} صور</span>
                            </div>
                        </div>
                    </a>
                @endif
            </div>
            @endif
        </div>

        <!-- Navigation Tabs -->
        <div class="flex gap-3 mb-10 overflow-x-auto no-scrollbar py-2">
            <button onclick="switchTab('description')" id="btn-description" class="tab-btn active px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-primary text-white shadow-xl shadow-blue-100">نبذة عن الخدمة</button>
            <button onclick="switchTab('reviews')" id="btn-reviews" class="tab-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap bg-white text-gray-500 border border-gray-100 hover:border-primary/20">التقييمات</button>
        </div>

        <!-- Main Content Layout -->
        <div class="w-full">
            
            <!-- Tab Contents (Full Width) -->
            <div class="w-full">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 md:p-12 mb-20 min-h-[500px]">
                    
                    <!-- Description Panel -->
                    <div id="tab-description" class="tab-content transition-all duration-500">
                        <h3 class="text-2xl font-black mb-8 relative inline-block">
                            نبذة عن الخدمة
                            <span class="absolute -bottom-2 right-0 w-12 h-1.5 bg-primary rounded-full"></span>
                        </h3>
                        <div class="text-gray-600 leading-loose text-lg font-medium">
                            {!! nl2br(e($service->description)) !!}
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
                                            <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star"></i>
                                        @endfor
                                    </div>
                                    <p class="text-gray-400 font-bold">{{ $totalReviews }} تقييم</p>
                                </div>
                                <div class="space-y-3">
                                    @foreach($ratingDistribution as $rating => $data)
                                    <div class="flex items-center gap-4">
                                        <span class="text-gray-400 font-bold text-xs w-8 text-left">%{{ $data['percentage'] }}</span>
                                        <div class="flex-1 h-2.5 bg-gray-50 rounded-full overflow-hidden">
                                            <div class="h-full bg-gray-900 rounded-full" style="width: {{ $data['percentage'] }}%"></div>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-[30px]">
                                            <span class="text-gray-900 font-black text-sm">{{ $rating }}</span>
                                            <i class="fa-solid fa-star text-yellow-400 text-[10px]"></i>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <button onclick="openReviewModal()" class="w-full py-4 bg-primary text-white rounded-2xl font-black shadow-lg shadow-primary/20 hover:scale-[1.01] transition-all mb-8">قيم تجربتك</button>
                            <div class="space-y-4">
                                @forelse($reviews as $review)
                                <div class="p-6 bg-gray-50/50 rounded-[24px] border border-gray-100 relative group transition-all hover:bg-white hover:shadow-md">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-primary text-white rounded-xl flex items-center justify-center font-black">
                                                {{ mb_substr($review->reviewer->name ?? 'أ', 0, 1) }}
                                            </div>
                                            <div>
                                                <h5 class="text-gray-900 font-black">{{ $review->reviewer->name ?? 'مستخدم' }}</h5>
                                                <div class="flex text-yellow-400 text-[10px] mt-1">
                                                    @for($i=1; $i<=5; $i++)
                                                        <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-gray-400 text-xs font-bold">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-500 text-sm font-medium pr-16">{{ $review->comment }}</p>
                                </div>
                                @empty
                                <div class="text-center py-10 text-gray-400 font-bold">لا توجد تقييمات لهذا المعلن حتى الآن</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[40px] w-full max-w-lg overflow-hidden shadow-2xl scale-95 transition-transform duration-300" id="reportModalContent">
        <div class="p-8 md:p-10">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-black text-gray-900">الإبلاغ عن المعلن</h3>
                <button onclick="closeReportModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('users.report', $service->user_id) }}" method="POST">
                @csrf
                <div class="mb-8">
                    <label class="block text-sm font-black text-gray-700 mb-4">سبب الإبلاغ</label>
                    <textarea name="description" required rows="5" class="w-full bg-gray-50 border-2 border-gray-100 rounded-3xl p-6 font-bold text-gray-900 focus:border-primary focus:ring-0 transition-all placeholder:text-gray-300" placeholder="اكتب سبب الإبلاغ هنا بالتفصيل..."></textarea>
                </div>
                
                <div class="flex flex-col gap-4">
                    <button type="submit" class="w-full bg-red-500 text-white py-5 rounded-[24px] font-black text-lg shadow-xl shadow-red-500/20 hover:bg-red-600 transition-all">إرسال البلاغ</button>
                    <button type="button" onclick="closeReportModal()" class="w-full py-4 text-gray-400 font-black text-sm hover:text-gray-600 transition-all">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[40px] w-full max-w-lg overflow-hidden shadow-2xl scale-95 transition-transform duration-300" id="reviewModalContent">
        <div class="p-8 md:p-10">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-black text-gray-900">أضف تقييمك</h3>
                <button onclick="closeReviewModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('reviews.store') }}" method="POST">
                @csrf
                <input type="hidden" name="rated_user_id" value="{{ $service->user_id }}">
                <input type="hidden" name="rating" id="ratingInput" value="5">
                
                <div class="mb-8 text-center">
                    <label class="block text-sm font-black text-gray-700 mb-4">قيم تجربتك مع المعلن</label>
                    <div class="flex items-center justify-center gap-3 text-4xl text-gray-200" id="starRating">
                        <i class="fa-solid fa-star cursor-pointer hover:text-yellow-400 transition-colors text-yellow-400" data-value="1"></i>
                        <i class="fa-solid fa-star cursor-pointer hover:text-yellow-400 transition-colors text-yellow-400" data-value="2"></i>
                        <i class="fa-solid fa-star cursor-pointer hover:text-yellow-400 transition-colors text-yellow-400" data-value="3"></i>
                        <i class="fa-solid fa-star cursor-pointer hover:text-yellow-400 transition-colors text-yellow-400" data-value="4"></i>
                        <i class="fa-solid fa-star cursor-pointer hover:text-yellow-400 transition-colors text-yellow-400" data-value="5"></i>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-black text-gray-700 mb-4">تعليقك (اختياري)</label>
                    <textarea name="comment" rows="4" class="w-full bg-gray-50 border-2 border-gray-100 rounded-3xl p-6 font-bold text-gray-900 focus:border-primary focus:ring-0 transition-all placeholder:text-gray-300" placeholder="اكتب تعليقك هنا..."></textarea>
                </div>
                
                <div class="flex flex-col gap-4">
                    <button type="submit" class="w-full bg-primary text-white py-5 rounded-[24px] font-black text-lg shadow-xl shadow-primary/20 hover:bg-blue-700 transition-all">إرسال التقييم</button>
                    <button type="button" onclick="closeReviewModal()" class="w-full py-4 text-gray-400 font-black text-sm hover:text-gray-600 transition-all">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[40px] w-full max-w-md overflow-hidden shadow-2xl scale-95 transition-transform duration-300" id="deleteModalContent">
        <div class="p-8 md:p-10 text-center">
            <!-- Icon Warning -->
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-100 text-3xl shadow-lg shadow-red-50">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            
            <h3 class="text-2xl font-black text-gray-900 mb-3">حذف الخدمة نهائياً</h3>
            <p class="text-gray-500 font-medium mb-8 leading-relaxed">هل أنت متأكد من رغبتك في حذف هذه الخدمة؟ لن تتمكن من استعادة البيانات أو تفاصيل الخدمة بعد إتمام عملية الحذف.</p>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="confirmDelete()" class="flex-1 bg-red-500 text-white py-4 rounded-2xl font-black text-base shadow-xl shadow-red-500/20 hover:bg-red-600 transition-all">نعم، تأكيد الحذف</button>
                <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-gray-50 text-gray-500 border border-gray-100 py-4 rounded-2xl font-black text-base hover:bg-gray-100 transition-all">إلغاء</button>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-btn.active { color: #ffffff !important; }
    #reportModal.active { display: flex; }
    #reportModal.active #reportModalContent { transform: scale(1); }
    #reviewModal.active { display: flex; }
    #reviewModal.active #reviewModalContent { transform: scale(1); }
    #deleteModal.active { display: flex; }
    #deleteModal.active #deleteModalContent { transform: scale(1); }
</style>

<script>
    // Tab switching script (Direct match to Ads details tab behavior)
    function switchTab(tab) {
        // Hide all content
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Deactivate all buttons
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active', 'bg-primary', 'text-white', 'shadow-xl', 'shadow-blue-100');
            el.classList.add('bg-white', 'text-gray-500', 'border', 'border-gray-100');
        });

        // Show target content
        document.getElementById('tab-' + tab).classList.remove('hidden');
        
        // Activate target button
        const btn = document.getElementById('btn-' + tab);
        if (btn) {
            btn.classList.add('active', 'bg-primary', 'text-white', 'shadow-xl', 'shadow-blue-100');
            btn.classList.remove('bg-white', 'text-gray-500', 'border', 'border-gray-100');
        }
    }

    // Review Modal Logic
    function openReviewModal() {
        @if(!Auth::check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif
        const modal = document.getElementById('reviewModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeReviewModal() {
        const modal = document.getElementById('reviewModal');
        modal.classList.remove('active');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    // Star Rating Logic
    document.querySelectorAll('#starRating i').forEach(star => {
        star.addEventListener('click', function() {
            const val = this.getAttribute('data-value');
            document.getElementById('ratingInput').value = val;
            
            // Update UI
            document.querySelectorAll('#starRating i').forEach(s => {
                if (s.getAttribute('data-value') <= val) {
                    s.classList.add('text-yellow-400');
                    s.classList.remove('text-gray-200');
                } else {
                    s.classList.add('text-gray-200');
                    s.classList.remove('text-yellow-400');
                }
            });
        });
    });

    function shareService() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $service->title }}',
                url: window.location.href
            }).catch(console.error);
        } else {
            // Fallback: Copy to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('تم نسخ رابط الخدمة بنجاح');
            });
        }
    }

    function openReportModal() {
        const modal = document.getElementById('reportModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeReportModal() {
        const modal = document.getElementById('reportModal');
        modal.classList.remove('active');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    // Delete Modal Functions
    function openDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('active');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function confirmDelete() {
        document.getElementById('deleteServiceForm').submit();
    }
</script>

@if($lat && $lng)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('adMap').setView([{{ $lat }}, {{ $lng }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([{{ $lat }}, {{ $lng }}]).addTo(map)
            .bindPopup('{{ $address }}')
            .openPopup();
    });
</script>
@endif
@endsection
