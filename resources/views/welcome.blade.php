<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مزاد | المنصة الأولى للمزادات</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Cairo:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        body { font-family: 'Cairo', 'Outfit', sans-serif; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-right">
                <div class="logo">Logo</div>
                <div class="location-picker" onclick="handleLocationClick()">
                    <i class="fa-solid fa-chevron-down"></i>
                    <span>كل المدن (السعودية)</span>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg" alt="Saudi Flag" class="flag-icon">
                </div>
            </div>
            
            <div class="search-bar">
                <input type="text" >
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <div class="nav-actions">
                @auth
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">لوحة التحكم</a>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline" title="تسجيل الخروج">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">تسجيل الدخول</a>
                    <a href="{{ route('login') }}" class="btn btn-primary"> أضف اعلانك</a>
                @endauth
            </div>
    </nav>

    <div class="categories-nav">
        <div class="container">
            <div class="category-pills">
                @foreach($discoveryCategories as $category)
                    <a href="#" class="category-pill">{{ $category->name }}</a>
                @endforeach
                {{-- Additional placeholders if DB is empty --}}
             
            </div>
        </div>
    </div>

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
                                        'show_button' => $item['show_button'] ?? true
                                    ];
                                }
                            } else {
                                $slidesList[] = [
                                    'title' => $banner->title,
                                    'description' => $banner->description,
                                    'image' => $banner->image,
                                    'button_text' => $banner->button_text,
                                    'show_button' => true
                                ];
                            }
                        }
                        
                        if (empty($slidesList)) {
                            $slidesList[] = [
                                'title' => 'ابحث، بع، أو زايد',
                                'description' => 'منصة موثوقة تمكّنك من تصفح آلاف الإعلانات أو إضافة إعلانك خلال دقائق.',
                                'image' => 'images/hero_car.png',
                                'button_text' => '',
                                'show_button' => false
                            ];
                        }
                    @endphp

                    @foreach($slidesList as $index => $item)
                        <div class="banner-slide {{ $index === 0 ? 'active' : '' }}">
                            <div class="hero-text">
                                <h1>{{ $item['title'] }}</h1>
                                <p>{{ $item['description'] }}</p>
                                @if(!empty($item['show_button']) && !empty($item['button_text']))
                                    <a href="#" class="btn btn-primary" style="margin-top: 2rem; display: inline-block;">{{ $item['button_text'] }}</a>
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

        <!-- Discovery Categories -->
        <section class="section discovery-categories">
            <div class="section-header">
                <h2 class="section-title">اكتشف أبرز الفئات</h2>
                <a href="#" class="view-all-btn">عرض الكل</a>
            </div>
            
            <div class="categories-carousel-container">
                                <button class="carousel-arrow next" id="catNext"><i class="fa-solid fa-chevron-right"></i></button>

                
                <div class="categories-carousel">
                    <div class="category-track" id="catTrack">
                        @foreach($discoveryCategories as $index => $category)
                            <a href="#" class="category-item">
                                <div class="category-icon">
                                    <img src="{{ $category->image ? (Str::startsWith($category->image, 'uploads/') ? asset($category->image) : asset('uploads/' . $category->image)) : 'https://mazad-admin.com/uploads/categories/placeholder.png' }}" alt="{{ $category->name }}">
                                </div>
                                <span class="category-name">{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <button class="carousel-arrow prev" id="catPrev"><i class="fa-solid fa-chevron-left"></i></button>

            </div>

            <div class="carousel-dots">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot active"></div>
            </div>
        </section>

        <!-- Featured Ads Section -->
        <section class="section featured-ads">
            <div class="section-header">
                <h2 class="section-title">أبرز الإعلانات</h2>
                <a href="#" class="view-all-btn">عرض الكل</a>
            </div>
            
            <div class="ads-carousel-outer">
                <button class="carousel-arrow prev" id="adsPrev"><i class="fa-solid fa-chevron-left"></i></button>
                
                <div class="ads-track" id="adsTrack">
                    @forelse($interactiveAds as $ad)
                        @include('partials.ad-card', ['ad' => $ad])
                    @empty
                        <div class="w-full text-center py-10">
                            <p class="text-slate-500 font-bold">لا توجد إعلانات حالياً</p>
                        </div>
                    @endforelse
                </div>

                <button class="carousel-arrow next" id="adsNext"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </section>

        <script>
            // Ads Carousel Logic
            document.addEventListener('DOMContentLoaded', function() {
                const adsTrack = document.getElementById('adsTrack');
                const adsPrev = document.getElementById('adsPrev');
                const adsNext = document.getElementById('adsNext');

                if (adsTrack && adsPrev && adsNext) {
                    const scrollAmount = 400;
                    
                    adsNext.addEventListener('click', () => {
                        adsTrack.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    });

                    adsPrev.addEventListener('click', () => {
                        adsTrack.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                    });
                }

                // Countdown Timer Logic
                function updateCountdowns() {
                    document.querySelectorAll('.p-countdown').forEach(el => {
                        const endData = el.dataset.end;
                        if (!endData) {
                            el.style.display = 'none';
                            return;
                        }

                        const endDate = new Date(endData).getTime();
                        const now = new Date().getTime();
                        const diff = endDate - now;

                        const timerSpan = el.querySelector('.countdown-timer');

                        if (isNaN(endDate) || diff <= 0) {
                            timerSpan.innerText = "منتهي";
                            return;
                        }

                        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                        timerSpan.innerText = `${days} يوم : ${hours} ساعة : ${minutes} دقيقة : ${seconds} ثانية`;
                    });
                }

                setInterval(updateCountdowns, 1000);
                updateCountdowns();
            });
        </script>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col" style="grid-column: span 2;">
                    <div class="footer-logo">Logo</div>
                    <p style="opacity: 0.8; max-width: 300px;">المنصة الرائدة للمزادات العلنية والإلكترونية في المنطقة. نسعى لتوفير تجربة آمنة وشفافة للجميع.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="#">الرئيسية</a></li>
                        <li><a href="#">عن المنصة</a></li>
                        <li><a href="#">كيفية المشاركة</a></li>
                        <li><a href="#">تواصل معنا</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>الفئات</h4>
                    <ul>
                        <li><a href="#">سيارات</a></li>
                        <li><a href="#">عقارات</a></li>
                        <li><a href="#">الكترونيات</a></li>
                        <li><a href="#">خدمات</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>المساعدة</h4>
                    <ul>
                        <li><a href="#">الأسئلة الشائعة</a></li>
                        <li><a href="#">سياسة الخصوصية</a></li>
                        <li><a href="#">الشروط والأحكام</a></li>
                        <li><a href="#">مركز المساعدة</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                جميع الحقوق محفوظة &copy; {{ date('Y') }} لمزادي. صنع بكل حب.
            </div>
        </div>
    <!-- Location Modal -->
    <div id="locationModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="modalBody">
                {{-- Content will be injected by JS --}}
            </div>
        </div>
    </div>
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.banner-slide');
        const dots = document.querySelectorAll('.dot');

        function showSlide(n) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        function goToSlide(n) {
            showSlide(n);
        }

        // Auto slide
        if (slides.length > 1) {
            setInterval(nextSlide, 5000);
        }

        function handleLocationClick() {
            const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
            const modal = document.getElementById('locationModal');
            const body = document.getElementById('modalBody');

            if (!isLoggedIn) {
                body.innerHTML = `
                    <div style="padding: 1rem;">
                        <i class="fa-solid fa-user-lock" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                        <h2 style="margin-bottom: 1rem;">يرجى تسجيل الدخول أولاً</h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem;">يجب أن تكون مسجلاً لدينا لتتمكن من تحديد عنوانك واختيار مدينتك بدقة.</p>
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 0.75rem 2rem;">تسجيل الدخول</a>
                            <button class="btn btn-outline" onclick="closeModal()" style="padding: 0.75rem 2rem;">إلغاء</button>
                        </div>
                    </div>
                `;
            } else {
                body.innerHTML = `
                    <div style="padding: 1rem;">
                        <h2 style="margin-bottom: 0.5rem;">اختر موقعك</h2>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">حدد العنوان المطلوب لعرض المزادات القريبة منك.</p>
                        <div class="map-container">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.7!2d46.67!3d24.71!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f038!2sRiyadh%20Saudi%20Arabia!5e0!3m2!1sen!2ssa!4v1620000000000" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                        <button class="btn btn-primary" style="margin-top: 1.5rem; width: 100%; padding: 1rem;" onclick="closeModal()">تأكيد الموقع</button>
                    </div>
                `;
            }
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('locationModal').style.display = 'none';
        }

        // Close on overlay click
        window.onclick = function(event) {
            const modal = document.getElementById('locationModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        // Categories Carousel Logic
        const track = document.getElementById('catTrack');
        const carousel = track.parentElement;
        const prevBtn = document.getElementById('catPrev');
        const nextBtn = document.getElementById('catNext');
        
        const scrollStep = 300;

        if (nextBtn) {
            nextBtn.onclick = () => {
                carousel.scrollBy({ left: -scrollStep, behavior: 'smooth' });
            };
        }

        if (prevBtn) {
            prevBtn.onclick = () => {
                carousel.scrollBy({ left: scrollStep, behavior: 'smooth' });
            };
        }
    </script>
</body>
</html>
