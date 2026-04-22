<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم - مزاد')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
        
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        
        @media (max-width: 1023px) {
            .sidebar-closed { right: -280px !important; }
            .sidebar-open { right: 0px !important; }
            .desktop-only { display: none !important; }
        }
        @media (min-width: 1024px) {
            .mobile-only { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-[#F7F7F7] text-[#1C1C1C] overflow-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Mobile Overlay -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden" x-cloak>
    </div>

    <div class="flex h-screen w-full relative text-right" dir="rtl">
        
        <!-- Desktop Sidebar -->
        <aside class="desktop-only w-[280px] bg-white border-l border-slate-200 shadow-[0_0_15px_rgba(0,0,0,0.02)] flex flex-col h-full flex-shrink-0 z-40 overflow-y-auto" style="display: flex;">
            <div class="h-28 flex items-center justify-center border-b border-transparent px-6 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="text-[3.5rem] font-black tracking-tight text-[#1C1C1C] hover:text-[#1A73E8] transition-colors leading-none">
                    Logo
                </a>
            </div>
            @include('layouts.partials.admin-sidebar-nav')
            <div class="p-6 mt-auto">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <aside :class="mobileMenuOpen ? 'sidebar-open' : 'sidebar-closed'"
               class="mobile-only fixed right-0 top-0 w-[280px] bg-white border-l border-slate-200 shadow-xl flex flex-col h-full z-50 transition-all duration-300 overflow-y-auto">
            <div class="h-28 flex items-center justify-between px-6 shrink-0 border-b border-transparent">
                <a href="{{ route('admin.dashboard') }}" class="text-[3.5rem] font-black tracking-tight text-[#1C1C1C] hover:text-[#1A73E8] transition-colors leading-none">
                    Logo
                </a>
                <button @click="mobileMenuOpen = false" class="p-2 text-slate-400 hover:text-slate-600 focus:outline-none bg-slate-50 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            @include('layouts.partials.admin-sidebar-nav')
            <div class="p-6 mt-auto">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto relative bg-[#F7F7F7]">
            
            <!-- Top Header -->
            <header class="bg-white h-24 flex items-center justify-between px-4 sm:px-8 lg:px-12 shrink-0 z-30 sticky top-0 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] border-b border-slate-100">
                
                <!-- Hamburger Menu -->
                <button @click="mobileMenuOpen = true" class="mobile-only p-2.5 ml-3 bg-slate-50 text-slate-600 rounded-xl hover:bg-slate-100 focus:outline-none border border-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>

                <!-- Welcome Title -->
                <div class="flex-1">
                    <h1 class="text-xl sm:text-[1.75rem] font-black text-[#1C1C1C] mb-0 sm:mb-1 tracking-tight truncate">مرحباً، {{ auth()->user()->name ?? 'أحمد' }}</h1>
                    <p class="text-[10px] sm:text-xs md:text-sm font-semibold text-[#6F6F6F] hidden sm:block">يمكنك هنا متابعة أداء المنصة وإدارة المستخدمين بسهولة.</p>
                </div>

                <!-- Profile Components -->
                <div class="flex items-center gap-3 sm:gap-6">
                    <!-- Notification Bell -->
                    <div x-data='{ 
                        notificationOpen: false,
                        limit: 4,
                        notifications: {!! auth()->user()->notifications()->latest()->limit(50)->get()->map(fn($n) => [
                            'id' => $n->id,
                            'title' => $n->title,
                            'desc' => $n->content,
                            'time' => $n->created_at->diffForHumans(),
                            'unread' => is_null($n->read_at),
                            'link' => str_contains($n->title, 'تذكرة') || str_contains($n->title, 'استفسار') 
                                ? route('admin.tickets.index') 
                                : (str_contains($n->title, 'مالي') || str_contains($n->title, 'رصيد') 
                                    ? route('admin.payments.index') 
                                    : route('admin.notifications.index'))
                        ])->toJson() !!},
                        get unreadCount() {
                            return this.notifications.filter(n => n.unread).length;
                        },
                        async markAsRead(id) {
                            let n = this.notifications.find(notif => notif.id === id);
                            if(n && n.unread) {
                                try {
                                    const response = await fetch(`/admin/notifications/${id}/mark-read`, {
                                        method: "POST",
                                        headers: {
                                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                            "Accept": "application/json"
                                        }
                                    });
                                    if(response.ok) {
                                        n.unread = false;
                                    }
                                } catch(e) { console.error("Error marking read", e); }
                            }
                        },
                        async markAllAsRead() {
                            try {
                                const response = await fetch("{{ route('admin.notifications.mark-all-read') }}", {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    }
                                });
                                if(response.ok) {
                                    this.notifications.forEach(n => n.unread = false);
                                }
                            } catch(e) { console.error("Error marking all as read", e); }
                        },
                        showMore() {
                            this.limit += 4;
                        }
                    }' class="relative">
                        <button @click="notificationOpen = !notificationOpen" class="relative bg-white border border-slate-200 rounded-full flex items-center justify-center text-[#6F6F6F] hover:text-[#1A73E8] hover:border-[#1A73E8] transition-all focus:outline-none shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute top-0 -right-1 min-w-[20px] h-5 bg-[#EF4444] text-white text-[10px] font-black rounded-full border-2 border-white flex items-center justify-center px-1"></span>
                        </button>

                        <!-- Notification Popup -->
                        <div x-show="notificationOpen" @click.away="notificationOpen = false" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 translate-y-1 scale-95" 
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute left-0 mt-3 w-[400px] bg-white border border-slate-100 rounded-[28px] shadow-2xl z-50 overflow-hidden" x-cloak>
                            
                            <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                                <button @click="markAllAsRead()" class="text-xs font-black text-[#1A73E8] hover:underline transition-all">تمييز الكل كمقروء</button>
                                <h3 class="text-[17px] font-black text-[#1C1C1C]">الإشعارات</h3>
                            </div>

                            <!-- List Container (Forced Fixed Height with Scroll) -->
                            <div class="h-[300px] overflow-y-auto" style="overflow-y: auto !important; height: 300px !important;">
                                <template x-for="notif in notifications.slice(0, limit)" :key="notif.id">
                                    <div @click="window.location.href = notif.link" 
                                         class="relative group border-b border-slate-50 transition-all hover:bg-slate-50 cursor-pointer overflow-hidden" 
                                         :class="notif.unread ? 'bg-blue-50/40' : 'bg-white'">
                                        
                                        <div class="p-4 flex items-start gap-4">
                                            <!-- Avatar & Unread Indicator -->
                                            <div class="relative flex-shrink-0">
                                                <div class="w-11 h-11 rounded-full flex items-center justify-center font-black transition-colors"
                                                     :class="notif.unread ? 'bg-[#1A73E8] text-white' : 'bg-slate-100 text-slate-400'">
                                                    أ
                                                </div>
                                                <div x-show="notif.unread" class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-white rounded-full"></div>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 text-right">
                                                <div class="flex items-center justify-between mb-1 gap-2">
                                                    <h4 class="text-[14px] font-black text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors" x-text="notif.title"></h4>
                                                    <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap" x-text="notif.time"></span>
                                                </div>
                                                <p class="text-[11px] font-bold leading-relaxed text-slate-500 mb-2 truncate" x-text="notif.desc"></p>
                                                
                                                <!-- Action: Mark as Read (Individual) -->
                                                <div x-show="notif.unread" class="flex justify-start">
                                                    <button @click.stop="markAsRead(notif.id)" class="text-[10px] font-black text-[#1A73E8] bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-[#1A73E8] hover:text-white transition-all">
                                                        تمييز كمقروء
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="p-5 text-center bg-slate-50 border-t border-slate-100" x-show="limit < notifications.length">
                                <button @click="limit += 4" class="text-[14px] font-black text-[#1A73E8] hover:text-blue-700 transition-colors">
                                    عرض المزيد
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 sm:gap-3 focus:outline-none group">
                            <div class="text-left desktop-only" style="display: block;">
                                <p class="text-sm font-bold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors" style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name ?? 'أحمد محمد' }}</p>
                                <p class="text-xs font-semibold text-[#6F6F6F] text-left">أدمن</p>
                            </div>
                            <div class="rounded-full bg-[#1A73E8] text-white flex items-center justify-center font-black text-base shadow-lg shadow-blue-500/30 overflow-hidden" style="width: 44px; height: 44px; min-width: 44px;">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset(auth()->user()->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                                @endif
                            </div>
                        </button>
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition.opacity class="absolute left-0 mt-3 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-2 z-50" x-cloak>
                            <a href="{{ route('admin.profile.index') }}" class="block px-4 py-2 text-sm font-semibold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">الملف الشخصي</a>
                            <a href="#" class="block px-4 py-2 text-sm font-semibold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">الإعدادات</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Inner Canvas Content Page -->
            <div class="flex-1 p-4 sm:p-8 lg:p-12 pt-4 sm:pt-0 pb-20">
                @yield('content')
            </div>
            
        </main>
    </div>

    @stack('scripts')
</body>
</html>
