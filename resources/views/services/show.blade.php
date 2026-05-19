@extends('layouts.main')

@section('title', $service->title)

@section('content')
<div class="service-details-page min-h-screen bg-[#F8FAFC] pb-20" dir="rtl">
    <!-- Breadcrumbs Section -->
    <div class="container mx-auto px-4 md:px-16 pt-12 pb-6">
        <div class="flex flex-col gap-6">
            <!-- Breadcrumbs Pill -->
            <nav class="bg-primary px-6 py-3 w-auto md:w-fit rounded-full flex items-center gap-3 text-white text-sm font-bold shadow-lg shadow-primary/20">
                <a href="{{ route('home') }}" class="hover:text-blue-100 transition-colors">الرئيسية</a>
                <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                <a href="{{ route('search', ['category' => $service->category_id]) }}" class="hover:text-blue-100 transition-colors">{{ $service->category->name ?? 'خدمات' }}</a>
                <i class="fa-solid fa-chevron-left text-[10px] opacity-50"></i>
                <span class="text-blue-100">{{ $service->title }}</span>
            </nav>

            <!-- Page Title & Actions -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <h1 class="text-3xl md:text-5xl font-black text-gray-900">{{ $service->title }}</h1>
                <div class="flex items-center gap-3">
                    <button onclick="shareService()" class="flex items-center gap-2 px-5 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 text-gray-500 font-bold hover:bg-gray-50 transition-all">
                        <i class="fa-solid fa-share-nodes"></i>
                        مشاركة
                    </button>
                    <button onclick="openReportModal()" class="flex items-center gap-2 px-5 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 text-red-500 font-bold hover:bg-red-50 transition-all">
                        <i class="fa-solid fa-flag"></i>
                        الإبلاغ عن هذا المعلن
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="container mx-auto px-4 md:px-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Right Side: Gallery & Info (8 Cols) -->
            <div class="lg:col-span-8 space-y-10">
                <!-- Gallery Section -->
                <div class="grid grid-cols-12 gap-4 h-[400px] md:h-[500px]">
                    <!-- Main Large Image -->
                    <a href="{{ route('services.gallery', ['id' => $service->id, 'index' => 0]) }}" class="col-span-12 md:col-span-8 h-full rounded-[40px] overflow-hidden shadow-sm group relative">
                        <img src="{{ $service->image ? (Str::startsWith($service->image, 'http') ? $service->image : '/' . ltrim($service->image, '/')) : 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=1000' }}" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-expand text-white text-3xl drop-shadow-lg"></i>
                        </div>
                    </a>
                    <!-- Side Small Images -->
                    <div class="hidden md:flex md:col-span-4 flex-col gap-4 h-full">
                        <a href="{{ route('services.gallery', ['id' => $service->id, 'index' => 1]) }}" class="flex-1 rounded-[32px] overflow-hidden shadow-sm group relative">
                            <img src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=600" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                            </div>
                        </a>
                        <a href="{{ route('services.gallery', ['id' => $service->id, 'index' => 2]) }}" class="flex-1 rounded-[32px] overflow-hidden shadow-sm relative group">
                            <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=600" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[2px] transition-all group-hover:bg-black/50">
                                <div class="bg-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl flex items-center gap-2 transform transition-all group-hover:scale-105">
                                    <i class="fa-solid fa-images"></i>
                                    <span>مشاهدة الكل</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Info Section with Tabs -->
                <div class="bg-white rounded-[40px] p-8 md:p-12 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-8 mb-10 border-b border-gray-100">
                        <button onclick="switchTab('overview')" id="tab-overview" class="tab-btn active pb-6 text-xl font-black text-primary border-b-4 border-primary transition-all">نبذة عن الخدمة</button>
                        <button onclick="switchTab('reviews')" id="tab-reviews" class="tab-btn pb-6 text-xl font-bold text-gray-400 hover:text-gray-600 transition-all">التقييمات</button>
                    </div>

                    <!-- Overview Content -->
                    <div id="content-overview" class="tab-content">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">نبذة عن الخدمة</h2>
                        <div class="prose prose-blue max-w-none text-gray-500 font-bold leading-loose">
                            {!! nl2br(e($service->description)) !!}
                        </div>
                    </div>

                    <!-- Reviews Content -->
                    <div id="content-reviews" class="tab-content hidden">
                        <!-- Rating Summary Box -->
                        <div class="bg-[#F1F1F1] rounded-[32px] p-8 mb-10">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-10 mb-8">
                                <div class="flex items-center gap-8">
                                    <div class="text-center">
                                        <span class="text-5xl font-black text-gray-900 block mb-2">{{ $avgRating }}</span>
                                        <div class="flex items-center gap-1 text-yellow-400 mb-2">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star"></i>
                                            @endfor
                                        </div>
                                        <span class="text-gray-400 text-sm font-bold">{{ $totalReviews }} تقييم</span>
                                    </div>
                                    <div class="space-y-2 flex-1 min-w-[250px]">
                                        @foreach($ratingDistribution as $rating => $data)
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-bold text-gray-500 w-6">%{{ $data['percentage'] }}</span>
                                            <div class="flex-1 h-2 bg-white rounded-full overflow-hidden">
                                                <div class="h-full bg-gray-800 rounded-full" style="width: {{ $data['percentage'] }}%"></div>
                                            </div>
                                            <i class="fa-solid fa-star text-yellow-400 text-xs"></i>
                                            <span class="text-sm font-bold text-gray-500 w-4">{{ $rating }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <button onclick="openReviewModal()" class="w-full py-4 bg-primary text-white rounded-2xl font-black shadow-lg shadow-primary/20 hover:scale-[1.01] transition-all">قيم تجربتك</button>
                        </div>

                        <div class="space-y-6">
                            @forelse($reviews as $review)
                                <div class="p-6 bg-gray-50 rounded-[32px] border border-gray-100">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-4">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($review->reviewer->name) }}&background=random" class="w-12 h-12 rounded-2xl object-cover">
                                            <div>
                                                <h4 class="font-black text-gray-900">{{ $review->reviewer->name }}</h4>
                                                <span class="text-xs text-gray-400 font-bold">{{ $review->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 text-yellow-400 text-sm">
                                            @for($j=0; $j<$review->rating; $j++) <i class="fa-solid fa-star"></i> @endfor
                                        </div>
                                    </div>
                                    <p class="text-gray-600 font-bold leading-relaxed">{{ $review->comment }}</p>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <p class="text-gray-400 font-bold">لا توجد تقييمات حالياً</p>
                                </div>
                            @endforelse
                            @if($totalReviews > 4)
                            <div class="flex justify-end mt-4">
                                <button class="bg-primary text-white px-8 py-3 rounded-xl font-black shadow-lg shadow-primary/20 hover:scale-[1.05] transition-all flex items-center gap-2">
                                    <i class="fa-solid fa-arrows-rotate text-sm"></i>
                                    عرض المزيد
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Related Services (From this Seller) -->
                <div>
                    <h2 class="text-2xl font-black text-gray-900 mb-8">خدمات من هذا المعلن</h2>
                    <div class="flex flex-wrap gap-4">
                        @foreach($userServices as $uService)
                            <a href="{{ route('services.show', $uService->id) }}" class="bg-[#E8F1FE] text-primary px-8 py-4 rounded-full font-black hover:bg-blue-100 transition-all border border-blue-100/50">
                                {{ $uService->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Left Side: Pricing & Seller (4 Cols) -->
            <div class="lg:col-span-4 space-y-10">
                <!-- Pricing Card -->
                <div class="bg-white rounded-[40px] p-8 md:p-10 shadow-sm border border-gray-100 sticky top-10">
                    <div class="mb-8">
                        <span class="bg-blue-50 text-primary px-4 py-1.5 rounded-xl font-black text-sm">{{ $service->category->name ?? 'سيارات' }}</span>
                        <h2 class="text-3xl font-black text-gray-900 mt-4">{{ $service->title }}</h2>
                        <div class="flex items-center gap-2 mt-4 text-primary">
                            <span class="text-4xl font-black">{{ number_format($service->price) }}</span>
                            <x-riyal-icon class="w-10 h-10" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button class="w-full bg-primary text-white py-5 rounded-[24px] font-black text-lg shadow-xl shadow-primary/20 hover:bg-blue-700 transition-all flex items-center justify-center gap-3">
                            اطلب الخدمة
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                    </div>

                    <!-- Seller Info Mini -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <h3 class="text-lg font-black text-gray-900 mb-6">معلومات المعلن</h3>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="relative">
                                <img src="{{ $service->user->avatar ? (Str::startsWith($service->user->avatar, 'http') ? $service->user->avatar : '/' . ltrim($service->user->avatar, '/')) : 'https://ui-avatars.com/api/?name='.urlencode($service->user->name) }}" 
                                     class="w-16 h-16 rounded-[24px] object-cover border-2 border-white shadow-sm">
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-4 border-white rounded-full"></div>
                            </div>
                            <div>
                                <h4 class="text-xl font-black text-gray-900">{{ $service->user->name }}</h4>
                                <p class="text-sm text-gray-400 font-bold">متخصص في خدمات السيارات</p>
                                <a href="{{ route('users.profile', $service->user_id) }}" class="text-primary text-xs font-black hover:underline mt-1 block">عرض جميع الإعلانات</a>
                            </div>
                        </div>
                        <p class="text-gray-400 text-sm font-bold leading-relaxed mb-8">لوريم إيبسوم دولار سيت آميت، كونسيكتيتور أديبيسيسينغ إيليت. فيتولو أوتيندوم إيغيت، إليفيند سيمبوس سيم.</p>
                        
                        <!-- Stats Removed as requested -->

                        <!-- Chat Button Moved Here -->
                        <button class="w-full mt-6 bg-white text-primary border-2 border-primary py-4 rounded-[20px] font-black text-base hover:bg-blue-50 transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-message"></i>
                            دردشة
                        </button>
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

<style>
    .prose p { margin-bottom: 1.5rem; }
    .tab-btn.active { color: #1A73E8; border-color: #1A73E8; }
    #reportModal.active { display: flex; }
    #reportModal.active #reportModalContent { transform: scale(1); }
    #reviewModal.active { display: flex; }
    #reviewModal.active #reviewModalContent { transform: scale(1); }
</style>

<script>
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

    function switchTab(tab) {
        // Hide all content
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Deactivate all buttons
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active', 'text-primary', 'border-primary');
            el.classList.add('text-gray-400');
        });

        // Show target content
        document.getElementById('content-' + tab).classList.remove('hidden');
        // Activate target button
        const btn = document.getElementById('tab-' + tab);
        btn.classList.add('active', 'text-primary', 'border-primary');
        btn.classList.remove('text-gray-400');
    }
</script>
@endsection
