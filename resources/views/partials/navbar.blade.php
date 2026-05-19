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

        <!-- Middle: Spacer -->
        <div class="nav-center-visual">
        </div>

        <!-- Left Side: Actions -->
        <div class="nav-left-visual">
            <div class="nav-common-icons" style="display: flex; align-items: center; gap: 0.5rem; margin-left: 1rem;">
                <!-- Search Container -->
                <div class="search-container" style="display: flex; align-items: center; gap: 0.5rem; position: relative;">
                    <form action="{{ route('search') }}" method="GET" id="navSearchForm">
                        <input type="text" name="q" id="navbarSearchInput" placeholder="ابحث هنا..." style="width: 0; opacity: 0; transition: all 0.3s ease; border: 1px solid #E2E8F0; border-radius: 12px; padding: 0.5rem 0; outline: none; background: #F8FAFC; font-size: 0.95rem;" onkeypress="if(event.key === 'Enter') this.form.submit();">
                    </form>
                    
                    <!-- Search Icon: Link on Mobile, JS on Desktop -->
                    <a href="{{ route('search') }}" class="nav-icon-link block lg:hidden">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="toggleNavbarSearch()" class="nav-icon-link hidden lg:flex" id="searchBtn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>

                    <a href="javascript:void(0)" onclick="toggleNavbarSearch()" class="close-search" style="display: none; padding: 0.5rem; color: #EF4444;">
                        <i class="fa-solid fa-xmark" style="font-size: 1.25rem;"></i>
                    </a>
                </div>
            </div>

            @auth
                <div class="auth-actions" style="display: flex; align-items: center; gap: 1.25rem;">
                    <!-- Icons section -->
                    <div class="nav-icons" style="display: flex; align-items: center; gap: 0.2rem;">
                        <a href="{{ route('cart.index') }}" class="nav-icon-link">
                            <i class="fa-solid fa-cart-shopping"></i>
                            @php $cartCount = Auth::user()->cartItems->count(); @endphp
                            @if($cartCount > 0)
                                <span class="nav-badge">{{ $cartCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('chats.index') }}" class="nav-icon-link">
                            <i class="fa-regular fa-comment-dots"></i>
                            @php $unreadMessages = Auth::user()->unreadMessagesCount(); @endphp
                            @if($unreadMessages > 0)
                                <span class="nav-badge">{{ $unreadMessages }}</span>
                            @endif
                        </a>
                        <div class="notification-wrapper" style="position: relative;">
                            <a href="javascript:void(0)" onclick="toggleNotifications()" class="nav-icon-link" id="notificationBtn">
                                <i class="fa-regular fa-bell"></i>
                                @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                                @if($unreadCount > 0)
                                    <span class="nav-badge">{{ $unreadCount }}</span>
                                @endif
                            </a>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notif-header">
                                    <h3>التنبيهات</h3>
                                </div>
                                <div class="notif-body">
                                    @forelse(Auth::user()->notifications as $notification)
                                        <a href="#" class="notification-item {{ !$notification->read_at ? 'unread' : '' }}">
                                            <div class="notif-avatar">
                                                <i class="fa-regular fa-bell"></i>
                                            </div>
                                            <div class="notif-content">
                                                <h4>{{ $notification->title ?? 'تنبيه جديد' }}</h4>
                                                <p>{{ $notification->content ?? 'لديك إشعار جديد' }}</p>
                                                <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="empty-notifications">
                                            <i class="fa-regular fa-bell" style="font-size: 3rem; color: #E2E8F0;"></i>
                                            <p>لا توجد تنبيهات حالياً</p>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="notif-footer">
                                    <a href="{{ route('notifications.index') }}">عرض كل التنبيهات</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile -->
                    <div class="profile-wrapper" style="position: relative;">
                        <div class="user-profile" id="userProfileBtn" onclick="toggleSidebar()">
                            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            <span class="user-greeting hidden sm:block">مرحباً، {{ explode(' ', Auth::user()->name)[0] }}</span>
                        </div>
                    </div>

                    <!-- Add Ad -->
                    <div class="add-ad-wrapper">
                        <a href="javascript:void(0)" onclick="openAddAdModal()" class="btn btn-primary add-ad-btn">أضف إعلانك</a>
                    </div>
                </div>
            @else
                <div class="guest-actions" style="display: flex; align-items: center; gap: 1rem;">
                    <a href="javascript:void(0)" onclick="openAccountModal()" class="btn btn-outline">تسجيل الدخول</a>
                    <a href="javascript:void(0)" onclick="openAddAdModal()" class="btn btn-primary add-ad-btn">أضف إعلانك</a>
                </div>
            @endauth
        </div>
    </div>

    <!-- Mobile Only Location Row -->
    <div class="mobile-location-row" style="display: none;">
        <div class="location-picker" onclick="handleLocationClick()">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg" alt="Saudi Flag" class="flag-icon" style="width: 24px;">
                <span id="currentLocationNameMobile">كل المدن (السعودية)</span>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>
</nav>
