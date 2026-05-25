<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .fa-heart.fa-solid { color: #EF4444 !important; }
    </style>
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
                }
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();
    </script>
    @stack('scripts')
</body>
</html>
