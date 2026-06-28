@php
    $addr = Auth::check() ? Auth::user()->address : session('user_address');
    $city = null;
    if ($addr) {
        $normalized = str_replace('،', ',', $addr);
        $parts = array_map('trim', explode(',', $normalized));
        $parts = array_filter($parts, function($part) {
            return $part !== 'السعودية' && !is_numeric($part) && !preg_match('/^[0-9]+$/', $part);
        });
        $parts = array_values($parts);
        
        if (count($parts) > 0) {
            if (count($parts) >= 2) {
                $lastPart = $parts[count($parts) - 1];
                $secondLast = $parts[count($parts) - 2];
                $cleanLast = preg_replace('/^(منطقة|محافظة|مدينة|بلدية)\s+/u', '', $lastPart);
                $cleanSecond = preg_replace('/^(منطقة|محافظة|مدينة|بلدية)\s+/u', '', $secondLast);
                
                if (mb_strlen($cleanLast) > 0 && mb_strlen($cleanLast) < 20) {
                    $city = $cleanLast;
                } else {
                    $city = $cleanSecond;
                }
            } else {
                $city = preg_replace('/^(منطقة|محافظة|مدينة|بلدية)\s+/u', '', $parts[0]);
            }
        }
    }
    $displayLocation = $city ?: 'كل المدن (السعودية)';
@endphp
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
            @if(!Auth::check() || Auth::user()->account_type !== 'partner')
            <div class="location-picker lg-location" onclick="handleLocationClick()" style="margin-right: 1.5rem;">
                <i class="fa-solid fa-chevron-down"></i>
                <span id="currentLocationName">
                    {{ $displayLocation }}
                </span>
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg" alt="Saudi Flag" class="flag-icon">
            </div>
            @endif
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
                        @if(Auth::user()->account_type !== 'partner')
                        <a href="{{ route('cart.index') }}" class="nav-icon-link" id="cartBtn">
                            <i class="fa-solid fa-cart-shopping"></i>
                            @php $cartCount = Auth::user()->cartItems->count(); @endphp
                            <span class="nav-badge" id="cartCount" style="display: {{ $cartCount > 0 ? 'flex' : 'none' }}">{{ $cartCount }}</span>
                        </a>
                        @endif
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
                                    <span class="nav-badge notif-badge" id="unreadNotifBadge">{{ $unreadCount }}</span>
                                @endif
                            </a>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notif-header" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px; margin-bottom: 10px;">
                                    <h3 style="margin: 0;">التنبيهات</h3>
                                    @if(Auth::user()->unreadNotifications->count() > 0)
                                        <button onclick="markAllNotificationsRead(event)" class="text-primary hover:underline bg-transparent border-0 cursor-pointer text-xs font-bold" id="markAllReadBtnDropdown" style="font-family: inherit;">تمييز الكل كمقروء</button>
                                    @endif
                                </div>
                                <div class="notif-body">
                                    @forelse(Auth::user()->notifications()->latest()->get() as $notification)
                                        <div class="notification-item {{ !$notification->read_at ? 'unread' : '' }}" id="dropdown-notif-{{ $notification->id }}" onclick="toggleDropdownNotifExpand(this)">
                                            <div class="notif-avatar">
                                                <i class="fa-regular fa-bell"></i>
                                            </div>
                                            <div class="notif-content" style="flex: 1; min-width: 0;">
                                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; width: 100%;">
                                                    <h4 style="margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">{{ $notification->title ?? 'تنبيه جديد' }}</h4>
                                                    <i class="fa-solid fa-chevron-down notif-chevron" style="font-size: 0.75rem; color: #94A3B8; transition: transform 0.2s ease; margin-left: 8px;"></i>
                                                </div>
                                                <p>{{ $notification->content ?? 'لديك إشعار جديد' }}</p>
                                                <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="notif-actions">
                                                @if(!$notification->read_at)
                                                    <button class="notif-action-btn mark-read-btn" onclick="markDropdownNotifRead(event, '{{ $notification->id }}')" title="تحديد كمقروء">
                                                        <i class="fa-regular fa-check-circle"></i>
                                                    </button>
                                                @endif
                                                <button class="notif-action-btn delete-btn" onclick="deleteDropdownNotif(event, '{{ $notification->id }}')" title="حذف الإشعار">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
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
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="user-avatar object-cover border border-slate-100">
                            @else
                                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            @endif
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
    @if(!Auth::check() || Auth::user()->account_type !== 'partner')
    <div class="mobile-location-row" style="display: none;">
        <div class="location-picker" onclick="handleLocationClick()">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg" alt="Saudi Flag" class="flag-icon" style="width: 24px;">
                <span id="currentLocationNameMobile">{{ $displayLocation }}</span>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>
    @endif
</nav>

@push('scripts')
<script>
    async function markDropdownNotifRead(event, id) {
        event.preventDefault();
        event.stopPropagation();
        
        try {
            const response = await fetch(`/notifications/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Update UI: remove unread class
                const item = document.getElementById(`dropdown-notif-${id}`);
                if (item && item.classList.contains('unread')) {
                    item.classList.remove('unread');
                    updateDropdownBadgeCount(-1);
                }
                
                // Remove the mark-as-read button
                const markBtn = item.querySelector('.mark-read-btn');
                if (markBtn) markBtn.remove();

                // Also update the index page card if it exists on screen
                const indexCard = document.getElementById(`notif-${id}`);
                if (indexCard && indexCard.classList.contains('unread')) {
                    indexCard.classList.remove('unread');
                    const indexMarkBtn = indexCard.querySelector('.action-item:not(.delete)');
                    if (indexMarkBtn) indexMarkBtn.remove();
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async function deleteDropdownNotif(event, id) {
        event.preventDefault();
        event.stopPropagation();

        try {
            const response = await fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const item = document.getElementById(`dropdown-notif-${id}`);
                if (item) {
                    const wasUnread = item.classList.contains('unread');
                    
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    
                    if (wasUnread) {
                        updateDropdownBadgeCount(-1);
                    }

                    setTimeout(() => {
                        item.remove();
                        // If no notifications left in dropdown, show empty message
                        const body = document.querySelector('.notification-dropdown .notif-body');
                        if (body && body.querySelectorAll('.notification-item').length === 0) {
                            body.innerHTML = `
                                <div class="empty-notifications">
                                    <i class="fa-regular fa-bell" style="font-size: 3rem; color: #E2E8F0;"></i>
                                    <p>لا توجد تنبيهات حالياً</p>
                                </div>
                            `;
                        }
                    }, 300);
                }

                // Also update the index page card if it exists on screen
                const indexCard = document.getElementById(`notif-${id}`);
                if (indexCard) {
                    indexCard.style.opacity = '0';
                    indexCard.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        indexCard.remove();
                        if (document.querySelectorAll('.notif-card-item').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    }

    async function markAllNotificationsRead(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        try {
            const response = await fetch(`/notifications/mark-all-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Update dropdown items
                document.querySelectorAll('.notification-dropdown .notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    const markBtn = item.querySelector('.mark-read-btn');
                    if (markBtn) markBtn.remove();
                });
                
                // Hide badge
                const badge = document.getElementById('unreadNotifBadge');
                if (badge) {
                    badge.style.display = 'none';
                    badge.textContent = '0';
                }
                
                // Hide dropdown mark all read button
                const btnDropdown = document.getElementById('markAllReadBtnDropdown');
                if (btnDropdown) btnDropdown.remove();

                // If on notification index page, update the UI there as well
                const btnIndex = document.getElementById('markAllReadBtnIndex');
                if (btnIndex) btnIndex.remove();

                document.querySelectorAll('.notif-card-item.unread').forEach(card => {
                    card.classList.remove('unread');
                    const dropdown = document.getElementById(`dropdown-${card.id.replace('notif-', '')}`);
                    if (dropdown) {
                        const markBtn = dropdown.querySelector('.action-item:not(.delete)');
                        if (markBtn) markBtn.remove();
                    }
                });
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    function updateDropdownBadgeCount(change) {
        const badge = document.getElementById('unreadNotifBadge');
        if (badge) {
            let currentCount = parseInt(badge.textContent) || 0;
            let newCount = currentCount + change;
            
            if (newCount <= 0) {
                badge.style.display = 'none';
                badge.textContent = '0';
            } else {
                badge.style.display = 'flex';
                badge.textContent = newCount;
            }
        }
    }

    function toggleDropdownNotifExpand(element) {
        element.classList.toggle('expanded');
    }
</script>
@endpush

<style>
    /* Collapse/Expand states for dropdown notification body */
    .notification-dropdown .notification-item .notif-content p,
    .notification-dropdown .notification-item .notif-content .notif-time {
        max-height: 0 !important;
        opacity: 0 !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin 0.3s ease;
    }

    .notification-dropdown .notification-item.expanded .notif-content p {
        max-height: 200px !important;
        opacity: 1 !important;
        margin-top: 0.5rem !important;
    }

    .notification-dropdown .notification-item.expanded .notif-content .notif-time {
        max-height: 50px !important;
        opacity: 1 !important;
        margin-top: 0.5rem !important;
    }

    .notification-dropdown .notification-item.expanded .notif-chevron {
        transform: rotate(180deg) !important;
    }
</style>
