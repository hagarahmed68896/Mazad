<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مزاد | المنصة الأولى للمزادات</title>

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

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <style>
        body { font-family: 'Cairo', 'Outfit', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Cairo', sans-serif; font-weight: 700; }
        .container {
            max-width: 100% !important;
            padding-left: 64px !important;
            padding-right: 64px !important;
        }
        @media (max-width: 1024px) {
            .container { padding-left: 24px !important; padding-right: 24px !important; }
        }
        .fa-heart.fa-solid { color: #EF4444 !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <!-- Navbar -->
    @include('partials.navbar')
    @include('partials.sidebar')

    @include('partials.categories-bar')

    <main class="container">
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-banner" id="heroBanner">
                <div class="banner-wrapper" id="bannerWrapper">
                     @php 
                        $slidesList = [];
                        foreach($banners as $banner) {
                            if (is_array($banner->items) && count($banner->items) > 0) {
                                foreach($banner->items as $item) {
                                    $slidesList[] = [
                                        'title' => $item['title'] ?? $banner->title,
                                        'description' => $item['description'] ?? $banner->description,
                                        'image' => $item['image'] ?? $banner->image,
                                        'button_text' => $item['button_text'] ?? $banner->button_text,
                                        'show_button' => $item['show_button'] ?? true,
                                        'url' => $item['url'] ?? '#'
                                    ];
                                }
                            } else {
                                $slidesList[] = [
                                    'title' => $banner->title,
                                    'description' => $banner->description,
                                    'image' => $banner->image,
                                    'button_text' => $banner->button_text,
                                    'show_button' => true,
                                    'url' => $banner->url ?? '#'
                                ];
                            }
                        }
                        
                        if (empty($slidesList)) {
                            $slidesList[] = [
                                'title' => 'ابحث، بع، أو زايد',
                                'description' => 'منصة موثوقة تمكّنك من تصفح آلاف الإعلانات أو إضافة إعلانك خلال دقائق.',
                                'image' => 'images/hero_car.png',
                                'button_text' => '',
                                'show_button' => false,
                                'url' => '#'
                            ];
                        }
                    @endphp

                    @foreach($slidesList as $index => $item)
                        <div class="banner-slide {{ $index === 0 ? 'active' : '' }}">
                            <div class="hero-text">
                                <h1>{{ $item['title'] }}</h1>
                                <p>{{ $item['description'] }}</p>
                                @if(!empty($item['show_button']) && !empty($item['button_text']))
                                    <a href="{{ $item['url'] }}" class="btn btn-primary" style="margin-top: 2rem; display: inline-block;">{{ $item['button_text'] }}</a>
                                @endif
                            </div>
                            <div class="hero-image">
                                @php
                                    $imgPath = $item['image'];
                                    if (!str_starts_with($imgPath, 'http') && !str_starts_with($imgPath, 'images/')) {
                                        $imgPath = asset($imgPath);
                                    } elseif (str_starts_with($imgPath, 'images/')) {
                                        $imgPath = asset($imgPath);
                                    }
                                @endphp
                                <img src="{{ $imgPath }}" alt="Mazad Hero">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Banner Controls -->
                @if(count($slidesList) > 1)
                    <div class="hero-controls">
                        <div class="banner-dots">
                            @foreach($slidesList as $index => $item)
                                <div class="dot {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})"></div>
                            @endforeach
                        </div>
                        <div class="banner-arrows">
                            <div class="arrow-btn" onclick="prevSlide()"><i class="fa-solid fa-chevron-right"></i></div>
                            <div class="arrow-btn" onclick="nextSlide()"><i class="fa-solid fa-chevron-left"></i></div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        @auth
        <!-- Reports Section -->
        <section class="section reports-section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <h2 class="section-title" style="margin: 0;font-weight: bold;font-size: 2rem;">تقاريري</h2>
                <a href="{{ route('reports.index') }}" class="view-all-btn">عرض التفاصيل</a>
            </div>
            <div class="reports-card">
                <div class="reports-info">
                    <div class="info-item">
                        <span class="info-label">الإيرادات</span>
                        <div class="info-value">
                            <span class="value">{{ number_format($totalRevenue) }}</span>
                            <span class="currency">ر.س</span>
                            <span class="trend up"><i class="fa-solid fa-arrow-up"></i> 0.43%</span>
                        </div>
                    </div>
                    <div class="reports-filter">
                        <select onchange="window.location.href='?filter=' + this.value">
                            <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                            <option value="monthly" {{ request('filter') == 'monthly' ? 'selected' : '' }}>شهري</option>
                            <option value="yearly" {{ request('filter') == 'yearly' ? 'selected' : '' }}>سنوي</option>
                        </select>
                    </div>
                </div>
                <div class="reports-chart">
                    <!-- Y Axis Labels -->
                    <div class="chart-y-labels">
                        <span>100M</span>
                        <span>50M</span>
                        <span>25M</span>
                        <span>0</span>
                    </div>
                    
                    <!-- Grid Lines -->
                    <div class="chart-grid">
                        <div class="grid-line"></div>
                        <div class="grid-line"></div>
                        <div class="grid-line"></div>
                        <div class="grid-line"></div>
                    </div>

                    @php
                        $maxVal = collect($chartData)->max('total') ?: 100;
                        $count = count($chartData);
                        $points = "";
                        foreach($chartData as $index => $data) {
                            $x = $index * (1000 / ($count - 1));
                            $y = 200 - ($data['total'] / $maxVal * 180);
                            $points .= ($index == 0 ? "M" : " L") . "$x,$y";
                        }
                    @endphp
                    <!-- Dynamic SVG Chart -->
                    <svg viewBox="0 0 1000 200" preserveAspectRatio="none">
                        <path d="{{ $points }}" fill="none" stroke="#1A73E8" stroke-width="4" stroke-linecap="round" />
                        @foreach($chartData as $index => $data)
                            @php 
                                $x = $index * (1000 / ($count - 1));
                                $y = 200 - ($data['total'] / $maxVal * 180);
                            @endphp
                            <circle cx="{{ $x }}" cy="{{ $y }}" r="6" fill="#1A73E8" stroke="white" stroke-width="2" />
                        @endforeach
                    </svg>
                </div>
                <div class="chart-labels">
                    @foreach($chartData as $data)
                        <span>{{ $data['day'] }}</span>
                    @endforeach
                </div>
            </div>
        </section>
        @endauth

        @if($discoveryCategories->count() > 0)
        <!-- Discovery Categories -->
        <section class="section discovery-categories">
            <div class="section-header d-flex justify-content-between align-items-center">
                <h2 class="section-title">اكتشف أبرز الفئات</h2>
                <a href="{{ route('categories.all') }}" class="view-all-btn">عرض الكل</a>
            </div>
            
            <div class="categories-carousel-container">
                <button class="carousel-arrow next" id="catNext"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="categories-carousel">
                    <div class="category-track" id="catTrack">
                        @foreach($discoveryCategories as $index => $category)
                            <a href="{{ route('categories.show', $category->id) }}" 
                               onclick="window.location.href='{{ route('categories.show', $category->id) }}'"
                               class="category-item" 
                               style="position: relative !important; z-index: 999 !important; pointer-events: auto !important;">
                               <div class="category-icon">
                                    <img src="{{ $category->image }}" alt="{{ $category->name }}">
                                </div>
                                <span class="category-name">{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <button class="carousel-arrow prev" id="catPrev"><i class="fa-solid fa-chevron-left"></i></button>
            </div>
        </section>
        @endif

        @auth
            @if($recentlyViewed->count() > 0)
            <!-- Recently Viewed Section -->
            <section class="section featured-ads">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h2 class="section-title">الأكثر مشاهدة مؤخراً</h2>
                    <a href="{{ route('search') }}" class="view-all-btn">عرض الكل</a>
                </div>
                
                <div class="ads-carousel-outer">
                    <button class="carousel-arrow next" id="adsNextRV"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="ads-track" id="adsTrackRV">
                        @foreach($recentlyViewed as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>
                    <button class="carousel-arrow prev" id="adsPrevRV"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </section>
            @endif

            @if($currentAuctions->count() > 0)
            <!-- Current Auctions Section -->
            <section class="section featured-ads">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h2 class="section-title">المزادات الحالية</h2>
                    <a href="{{ route('search', ['type' => 'auction']) }}" class="view-all-btn">عرض الكل</a>
                </div>
                
                <div class="ads-carousel-outer">
                    <button class="carousel-arrow next" id="adsNextCA"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="ads-track" id="adsTrackCA">
                        @foreach($currentAuctions as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>
                    <button class="carousel-arrow prev" id="adsPrevCA"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </section>
            @endif

            @if($instantBuy->count() > 0)
            <!-- Instant Buy Section -->
            <section class="section featured-ads">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h2 class="section-title">عروض الشراء الفوري</h2>
                    <a href="{{ route('search', ['type' => 'direct_purchase']) }}" class="view-all-btn">عرض الكل</a>
                </div>
                
                <div class="ads-carousel-outer">
                    <button class="carousel-arrow next" id="adsNextIB"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="ads-track" id="adsTrackIB">
                        @foreach($instantBuy as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>
                    <button class="carousel-arrow prev" id="adsPrevIB"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </section>
            @endif
        @endauth

        @guest
            @if($featuredAds->count() > 0)
            <!-- Featured Ads Section -->
            <section class="section featured-ads">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h2 class="section-title">أبرز الإعلانات</h2>
                    <a href="{{ route('search') }}" class="view-all-btn">عرض الكل</a>
                </div>
                
                <div class="ads-carousel-outer">
                    <button class="carousel-arrow next" id="adsNext1"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="ads-track" id="adsTrack1">
                        @foreach($featuredAds as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>
                    <button class="carousel-arrow prev" id="adsPrev1"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </section>
            @endif

            @if($interactiveAds->count() > 0)
            <!-- Interactive Ads Section -->
            <section class="section featured-ads">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h2 class="section-title">الإعلانات الأكثر تفاعل</h2>
                    <a href="{{ route('search') }}" class="view-all-btn">عرض الكل</a>
                </div>
                
                <div class="ads-carousel-outer">
                    <button class="carousel-arrow next" id="adsNext2"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="ads-track" id="adsTrack2">
                        @foreach($interactiveAds as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>
                    <button class="carousel-arrow prev" id="adsPrev2"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </section>
            @endif

            @if($endingSoon->count() > 0)
            <!-- Ending Soon Ads Section -->
            <section class="section featured-ads">
                <div class="section-header">
                    <h2 class="section-title">الإعلانات قرب الانتهاء</h2>
                    <a href="{{ route('search', ['sort' => 'ending_soon']) }}" class="view-all-btn">عرض الكل</a>
                </div>
                
                <div class="ads-carousel-outer">
                    <button class="carousel-arrow next" id="adsNext3"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="ads-track" id="adsTrack3">
                        @foreach($endingSoon as $ad)
                            @include('partials.ad-card', ['ad' => $ad])
                        @endforeach
                    </div>
                    <button class="carousel-arrow prev" id="adsPrev3"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </section>
            @endif
        @endguest

    </main>

    @include('partials.footer')

    <script>
        // Global variables
        let currentSlide = 0;
        let currentLocationMarker = null;

        // Functions defined globally to be accessible from HTML onclick
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

        window.toggleNotifications = function() {
            const dropdown = document.getElementById('notificationDropdown');
            if (!dropdown) return;
            const cartDropdown = document.getElementById('cartDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if (profileDropdown) profileDropdown.style.display = 'none';
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
            
            if (profileDropdown) profileDropdown.style.display = 'none';
            if (notifDropdown) notifDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';
            
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        };

        window.toggleProfileDropdown = function() {
            const dropdown = document.getElementById('profileDropdown');
            if (!dropdown) return;
            const notificationDropdown = document.getElementById('notificationDropdown');
            const cartDropdown = document.getElementById('cartDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if (notificationDropdown) notificationDropdown.style.display = 'none';
            if (cartDropdown) cartDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';
            
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        };

        window.toggleNavbarSearch = function() {
            const input = document.getElementById('navbarSearchInput');
            if (!input) return;
            if (input.style.width === '200px') {
                input.style.width = '0';
                input.style.opacity = '0';
                input.style.padding = '0.4rem 0';
            } else {
                input.style.width = '200px';
                input.style.opacity = '1';
                input.style.padding = '0.4rem 1rem';
                input.focus();
            }
        };

        window.closeModal = function() {
            const modal = document.getElementById('locationModal');
            if (modal) modal.style.display = 'none';
        };

        window.handleLocationClick = function() {
            // using global isLoggedIn
            const userSavedLat = {{ Auth::check() && Auth::user()->latitude ? Auth::user()->latitude : 'null' }};
            const userSavedLng = {{ Auth::check() && Auth::user()->longitude ? Auth::user()->longitude : 'null' }};
            
            const modal = document.getElementById('locationModal');
            const body = document.getElementById('modalBody');
            if (!modal || !body) return;

            if (!isLoggedIn) {
                body.innerHTML = `
                    <div style="padding: 1rem; text-align: center;">
                        <i class="fa-solid fa-user-lock" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                        <h2 style="margin-bottom: 1rem;">يرجى تسجيل الدخول أولاً</h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem;">يجب أن تكون مسجلاً لدينا لتتمكن من تحديد عنوانك واختيار مدينتك بدقة.</p>
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 0.75rem 2rem;">تسجيل الدخول</a>
                            <button class="btn btn-outline" onclick="closeModal()" style="padding: 0.75rem 2rem;">إلغاء</button>
                        </div>
                    </div>
                `;
                modal.style.display = 'flex';
            } else {
                body.innerHTML = `
                    <div style="padding: 1rem; text-align: center;">
                        <h2 style="margin-bottom: 0.5rem;">اختر موقعك</h2>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">قم بالضغط على أي مكان في الخريطة أو سحب العلامة لتحديد موقعك بدقة.</p>
                        <div id="map" style="height: 350px; border-radius: 12px; margin-bottom: 1rem;"></div>
                        <button class="btn btn-primary" id="confirmLocationBtn" style="width: 100%; padding: 1rem;" onclick="confirmLocation()">تأكيد هذا الموقع</button>
                    </div>
                `;
                modal.style.display = 'flex';
                
                setTimeout(() => {
                    const initialLat = userSavedLat || 24.7136;
                    const initialLng = userSavedLng || 46.6753;
                    const zoomLevel = userSavedLat ? 14 : 6;
                    
                    const map = L.map('map').setView([initialLat, initialLng], zoomLevel);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                    
                    const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
                    currentLocationMarker = marker;

                    map.on('click', function(e) {
                        marker.setLatLng(e.latlng);
                    });

                    if (!userSavedLat && navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(position => {
                            const userLat = position.coords.latitude;
                            const userLng = position.coords.longitude;
                            map.setView([userLat, userLng], 13);
                            marker.setLatLng([userLat, userLng]);
                        }, () => {});
                    }
                }, 300);
            }
        };

        window.confirmLocation = function() {
            const btn = document.getElementById('confirmLocationBtn');
            if (!btn) return;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري استنتاج العنوان...';
            btn.disabled = true;

            if (!currentLocationMarker) return;
            const pos = currentLocationMarker.getLatLng();
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}&accept-language=ar`)
                .then(res => res.json())
                .then(data => {
                    let cityOnly = "السعودية";
                    if (data.address) {
                        const addr = data.address;
                        let city = addr.city || addr.town || addr.village || addr.state || "السعودية";
                        city = city.replace(/محافظة |منطقة |مدينة /g, '');
                        cityOnly = city + "، السعودية";
                    }
                    saveLocation(pos.lat, pos.lng, cityOnly, cityOnly);
                })
                .catch(err => {
                    console.error(err);
                    saveLocation(pos.lat, pos.lng, "الرياض، السعودية", "الرياض، السعودية");
                });
        };

        function saveLocation(lat, lng, address, city) {
            const btn = document.getElementById('confirmLocationBtn');
            if(btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';

            fetch('{{ route("location.update") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ latitude: lat, longitude: lng, address: address })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const desktopName = document.getElementById('currentLocationName');
                    const mobileName = document.getElementById('currentLocationNameMobile');
                    if (desktopName) desktopName.innerText = city;
                    if (mobileName) mobileName.innerText = city;
                    closeModal();
                } else {
                    alert("خطأ: " + (data.message || "حدث خطأ"));
                    if(btn) { btn.innerHTML = 'تأكيد هذا الموقع'; btn.disabled = false; }
                }
            })
            .catch(err => {
                console.error(err);
                alert("حدث خطأ في الاتصال");
                if(btn) { btn.innerHTML = 'تأكيد هذا الموقع'; btn.disabled = false; }
            });
        }

        // Banner slides logic
        window.showSlide = function(n) {
            const slides = document.querySelectorAll('.banner-slide');
            const dots = document.querySelectorAll('.dot');
            if (slides.length === 0) return;
            slides[currentSlide].classList.remove('active');
            if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            if (dots[currentSlide]) dots[currentSlide].classList.add('active');
        };

        window.nextSlide = function() { showSlide(currentSlide + 1); };
        window.prevSlide = function() { showSlide(currentSlide - 1); };
        window.goToSlide = function(n) { showSlide(n); };

        document.addEventListener('DOMContentLoaded', function() {
            // Banner Auto Slide
            const slides = document.querySelectorAll('.banner-slide');
            if (slides.length > 1) {
                setInterval(nextSlide, 5000);
            }

            // Carousel initialization helper
            const setupCarousel = (trackId, prevId, nextId) => {
                const track = document.getElementById(trackId);
                const prevBtn = document.getElementById(prevId);
                const nextBtn = document.getElementById(nextId);
                if (track && prevBtn && nextBtn) {
                    const scrollAmount = 400;
                    nextBtn.onclick = () => track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    prevBtn.onclick = () => track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                }
            };

            setupCarousel('adsTrack1', 'adsPrev1', 'adsNext1');
            setupCarousel('adsTrack2', 'adsPrev2', 'adsNext2');
            setupCarousel('adsTrack3', 'adsPrev3', 'adsNext3');
            setupCarousel('adsTrackRV', 'adsPrevRV', 'adsNextRV');
            setupCarousel('adsTrackCA', 'adsPrevCA', 'adsNextCA');
            setupCarousel('adsTrackIB', 'adsPrevIB', 'adsNextIB');

            const catTrack = document.getElementById('catTrack');
            if (catTrack) {
                const catNext = document.getElementById('catNext');
                const catPrev = document.getElementById('catPrev');
                if (catNext) catNext.onclick = () => catTrack.parentElement.scrollBy({ left: -300, behavior: 'smooth' });
                if (catPrev) catPrev.onclick = () => catTrack.parentElement.scrollBy({ left: 300, behavior: 'smooth' });
            }

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
                    if (isNaN(endDate) || diff <= 0) { timerSpan.innerText = "منتهي"; return; }
                    const d = Math.floor(diff / 86400000);
                    const h = Math.floor((diff % 86400000) / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    timerSpan.innerText = `${d} يوم : ${h} ساعة : ${m} دقيقة : ${s} ثانية`;
                });
            }
            setInterval(updateCountdowns, 1000);
            updateCountdowns();
        });

        // Outside-click listeners
        window.addEventListener('click', function(e) {
            const dropdowns = ['notificationDropdown', 'cartDropdown', 'profileDropdown'];
            const buttons = ['notificationBtn', 'cartBtn', 'userProfileBtn'];
            
            dropdowns.forEach((id, index) => {
                const drop = document.getElementById(id);
                const btn = document.getElementById(buttons[index]);
                if (drop && !drop.contains(e.target) && btn && !btn.contains(e.target)) {
                    drop.style.display = 'none';
                }
            });

            const locModal = document.getElementById('locationModal');
            const authModal = document.getElementById('authRequiredModal');
            if (e.target === locModal) closeModal();
            if (e.target === authModal) hideAuthModal();

            const addAdDropdown = document.getElementById('addAdDropdown');
            const addAdBtn = document.getElementById('addAdBtn');
            const addAdDropdownGuest = document.getElementById('addAdDropdownGuest');
            const addAdBtnGuest = document.getElementById('addAdBtnGuest');

            if (addAdDropdown && addAdBtn && !addAdDropdown.contains(e.target) && !addAdBtn.contains(e.target)) {
                addAdDropdown.style.display = 'none';
            }
            if (addAdDropdownGuest && addAdBtnGuest && !addAdDropdownGuest.contains(e.target) && !addAdBtnGuest.contains(e.target)) {
                addAdDropdownGuest.style.display = 'none';
            }
        });

        window.toggleAddAdDropdown = function() {
            const dropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            if(!dropdown) return;
            
            ['notificationDropdown', 'cartDropdown', 'profileDropdown'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            
            dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
        };

        // Global Auth Guard
        // using global isLoggedIn below

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
    </script>
    <!-- Location Picker Modal -->
    <div id="locationModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="modalBody">
                <!-- Content will be injected by handleLocationClick -->
            </div>
        </div>
    </div>

    @include('partials.add-ad-modal')
    @include('partials.account-type-modal')
    @include('partials.interests-modal')


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
    @stack('scripts')
</body>
</html>
