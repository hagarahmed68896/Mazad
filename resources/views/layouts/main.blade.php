<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'مزاد | المنصة الأولى للمزادات')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        body { font-family: 'IBM Plex Sans Arabic', 'Outfit', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'IBM Plex Sans Arabic', sans-serif; font-weight: 700; }
        
        /* Force landing.css container behavior even with Tailwind */
        .container {
            max-width: 100% !important;
            padding-left: 64px !important;
            padding-right: 64px !important;
        }

        @media (max-width: 1024px) {
            .container {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
        }
        
        /* Modern Navbar Fixes */
        .add-ad-btn {
            white-space: nowrap;
            padding: 0.75rem 1.5rem !important;
            min-width: max-content;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }

        /* Notification Dropdown Styling */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 320px;
            background: #F7F7F7;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-top: 1rem;
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
            padding: 1.5rem;
            transform: translateX(-20%);
        }
        .notification-dropdown.active {
            display: flex;
        }
        .empty-notifications p {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1C1C1C;
            margin-top: 1rem;
        }

        /* Notification Badge */
        .nav-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #EF4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        /* Notification Item Styling */
        .notification-item {
            display: flex;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #F1F5F9;
            transition: all 0.2s;
            text-align: right;
            text-decoration: none;
        }
        .notification-item.unread {
            background: #EBF4FF;
        }
        .notification-item:hover {
            background: #F8FAFC;
        }
        .notif-avatar {
            width: 45px;
            height: 45px;
            background: #1A73E8;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
            font-size: 1.2rem;
        }
        .notif-content {
            flex: 1;
        }
        .notif-content h4 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #1E293B;
            margin-bottom: 0.25rem;
            margin-top: 0;
        }
        .notif-content p {
            font-size: 0.85rem;
            color: #64748B;
            line-height: 1.4;
            margin-bottom: 0.4rem;
        }
        .notif-time {
            font-size: 0.75rem;
            color: #94A3B8;
            font-weight: 600;
        }
        .notif-footer {
            padding: 1.25rem;
            text-align: center;
            border-top: 1px solid #F1F5F9;
        }
        .notif-footer a {
            color: #94A3B8;
            font-weight: 700;
            font-size: 0.95rem;
            transition: color 0.2s;
            text-decoration: none;
        }
        .notif-footer a:hover {
            color: #1A73E8;
        }
        .notif-header h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1C1C1C;
            margin-bottom: 2rem;
            text-align: right;
            margin-top: 0;
        }
        .empty-notifications {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .bell-icon-container {
            position: relative;
            margin-bottom: 2rem;
        }
        .bell-bg {
            width: 140px;
            height: 140px;
            background: #EDEDED;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #D1D5DB;
            position: relative;
        }
        .bell-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            width: 35px;
            height: 35px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 800;
            color: #64748B;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .bell-rays {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
        }
        .bell-rays span {
            width: 4px;
            height: 15px;
            background: #D1D5DB;
            border-radius: 2px;
            display: block;
        }
        .bell-rays span:nth-child(1) { transform: rotate(45deg); }
        .bell-rays span:nth-child(3) { transform: rotate(-45deg); }
        
        /* Responsive Fixes for Navbar */
        @media (max-width: 1024px) {
            .navbar .container {
                grid-template-columns: auto 1fr;
                gap: 1rem;
                padding-bottom: 0.5rem;
            }
            .nav-center-visual {
                display: none;
            }
            .location-picker {
                display: none !important; 
            }
            .user-greeting {
                display: none;
            }
        }

        /* Mobile Navbar with Sub-row for Location */
        @media (max-width: 640px) {
            .navbar .container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 0.75rem;
                padding-bottom: 0.75rem;
            }
            .nav-right-visual {
                order: 1;
                flex: 1;
            }
            .nav-left-visual {
                order: 2;
                flex: 1;
                justify-content: flex-end !important;
            }
            .mobile-location-row {
                display: block !important;
                width: 100%;
                order: 3;
                margin-top: 0.5rem;
            }
            .mobile-location-row .location-picker {
                display: flex !important;
                width: 100%;
                justify-content: space-between;
                background: #F1F5F9;
                padding: 0.75rem 1rem;
            }
            .nav-icons {
                gap: 0.25rem !important;
            }
            .nav-icon-link svg {
                width: 20px;
                height: 20px;
            }
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
            .add-ad-btn {
                display: none;
            }
        }

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .fa-heart.fa-solid { color: #EF4444 !important; }
    </style>
    @stack('styles')
</head>
<body>

    @include('partials.navbar')
    @include('partials.categories-bar')

    @yield('content')

    @include('partials.footer')

    <!-- Location Modal -->
    <div id="locationModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="modalBody">
            </div>
        </div>
    </div>
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

    <!-- Auth / Favorite Popup -->
    <div id="authFavModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:white; border-radius:2rem; padding:2.5rem; max-width:420px; width:90%; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.2); position:relative;">
            <button onclick="document.getElementById('authFavModal').style.display='none'" style="position:absolute; top:1.2rem; left:1.2rem; background:none; border:none; font-size:1.5rem; color:#94A3B8; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            <div style="width:70px; height:70px; background:linear-gradient(135deg,#FEE2E2,#FECACA); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <i class="fa-solid fa-heart" style="font-size:1.8rem; color:#EF4444;"></i>
            </div>
            <h2 style="font-size:1.4rem; font-weight:800; color:#1E293B; margin-bottom:0.75rem;">أضف للمفضلة</h2>
            <p style="color:#64748B; margin-bottom:2rem; line-height:1.7;">سجّل دخولك أو أنشئ حساباً مجانياً لتتمكن من حفظ إعلاناتك المفضلة ومتابعتها في أي وقت.</p>
            <div style="display:flex; gap:0.75rem;">
                <button onclick="openAccountModal(); document.getElementById('authFavModal').style.display='none'" style="flex:1; padding:0.9rem; background:#1A73E8; color:white; border-radius:12px; font-weight:700; border:none; cursor:pointer;">تسجيل الدخول</button>
                <button onclick="document.getElementById('authFavModal').style.display='none'" style="flex:1; padding:0.9rem; background:#F1F5F9; color:#475569; border:none; border-radius:12px; font-weight:700; cursor:pointer;">إلغاء</button>
            </div>
        </div>
    </div>

    <script>
        function toggleNavbarSearch() {
            const input = document.getElementById('navbarSearchInput');
            if(!input) return;
            const isExpanded = input.style.width === '180px';
            
            if (isExpanded) {
                input.style.width = '0';
                input.style.opacity = '0';
                input.style.padding = '0.4rem 0';
            } else {
                input.style.width = '180px';
                input.style.opacity = '1';
                input.style.padding = '0.4rem 1rem';
                input.focus();
            }
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            if(!dropdown) return;
            dropdown.classList.toggle('active');
            
            if (dropdown.classList.contains('active')) {
                const searchInput = document.getElementById('navbarSearchInput');
                if(searchInput) {
                    searchInput.style.width = '0';
                    searchInput.style.opacity = '0';
                }
            }
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const btn = document.getElementById('notificationBtn');
            if (!dropdown || !btn) return;
            
            if (!dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        function handleLocationClick() {
            const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
            const modal = document.getElementById('locationModal');
            const body = document.getElementById('modalBody');

            if (!isLoggedIn) {
                body.innerHTML = `
                    <div style="padding: 1rem; text-align:center;">
                        <i class="fa-solid fa-user-lock" style="font-size: 3rem; color: #1A73E8; margin-bottom: 1.5rem;"></i>
                        <h2 style="margin-bottom: 1rem;">يرجى تسجيل الدخول أولاً</h2>
                        <p style="color: #64748B; margin-bottom: 2rem;">يجب أن تكون مسجلاً لدينا لتتمكن من تحديد عنوانك واختيار مدينتك بدقة.</p>
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
                        <p style="color: #64748B; margin-bottom: 1.5rem;">حدد العنوان المطلوب لعرض المزادات القريبة منك.</p>
                        <div class="map-container" style="height: 300px; border-radius: 1rem; overflow: hidden;">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.7!2d46.67!3d24.71!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f038!2sRiyadh%20Saudi%20Arabia!5e0!3m2!1sen!2ssa!4v1620000000000" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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

        // Global Auth Guard
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

        function requireAuth(callback) {
            console.log('requireAuth check, isLoggedIn:', isLoggedIn);
            if (isLoggedIn) {
                if (typeof callback === 'function') callback();
            } else {
                console.log('User not logged in, showing modal');
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

        // Global Wishlist Logic
        function toggleGlobalWishlist(event, adId) {
            console.log('Toggling Wishlist for Ad:', adId);
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
                console.log('Wishlist Response:', data);
                if (data.status === 'success') {
                    if (icons.length > 0) {
                        icons.forEach(icon => {
                            if (data.action === 'added') {
                                icon.classList.remove('fa-regular');
                                icon.classList.add('fa-solid');
                                icon.style.setProperty('color', '#EF4444', 'important');
                            } else {
                                icon.classList.remove('fa-solid');
                                icon.classList.add('fa-regular');
                                icon.style.color = '';
                            }
                        });
                    }

                    // Special case for ad details page
                    if (typeof toggleWishlistUI === 'function') {
                        toggleWishlistUI(data.action);
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
            .catch(error => {
                console.error('Wishlist Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء تحديث المفضلة'
                });
            });
        }

        window.onclick = function(event) {
            const locModal = document.getElementById('locationModal');
            const authModal = document.getElementById('authRequiredModal');
            if (event.target == locModal) closeModal();
            if (event.target == authModal) hideAuthModal();
        }
    </script>
    @include('partials.account-type-modal')
    @stack('scripts')
</body>
</html>
