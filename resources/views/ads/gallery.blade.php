@extends('layouts.main')

@section('title', 'معرض الصور | ' . $ad->title)

@push('styles')
<style>
    .gallery-page {
        background-color: #F8FAFC;
        min-height: 100vh;
    }

    .gallery-main-image-container {
        aspect-ratio: 16/9;
        background: white;
        border-radius: 40px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        border: 1px solid white;
    }

    @media (min-width: 768px) {
        .gallery-main-image-container {
            aspect-ratio: 21/9;
        }
    }

    .gallery-thumb {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .gallery-thumb img {
        transition: all 0.3s;
    }

    .gallery-thumb.active {
        border-color: #1A73E8;
    }

    .gallery-thumb.active img {
        opacity: 1;
        transform: scale(1.05);
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Custom Login-style Footer */
    .login-footer {
        background-color: #1557B0;
        background-image: linear-gradient(135deg, #1557B0 0%, #0a3066 100%);
        position: relative;
        overflow: hidden;
        padding: 24px 0;
        text-align: center;
        color: white;
    }
    .login-footer::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 350px;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><polygon points="0,0 70,0 0,100" fill="rgba(14, 62, 126, 0.4)"/><polygon points="0,20 40,0 0,60" fill="rgba(14, 62, 126, 0.6)"/></svg>');
        background-size: cover;
        z-index: 1;
    }
    .login-footer p {
        position: relative;
        z-index: 2;
    }

    /* Remove standard footer for this page */
    footer:not(.login-footer) {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="gallery-page py-12" dir="rtl">
    <div class="container mx-auto px-4 md:px-16">
        <!-- Breadcrumbs & Title -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-8">
                 <div class="flex items-center gap-4">
                    <a href="{{ route('ads.show', $ad->id) }}" class="w-12 h-12 bg-white border border-gray-100 rounded-2xl flex items-center justify-center text-gray-400 hover:text-primary transition-all shadow-sm">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-2xl md:text-4xl font-black text-gray-900">{{ $ad->title }}</h2>
                            <span class="bg-amber-100 text-amber-600 px-4 py-1.5 rounded-full text-xs font-bold border border-amber-200">شراء فوري</span>
                        </div>
                        <a href="{{ route('users.profile', $ad->user_id) }}" class="text-gray-400 font-bold text-sm hover:text-primary transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-xs"></i> المعلن: <span class="text-gray-900">{{ $ad->user->name }}</span>
                        </a>
                    </div>
                 </div>
            @php
                $is_favorited = Auth::check() && Auth::user()->favorites()->where('ad_id', $ad->id)->exists();
            @endphp
            <div class="hidden md:flex gap-3">
                <button onclick="requireAuth(() => toggleGlobalWishlist(event, {{ $ad->id }}))" class="bg-white border border-gray-100 px-6 py-3 rounded-2xl text-sm font-bold text-gray-700 flex items-center gap-2 hover:bg-gray-50 transition-all shadow-sm">
                    <i class="{{ $is_favorited ? 'fa-solid' : 'fa-regular' }} fa-heart wishlist-icon-{{ $ad->id }}" style="{{ $is_favorited ? 'color: #EF4444 !important;' : '' }}"></i> 
                    <span class="wishlist-text-{{ $ad->id }}">{{ $is_favorited ? 'في المفضلة' : 'أضف إلى مفضلتي' }}</span>
                </button>
                <button onclick="shareAd()" class="bg-white border border-gray-100 px-6 py-3 rounded-2xl text-sm font-bold text-gray-700 flex items-center gap-2 hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fa-solid fa-share-nodes text-primary"></i> مشاركة
                </button>
            </div>
            </div>
        </div>

        <!-- Main Slider Container -->
        <div class="relative max-w-7xl mx-auto mb-16 group">
            <!-- Navigation Arrows -->
            <button onclick="galleryPrev()" class="absolute left-8 top-1/2 -translate-y-1/2 w-14 h-14 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-gray-400 hover:text-primary shadow-xl z-20 transition-all opacity-0 group-hover:opacity-100">
                <i class="fa-solid fa-chevron-left text-xl"></i>
            </button>
            
            <button onclick="galleryNext()" class="absolute right-8 top-1/2 -translate-y-1/2 w-14 h-14 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-gray-400 hover:text-primary shadow-xl z-20 transition-all opacity-0 group-hover:opacity-100">
                <i class="fa-solid fa-chevron-right text-xl"></i>
            </button>

            <!-- Main Image Viewport -->
            <div class="gallery-main-image-container relative">
                <img id="galleryMainImage" src="{{ $allImages[$startIndex] ?? $allImages[0] }}" class="w-full h-full object-contain transition-all duration-700 ease-out">
                
                <!-- Bottom Gradient Shadow -->
                <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-black/20 to-transparent pointer-events-none"></div>
            </div>

            <!-- Image Counter -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10">
                <div class="bg-black/50 backdrop-blur-md text-white px-6 py-2 rounded-full font-black text-lg tracking-wider">
                    <span id="galleryCounter">{{ $startIndex + 1 }} / {{ count($allImages) }}</span>
                </div>
            </div>
        </div>

        <!-- Thumbnails Row -->
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-center gap-5 overflow-x-auto no-scrollbar pb-12 px-4">
                @foreach($allImages as $index => $img)
                    <div class="gallery-thumb flex-shrink-0 w-32 md:w-48 aspect-[4/3] rounded-3xl overflow-hidden border-4 {{ $index === $startIndex ? 'border-primary active' : 'border-transparent' }} shadow-lg transition-all" 
                         onclick="setGalleryImage({{ $index }})" data-index="{{ $index }}">
                        <img src="{{ $img }}" class="w-full h-full object-cover {{ $index === $startIndex ? 'opacity-100' : 'opacity-60' }} hover:opacity-100 transition-opacity">
                    </div>
                @endforeach
            </div>
        </div>


    </div>
</div>

<!-- Custom Footer -->
<footer class="login-footer w-full">
    <p class="text-white text-sm font-medium" dir="ltr">{{ $settings['copyright'] ?? '©2025 copyright' }}</p>
</footer>
@endsection

@push('scripts')
<script>
    const allImages = {!! json_encode($allImages) !!};
    let currentIndex = {{ $startIndex }};

    // Initialize gallery state on load
    document.addEventListener('DOMContentLoaded', () => {
        setGalleryImage(currentIndex);
    });

    function galleryNext() {
        currentIndex = (currentIndex + 1) % allImages.length;
        setGalleryImage(currentIndex);
    }

    function galleryPrev() {
        currentIndex = (currentIndex - 1 + allImages.length) % allImages.length;
        setGalleryImage(currentIndex);
    }

    function setGalleryImage(index) {
        currentIndex = index;
        const mainImg = document.getElementById('galleryMainImage');
        const counter = document.getElementById('galleryCounter');
        
        // Main Image Animation
        mainImg.style.opacity = '0';
        mainImg.style.transform = 'scale(0.98)';
        
        setTimeout(() => {
            mainImg.src = allImages[index];
            mainImg.style.opacity = '1';
            mainImg.style.transform = 'scale(1)';
        }, 150);

        if (counter) counter.innerText = `${index + 1} / ${allImages.length}`;

        // Update Thumbnails
        document.querySelectorAll('.gallery-thumb').forEach(thumb => {
            const idx = parseInt(thumb.getAttribute('data-index'));
            const img = thumb.querySelector('img');
            if (idx === index) {
                thumb.classList.add('border-primary', 'active');
                thumb.classList.remove('border-transparent');
                if (img) img.classList.replace('opacity-60', 'opacity-100');
                
                // Scroll thumbnail into view
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('border-primary', 'active');
                thumb.classList.add('border-transparent');
                if (img) img.classList.replace('opacity-100', 'opacity-60');
            }
        });
    }

    // Keyboard Navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') galleryNext();
        if (e.key === 'ArrowLeft') galleryPrev();
    });

    // Share Functionality
    function shareAd() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $ad->title }}',
                text: 'شاهد هذا الإعلان على منصة مزاد',
                url: '{{ route("ads.show", $ad->id) }}'
            }).then(() => {
                console.log('Successful share');
            }).catch((error) => {
                console.log('Error sharing', error);
            });
        } else {
            // Fallback: Copy to clipboard
            const el = document.createElement('textarea');
            el.value = '{{ route("ads.show", $ad->id) }}';
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
        const textElements = document.querySelectorAll(`.wishlist-text-${adId}`);
        if (textElements.length > 0) {
            textElements.forEach(el => {
                el.innerText = action === 'added' ? 'في المفضلة' : 'أضف إلى مفضلتي';
            });
        }
    }
</script>
@endpush
