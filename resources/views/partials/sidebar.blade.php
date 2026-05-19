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
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-user"></i>
                <span>ملفي الشخصي</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-shapes"></i>
                <span>اهتماماتي</span>
            </a>
            @auth
                @if(Auth::user()->account_type === 'company')
                    <a href="#" class="sidebar-link">
                        <i class="fa-solid fa-users"></i>
                        <span>أعضاء الفريق</span>
                    </a>
                @endif
            @endauth
            <a href="{{ route('chats.index') }}" class="sidebar-link">
                <i class="fa-regular fa-comment-dots"></i>
                <span>الدردشات</span>
                @php $unreadMessages = Auth::user()->unreadMessagesCount(); @endphp
                @if($unreadMessages > 0)
                    <span class="badge" style="background: #EF4444; color: white; border-radius: 50%; padding: 0.1rem 0.4rem; font-size: 0.7rem; margin-right: auto;">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-bell"></i>
                <span>إعدادات الإشعارات</span>
            </a>
        </div>

        <div class="sidebar-divider"></div>

        <!-- Ads Section -->
        <div class="sidebar-section">
            <h4 class="section-title">الاعلانات</h4>
            <a href="{{ route('ads.my') }}" class="sidebar-link">
                <i class="fa-regular fa-rectangle-list"></i>
                <span>إعلاناتي</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-heart"></i>
                <span>مفضلتي</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-star"></i>
                <span>تقييماتي</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-calendar-check"></i>
                <span>مبيعاتي</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-chart-column"></i>
                <span>تقاريري</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>مشترياتي</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-bookmark"></i>
                <span>منتجات للاطلاع لاحقاً</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-gavel"></i>
                <span>للمزادات والعروض</span>
            </a>
        </div>

        <div class="sidebar-divider"></div>

        <!-- Support Section -->
        <div class="sidebar-section">
            <h4 class="section-title">الدعم والمساعدة</h4>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-circle-question"></i>
                <span>الأسئلة الشائعة</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-file-lines"></i>
                <span>الشروط و الاحكام</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-shield"></i>
                <span>سياسة الخصوصية</span>
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-headset"></i>
                <span>الدعم و المساعدة</span>
            </a>
            <a href="#" class="sidebar-link">
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
