@auth
<!-- Sidebar Drawer -->
<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div id="sidebarDrawer" class="sidebar-drawer">
    <div class="sidebar-header">
        <div class="sidebar-title">
            <i class="fa-solid fa-bars-staggered"></i>
            <span>القائمة</span>
        </div>
        <button class="sidebar-close" onclick="toggleSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <!-- Account Info Section -->
        <div class="sidebar-section">
            @if(Auth::user()->email === 'admin@mazad.com')
                <h4 class="section-title">الإدارة</h4>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link" style="color: var(--primary-color); font-weight: 800;">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>لوحة التحكم</span>
                </a>
                <div class="sidebar-divider"></div>
            @endif
            <h4 class="section-title">معلومات الحساب</h4>
            <a href="{{ route('profile.index') }}" class="sidebar-link">
                <i class="fa-regular fa-user"></i>
                <span>ملفي الشخصي</span>
            </a>
            <a href="{{ route('profile.interests.index') }}" class="sidebar-link">
                <i class="fa-solid fa-shapes"></i>
                <span>{{ Auth::user()->account_type === 'partner' ? 'الفئات' : 'اهتماماتي' }}</span>
            </a>
            @auth
                @if(Auth::user()->account_type === 'company')
                    <a href="{{ route('team.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-users"></i>
                        <span>أعضاء الفريق</span>
                    </a>
                @endif
            @endauth
            @if(Auth::user()->account_type !== 'partner')
            <a href="{{ route('chats.index') }}" class="sidebar-link">
                <i class="fa-regular fa-comment-dots"></i>
                <span>الدردشات</span>
                @php $unreadMessages = Auth::user()->unreadMessagesCount(); @endphp
                @if($unreadMessages > 0)
                    <span class="badge" style="background: #EF4444; color: white; border-radius: 50%; padding: 0.1rem 0.4rem; font-size: 0.7rem; margin-right: auto;">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="{{ route('profile.notifications.index') }}" class="sidebar-link">
                <i class="fa-regular fa-bell"></i>
                <span>إعدادات الإشعارات</span>
            </a>
            @endif
        </div>

        <div class="sidebar-divider"></div>

        <!-- Ads Section -->
        <div class="sidebar-section">
            @if(Auth::user()->account_type === 'partner')
                <h4 class="section-title">لوحة الخدمات</h4>
                <a href="{{ route('ads.my') }}" class="sidebar-link">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span>لوحة خدماتي</span>
                </a>
                <a href="{{ route('partner.service-orders') }}" class="sidebar-link">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>طلبات الخدمات الواردة</span>
                </a>
            @else
                <h4 class="section-title">الاعلانات</h4>
                <a href="{{ route('ads.my') }}" class="sidebar-link">
                    <i class="fa-regular fa-rectangle-list"></i>
                    <span>إعلاناتي</span>
                </a>
                <a href="{{ route('wishlist.index') }}" class="sidebar-link">
                    <i class="fa-regular fa-heart"></i>
                    <span>مفضلتي</span>
                </a>
                <a href="{{ route('look-later.index') }}" class="sidebar-link">
                    <i class="fa-regular fa-bookmark"></i>
                    <span>منتجات للاطلاع لاحقاً</span>
                </a>
                <a href="{{ route('reviews.my') }}" class="sidebar-link">
                    <i class="fa-regular fa-star"></i>
                    <span>تقييماتي</span>
                </a>
                <a href="{{ route('sales.my') }}" class="sidebar-link">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>مبيعاتي</span>
                </a>
            @endif
            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <i class="fa-solid fa-chart-column"></i>
                <span>تقاريري</span>
            </a>
            @if(Auth::user()->account_type !== 'partner')
            <a href="{{ route('purchases.my') }}" class="sidebar-link">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>مشترياتي</span>
            </a>
            <a href="{{ route('bids-offers.index') }}" class="sidebar-link">
                <i class="fa-solid fa-gavel"></i>
                <span>للمزادات والعروض</span>
            </a>
            @endif
        </div>

        <div class="sidebar-divider"></div>

        <!-- Support Section -->
        <div class="sidebar-section">
            <h4 class="section-title">الدعم والمساعدة</h4>
            <a href="{{ route('faqs') }}" class="sidebar-link">
                <i class="fa-regular fa-circle-question"></i>
                <span>الأسئلة الشائعة</span>
            </a>
            <a href="{{ route('terms') }}" class="sidebar-link">
                <i class="fa-regular fa-file-lines"></i>
                <span>الشروط و الاحكام</span>
            </a>
            <a href="{{ route('privacy') }}" class="sidebar-link">
                <i class="fa-regular fa-shield"></i>
                <span>سياسة الخصوصية</span>
            </a>
            <a href="{{ route('contact') }}" class="sidebar-link">
                <i class="fa-solid fa-headset"></i>
                <span>الدعم و المساعدة</span>
            </a>
            <a href="{{ route('inquiries.my') }}" class="sidebar-link">
                <i class="fa-regular fa-message"></i>
                <span>استفساراتي</span>
            </a>
        </div>

        <div class="sidebar-divider"></div>

        <!-- Logout Section -->
        <div class="sidebar-section">
            @auth
                <a href="#" class="sidebar-link logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>تسجيل الخروج</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @else
                <a href="#" onclick="openAccountModal(); return false;" class="sidebar-link">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>تسجيل الدخول</span>
                </a>
            @endauth
        </div>
    </div>
</div>
@endauth
