<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'مزاد | المنصة الأولى للمزادات')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <style>
        body { font-family: 'Cairo', 'Outfit', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Cairo', sans-serif; font-weight: 700; }
        
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .fa-heart.fa-solid { color: #EF4444 !important; }
    </style>
    
    <!-- Leaflet (Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('styles')
</head>
<body>

    @include('partials.navbar')
    @include('partials.sidebar')
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
                <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="block w-full bg-primary text-white font-black py-4 rounded-xl hover:bg-blue-700 transition-all text-center text-lg shadow-lg shadow-blue-200">
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
        let locationMapInstance = null;
        let currentLocationMarker = null;

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

        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};

        function toggleNavbarSearch() {
            // If screen is mobile/tablet, go straight to search page
            if (window.innerWidth <= 1024) {
                window.location.href = "{{ route('search') }}";
                return;
            }

            const navbar = document.querySelector('.navbar');
            const container = document.querySelector('.search-container');
            const input = document.getElementById('navbarSearchInput');
            if(!navbar || !container || !input) return;
            
            container.classList.toggle('active');
            navbar.classList.toggle('search-mode');
            
            if (container.classList.contains('active')) {
                input.focus();
            } else {
                input.value = '';
            }
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            const cartDropdown = document.getElementById('cartDropdown');
            if(!dropdown) return;
            const profileDropdown = document.getElementById('profileDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if(!dropdown) return;

            if (profileDropdown) profileDropdown.style.display = 'none';
            if (cartDropdown) cartDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';

            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function toggleCart() {
            const dropdown = document.getElementById('cartDropdown');
            const notifDropdown = document.getElementById('notificationDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if(!dropdown) return;

            if (profileDropdown) profileDropdown.style.display = 'none';
            if (notifDropdown) notifDropdown.style.display = 'none';
            if (addAdDropdown) addAdDropdown.style.display = 'none';

            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const btn = document.getElementById('notificationBtn');
            const cartDropdown = document.getElementById('cartDropdown');
            const cartBtn = document.getElementById('cartBtn');
            
            if (dropdown && btn && !dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.style.display = 'none';
            }
            
            if (cartDropdown && cartBtn && !cartDropdown.contains(event.target) && !cartBtn.contains(event.target)) {
                cartDropdown.style.display = 'none';
            }

            const addAdDropdown = document.getElementById('addAdDropdown');
            const addAdBtn = document.getElementById('addAdBtn');
            const addAdDropdownGuest = document.getElementById('addAdDropdownGuest');
            const addAdBtnGuest = document.getElementById('addAdBtnGuest');

            if (addAdDropdown && addAdBtn && !addAdDropdown.contains(event.target) && !addAdBtn.contains(event.target)) {
                addAdDropdown.style.display = 'none';
            }
            if (addAdDropdownGuest && addAdBtnGuest && !addAdDropdownGuest.contains(event.target) && !addAdBtnGuest.contains(event.target)) {
                addAdDropdownGuest.style.display = 'none';
            }

            const profileDropdown = document.getElementById('profileDropdown');
            const userProfileBtn = document.getElementById('userProfileBtn');
            if (profileDropdown && userProfileBtn && !profileDropdown.contains(event.target) && !userProfileBtn.contains(event.target)) {
                profileDropdown.style.display = 'none';
            }
        });

        function toggleAddAdDropdown() {
            const dropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            const cartDropdown = document.getElementById('cartDropdown');
            const notifDropdown = document.getElementById('notificationDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            
            if(!dropdown) return;
            
            if(cartDropdown) cartDropdown.style.display = 'none';
            if(notifDropdown) notifDropdown.style.display = 'none';
            if(profileDropdown) profileDropdown.style.display = 'none';
            
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            const notifDropdown = document.getElementById('notificationDropdown');
            const cartDropdown = document.getElementById('cartDropdown');
            const addAdDropdown = document.getElementById('addAdDropdown') || document.getElementById('addAdDropdownGuest');
            
            if(!dropdown) return;
            
            if(notifDropdown) notifDropdown.style.display = 'none';
            if(cartDropdown) cartDropdown.style.display = 'none';
            if(addAdDropdown) addAdDropdown.style.display = 'none';
            
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function handleLocationClick() {
            if (!isLoggedIn) {
                showAuthModal();
                return;
            }

            const modal = document.getElementById('locationModal');
            const body = document.getElementById('modalBody');
            if (!modal || !body) return;

            body.innerHTML = `
                <div style="padding: 1rem; text-align: center;">
                    <h2 style="margin-bottom: 0.5rem; font-weight: 800;">اختر موقعك</h2>
                    <p style="color: #64748B; margin-bottom: 1.5rem;">قم بالضغط على أي مكان في الخريطة أو سحب العلامة لتحديد موقعك بدقة.</p>
                    <div id="map" style="height: 350px; border-radius: 12px; margin-bottom: 1rem; position: relative;"></div>
                    <button class="btn btn-primary" id="confirmLocationBtn" style="width: 100%; padding: 1rem;" onclick="confirmLocation()">تأكيد هذا الموقع</button>
                </div>
            `;
            modal.style.display = 'flex';
            
            setTimeout(() => {
                const initialLat = {{ Auth::check() ? (Auth::user()->latitude ?? 'null') : 'null' }} || 24.7136;
                const initialLng = {{ Auth::check() ? (Auth::user()->longitude ?? 'null') : 'null' }} || 46.6753;
                const hasSaved = {{ Auth::check() && Auth::user()->latitude ? 'true' : 'false' }};
                const zoomLevel = hasSaved ? 14 : 6;
                
                if (locationMapInstance) {
                    locationMapInstance.remove();
                }

                locationMapInstance = L.map('map').setView([initialLat, initialLng], zoomLevel);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(locationMapInstance);
                
                currentLocationMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(locationMapInstance);

                locationMapInstance.on('click', function(e) {
                    currentLocationMarker.setLatLng(e.latlng);
                });

                if (!hasSaved && navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        if (userLat >= 16 && userLat <= 32.5 && userLng >= 34 && userLng <= 56) {
                            locationMapInstance.setView([userLat, userLng], 13);
                            currentLocationMarker.setLatLng([userLat, userLng]);
                        }
                    }, () => {});
                }
            }, 300);
        }

        function closeModal() {
            const modal = document.getElementById('locationModal');
            if (modal) modal.style.display = 'none';
        }

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
                    const isSaudi = data.address && (data.address.country_code === 'sa' || (data.address.country && data.address.country.includes('السعودية')));
                    if (!isSaudi) {
                        alert("الرجاء اختيار موقع داخل المملكة العربية السعودية فقط.");
                        if(btn) { btn.innerHTML = 'تأكيد هذا الموقع'; btn.disabled = false; }
                        return;
                    }

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
                body: JSON.stringify({ latitude: lat, longitude: lng, address: address, city: city })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const desktopName = document.getElementById('currentLocationName');
                    const mobileName = document.getElementById('currentLocationNameMobile');
                    if (desktopName) desktopName.innerText = city;
                    if (mobileName) mobileName.innerText = city;
                    closeModal();
                    
                    // Reload the page and append city_id if found
                    const url = new URL(window.location.href);
                    if (data.city_id) {
                        url.searchParams.set('city', data.city_id);
                    } else {
                        url.searchParams.delete('city');
                    }
                    window.location.href = url.toString();
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

        // Global Auth Guard
        // isLoggedIn is already defined at the top of the script tag

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

                    if (data.action === 'added') {
                        const txtEl = document.getElementById('wishlistText');
                        if (txtEl) txtEl.innerText = 'في المفضلة';
                    } else {
                        const txtEl = document.getElementById('wishlistText');
                        if (txtEl) txtEl.innerText = 'أضف للمفضلة';
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

        // Global Look Later Logic
        function toggleGlobalLookLater(event, adId) {
            console.log('Toggling Look Later for Ad:', adId);
            const icons = document.querySelectorAll(`.look-later-icon-${adId}`);
            
            fetch(`/look-later/toggle/${adId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Look Later Response:', data);
                if (data.status === 'success') {
                    if (icons.length > 0) {
                        icons.forEach(icon => {
                            if (data.action === 'added') {
                                icon.classList.remove('fa-regular');
                                icon.classList.add('fa-solid');
                                icon.style.setProperty('color', '#1A73E8', 'important');
                            } else {
                                icon.classList.remove('fa-solid');
                                icon.classList.add('fa-regular');
                                icon.style.color = '';
                            }
                        });
                    }

                    if (data.action === 'added') {
                        const txtEl = document.getElementById('lookLaterText');
                        if (txtEl) txtEl.innerText = 'محفوظ للاطلاع لاحقاً';
                    } else {
                        const txtEl = document.getElementById('lookLaterText');
                        if (txtEl) txtEl.innerText = 'حفظ للاطلاع لاحقاً';
                    }

                    // Special case for details page
                    if (typeof toggleLookLaterUI === 'function') {
                        toggleLookLaterUI(data.action);
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
                console.error('Look Later Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء تحديث قائمة الاطلاع لاحقاً'
                });
            });
        }

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

        window.onclick = function(event) {
            const locModal = document.getElementById('locationModal');
            const authModal = document.getElementById('authRequiredModal');
            if (event.target == locModal) closeModal();
            if (event.target == authModal) hideAuthModal();
        }
    </script>
    @include('partials.add-ad-modal')
    @include('partials.account-type-modal')
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2000,
                customClass: { popup: 'rounded-[32px]' }
            });
        </script>
    @endif

    @if(session('team_member_created'))
        <script>
            (function() {
                const memberName = '{{ session('team_member_created.name') }}';
                const password   = '{{ session('team_member_created.password') }}';

                Swal.fire({
                    icon: 'success',
                    title: 'تم إضافة العضو بنجاح! 🎉',
                    html: `
                        <p class="text-gray-500 font-bold mb-4">تم إنشاء حساب <strong class="text-gray-800">${memberName}</strong> بنجاح.</p>
                        <p class="text-gray-500 font-bold mb-3">كلمة المرور الافتراضية:</p>
                        <div class="flex items-center justify-center gap-3 bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3 mb-2">
                            <span id="swal-password" class="text-gray-800 font-black text-lg tracking-widest">${password}</span>
                            <button onclick="copyPassword()" id="copy-btn" class="flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs px-3 py-1.5 rounded-xl transition-all border border-blue-200" title="نسخ كلمة المرور">
                                <i class="fa-regular fa-copy"></i>
                                <span id="copy-label">نسخ</span>
                            </button>
                        </div>
                        <p class="text-orange-500 text-xs font-bold mt-3">⚠️ يُنصح بإبلاغ العضو بكلمة المرور وتغييرها عند أول تسجيل دخول.</p>
                    `,
                    confirmButtonText: 'حسناً، تم',
                    confirmButtonColor: '#1A73E8',
                    showConfirmButton: true,
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-[32px] p-8',
                        confirmButton: 'rounded-2xl px-10 py-3 font-black text-base',
                        title: 'font-black text-2xl',
                        htmlContainer: 'font-bold'
                    }
                });

                window.copyPassword = function() {
                    navigator.clipboard.writeText(password).then(() => {
                        const label = document.getElementById('copy-label');
                        const btn   = document.getElementById('copy-btn');
                        label.textContent = 'تم النسخ ✓';
                        btn.classList.remove('bg-blue-50', 'text-blue-600', 'border-blue-200');
                        btn.classList.add('bg-green-50', 'text-green-600', 'border-green-200');
                        setTimeout(() => {
                            label.textContent = 'نسخ';
                            btn.classList.remove('bg-green-50', 'text-green-600', 'border-green-200');
                            btn.classList.add('bg-blue-50', 'text-blue-600', 'border-blue-200');
                        }, 2500);
                    });
                };
            })();
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'عذراً',
                text: '{{ session('error') }}',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#1A73E8',
                customClass: { 
                    popup: 'rounded-[32px] p-8',
                    confirmButton: 'rounded-xl px-10 py-3 font-bold',
                    title: 'font-black text-2xl',
                    htmlContainer: 'font-bold text-gray-500'
                }
            });
        </script>
    @endif
    <script>
        function updateCountdowns() {
            const containers = document.querySelectorAll('[data-end]');
            containers.forEach(container => {
                const endDateStr = container.getAttribute('data-end');
                if (!endDateStr) return;
                
                const endDate = new Date(endDateStr).getTime();
                const now = new Date().getTime();
                const diff = endDate - now;
                
                const timerEl = container.querySelector('.countdown-timer');
                if (!timerEl) return;
                
                if (diff <= 0) {
                    timerEl.innerHTML = '<span class="text-red-500 font-bold">منتهي</span>';
                    return;
                }
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                if (container.classList.contains('sidebar-countdown')) {
                    // Update specific spans for sidebar
                    const dEl = timerEl.querySelector('.days');
                    const hEl = timerEl.querySelector('.hours');
                    const mEl = timerEl.querySelector('.minutes');
                    const sEl = timerEl.querySelector('.seconds');
                    
                    if(dEl) dEl.innerText = days.toString().padStart(2, '0');
                    if(hEl) hEl.innerText = hours.toString().padStart(2, '0');
                    if(mEl) mEl.innerText = minutes.toString().padStart(2, '0');
                    if(sEl) sEl.innerText = seconds.toString().padStart(2, '0');
                } else {
                    // Standard text format for cards
                    timerEl.innerText = `${days}ي ${hours}س ${minutes}د ${seconds}ث`;
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();
    </script>
    
    @auth
    <script>
        function checkGlobalChatStatus() {
            fetch('/chats/status')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // 1. Update Navbar Badge
                        const badge = document.getElementById('navChatBadge');
                        if (badge) {
                            if (data.total_unread > 0) {
                                badge.textContent = data.total_unread;
                                badge.style.display = 'flex';
                            } else {
                                badge.style.display = 'none';
                            }
                        }

                        // 2. Update Sidebar Chat items if currently on the chats index page
                        if (window.location.pathname.startsWith('/chats') && data.conversations) {
                            data.conversations.forEach(conv => {
                                const activeItem = document.querySelector(`a[href*="id=${conv.id}"]`);
                                if (activeItem) {
                                    // Update last message preview
                                    const textEl = activeItem.querySelector('p.text-xs');
                                    if (textEl) {
                                        // Update preview text if not already updated
                                        if (textEl.textContent.trim().indexOf('أنت:') === -1) {
                                            textEl.textContent = conv.last_message;
                                        }
                                    }

                                    // Update time
                                    const timeEl = activeItem.querySelector('span.text-\\[10px\\]');
                                    if (timeEl && conv.last_message_time) {
                                        timeEl.textContent = conv.last_message_time;
                                    }

                                    // Update conversation badge
                                    let badgeContainer = activeItem.querySelector('.unread-count-badge');
                                    if (conv.unread_count > 0) {
                                        if (badgeContainer) {
                                            badgeContainer.textContent = conv.unread_count;
                                            badgeContainer.style.display = 'flex';
                                        } else {
                                            // Create badge if not exist
                                            const badgeParent = activeItem.querySelector('.flex-1 .flex.justify-between.items-center:last-child');
                                            if (badgeParent) {
                                                const newBadge = document.createElement('span');
                                                newBadge.className = 'unread-count-badge w-5 h-5 bg-primary text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg shadow-blue-100';
                                                newBadge.textContent = conv.unread_count;
                                                badgeParent.appendChild(newBadge);
                                            }
                                        }
                                    } else {
                                        if (badgeContainer) {
                                            badgeContainer.remove();
                                        }
                                    }
                                }
                            });
                        }
                    }
                })
                .catch(err => console.error('Error checking global chat status:', err));
        }

        // Check chat status every 4 seconds globally
        setInterval(checkGlobalChatStatus, 4000);
        checkGlobalChatStatus();
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
