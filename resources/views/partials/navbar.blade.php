<nav class="navbar">
    <div class="container">
        <!-- Right Side: Burger + Logo + Location (RTL Start) -->
        <div class="nav-right-visual">
            @auth
            <button class="burger-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
            @endauth
            <div class="logo">
                <a href="/" style="text-decoration: none;">
                    @include('partials.logo', ['size' => 'text-3xl'])
                </a>
            </div>
            <div class="location-picker lg-location" onclick="handleLocationClick()" style="margin-right: 1.5rem;">
                <i class="fa-solid fa-chevron-down"></i>
                <span id="currentLocationName">
                    @auth
                        @php
                            $addr = Auth::user()->address;
                            $city = $addr;
                            if ($addr && strpos($addr, '،') !== false) {
                                $parts = explode('،', $addr);
                                $city = (count($parts) >= 3) ? trim($parts[count($parts) - 3]) : trim($parts[0]);
                            }
                        @endphp
                        {{ $city ?: 'كل المدن (السعودية)' }}
                    @else
                        كل المدن (السعودية)
                    @endauth
                </span>
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg" alt="Saudi Flag" class="flag-icon">
            </div>
        </div>

        <!-- Mobile Only Location Row -->
        <div class="mobile-location-row" style="display: none;">
            <div class="location-picker" onclick="handleLocationClick()">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg" alt="Saudi Flag" class="flag-icon">
                    <span id="currentLocationNameMobile">
                        @auth
                            @php
                                $addr = Auth::user()->address;
                                $city = $addr;
                                if ($addr && strpos($addr, '،') !== false) {
                                    $parts = explode('،', $addr);
                                    $city = (count($parts) >= 3) ? trim($parts[count($parts) - 3]) : trim($parts[0]);
                                }
                            @endphp
                            {{ $city ?: 'كل المدن (السعودية)' }}
                        @else
                            كل المدن (السعودية)
                        @endauth
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <!-- Middle: Empty or Search (HTML Middle) -->
        <div class="nav-center-visual">
            {{-- Spacer or search bar can go here --}}
        </div>

        <!-- Visual Left: User Actions (HTML Last) -->
        <div class="nav-left-visual">
            <div class="nav-common-icons" style="display: flex; align-items: center; gap: 0.5rem; margin-left: 1rem;">
                <div class="search-container" style="display: flex; align-items: center; gap: 0.5rem; position: relative;">
                    <form action="{{ route('search') }}" method="GET" id="navSearchForm">
                        <input type="text" name="q" id="navbarSearchInput" placeholder="ابحث هنا..." style="width: 0; opacity: 0; transition: all 0.3s ease; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.4rem 0; outline: none; background: #F8FAFC; font-size: 0.9rem;" onkeypress="if(event.key === 'Enter') this.form.submit();">
                    </form>
                    <a href="javascript:void(0)" onclick="toggleNavbarSearch()" class="nav-icon-link" id="searchBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#403B6D" class="bi bi-search" viewBox="0 0 18 18">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </a>
                </div>
            </div>
            @auth
                <div class="auth-actions" style="display: flex; align-items: center; gap: 1.25rem;">
                    <!-- Icons section (RTL: Search is first/rightmost in this group) -->
                    <div class="nav-icons" style="display: flex; align-items: center; gap: 0.2rem;">
                        <a href="#" class="nav-icon-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#403B6D" class="bi bi-chat-dots" viewBox="0 0 18 18">
                        <path d="M5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                        <path d="m2.165 15.803.02-.004c1.83-.363 2.948-.842 3.468-1.105A9 9 0 0 0 8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6a10.4 10.4 0 0 1-.524 2.318l-.003.011a11 11 0 0 1-.244.637c-.079.186.074.394.273.362a22 22 0 0 0 .693-.125m.8-3.108a1 1 0 0 0-.287-.801C1.618 10.83 1 9.468 1 8c0-3.192 3.004-6 7-6s7 2.808 7 6-3.004 6-7 6a8 8 0 0 1-2.088-.272 1 1 0 0 0-.711.074c-.387.196-1.24.57-2.634.893a11 11 0 0 0 .398-2"/>
                        </svg></a>
                        <div class="cart-wrapper" style="position: relative;">
                            <a href="javascript:void(0)" onclick="toggleCart()" class="nav-icon-link" id="cartBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#403B6D" class="bi bi-cart3" viewBox="0 0 18 18">
                                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                                </svg>
                            </a>
                            <div id="cartDropdown" class="notification-dropdown cart-dropdown">
                                <div class="notif-header">
                                    <h3>السلة</h3>
                                </div>
                                <div class="notif-body">
                                    <div class="empty-cart-container">
                                        <div class="empty-cart-icon">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                            <div class="wind-lines">
                                                <span></span><span></span><span></span>
                                            </div>
                                        </div>
                                        <p>سلة التسوق فارغة</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="notification-wrapper" style="position: relative;">
                            <a href="javascript:void(0)" onclick="toggleNotifications()" class="nav-icon-link" id="notificationBtn" style="position: relative;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#403B6D" class="bi bi-bell" viewBox="0 0 18 18">
                                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                                </svg>
                                @auth
                                    @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                                    @if($unreadCount > 0)
                                        <span class="nav-badge">{{ $unreadCount }}</span>
                                    @endif
                                @endauth
                            </a>
                            
                            <!-- Notifications Dropdown -->
                            <div id="notificationDropdown" class="notification-dropdown">
                                <div class="notif-header">
                                    <h3>إشعارات</h3>
                                </div>
                                <div class="notif-body">
                                    @auth
                                        @php
                                            $notifs = Auth::user()->notifications()->latest()->take(5)->get();
                                        @endphp

                                        @forelse($notifs as $notif)
                                            <div class="notification-item {{ is_null($notif->read_at) ? 'unread' : '' }}">
                                                <div class="notif-avatar">
                                                    <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                                </div>
                                                <div class="notif-content">
                                                    <h4>{{ $notif->title }}</h4>
                                                    <p>{{ $notif->content }}</p>
                                                    <span class="notif-time">{{ $notif->created_at->locale('ar')->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="empty-notifications">
                                                <div class="bell-icon-container">
                                                    <div class="bell-bg">
                                                        <i class="fa-solid fa-bell"></i>
                                                        <span class="bell-badge">0</span>
                                                    </div>
                                                    <div class="bell-rays">
                                                        <span></span><span></span><span></span>
                                                    </div>
                                                </div>
                                                <p>لا توجد إشعارات حالياً</p>
                                            </div>
                                        @endforelse
                                    @else
                                        <div class="empty-notifications">
                                            <div class="bell-icon-container">
                                                <div class="bell-bg">
                                                    <i class="fa-solid fa-bell"></i>
                                                    <span class="bell-badge">0</span>
                                                </div>
                                                <div class="bell-rays">
                                                    <span></span><span></span><span></span>
                                                </div>
                                            </div>
                                            <p>لا توجد إشعارات حالياً</p>
                                        </div>
                                    @endauth
                                </div>
                                @auth
                                    <div class="notif-footer">
                                        <a href="{{ route('notifications.index') }}">عرض الكل</a>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Section -->
                    <div class="profile-wrapper">
                        <div class="user-profile" id="userProfileBtn" onclick="toggleProfileDropdown()">
                            <div class="user-avatar">
                                <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                            <span class="user-greeting">مرحباً، {{ explode(' ', Auth::user()->name)[0] }}</span>
                        </div>

                        <!-- Profile Dropdown -->
                        <div id="profileDropdown" class="profile-dropdown">
                            <div class="profile-header">
                                <div class="profile-info">
                                    <h3>{{ Auth::user()->name }}</h3>
                                    <p>
                                        @if(Auth::user()->account_type == 'individual') فرد
                                        @elseif(Auth::user()->account_type == 'company') شركة
                                        @elseif(Auth::user()->account_type == 'partner') مندوب
                                        @else مستخدم
                                        @endif
                                    </p>
                                </div>
                                <div class="profile-avatar-lg">
                                    <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="profile-divider"></div>
                            <div class="profile-menu">
                                @if(Auth::user()->email === 'admin@mazad.com')
                                    <a href="{{ route('admin.dashboard') }}" class="profile-menu-item" style="color: var(--primary-color); font-weight: 800;">
                                        <span>لوحة التحكم</span>
                                    </a>
                                    <div class="profile-divider"></div>
                                @endif
                                <a href="#" class="profile-menu-item">
                                    <span>ملفي الشخصي</span>
                                </a>
                                <a href="#" class="profile-menu-item">
                                    <span>مفضلتي</span>
                                </a>
                                <a href="#" class="profile-menu-item">
                                    <span>إعلاناتي</span>
                                </a>
                                <div class="profile-divider" style="margin-top: 0.5rem;"></div>
                                <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" style="display: none;">
                                    @csrf
                                </form>
                                <a href="#" class="profile-menu-item logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <span>تسجيل الخروج</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Leftmost in this section (far left of screen) -->
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary add-ad-btn">أضف إعلانك</a>
                </div>
            @else
                <div class="guest-actions" style="display: flex; align-items: center; gap: 1rem;">
                    <a href="#" onclick="openAccountModal(); return false;" class="btn btn-outline">تسجيل الدخول</a>
                    <a href="#" onclick="openAccountModal(); return false;" class="btn btn-primary add-ad-btn">أضف إعلانك</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
