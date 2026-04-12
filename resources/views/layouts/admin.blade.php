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
        /* Custom scrollbar for sidebar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
        
        /* Mobile Sidebar Fixes */
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

    <div class="flex h-screen w-full relative">
        
        <!-- Desktop Sidebar (Persistent on lg screens) -->
        <aside class="desktop-only w-[280px] bg-white border-l border-slate-200 shadow-[0_0_15px_rgba(0,0,0,0.02)] flex flex-col h-full flex-shrink-0 z-40 overflow-y-auto" style="display: flex;">
            <!-- Logo Area -->
            <div class="h-28 flex items-center justify-center border-b border-transparent px-6 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="text-[3.5rem] font-black tracking-tight text-[#1C1C1C] hover:text-[#1A73E8] transition-colors leading-none">
                    Logo
                </a>
            </div>
            
            <!-- Navigation Links (Standard Sidebar Content) -->
            @include('layouts.partials.admin-sidebar-nav')

            <!-- Logout Form -->
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

        <!-- Mobile Sidebar (Fixed Overlay on < lg screens) -->
        <aside :class="mobileMenuOpen ? 'sidebar-open' : 'sidebar-closed'"
               class="mobile-only fixed right-0 top-0 w-[280px] bg-white border-l border-slate-200 shadow-xl flex flex-col h-full z-50 transition-all duration-300 overflow-y-auto">
            
            <!-- Logo & Close Button Area -->
            <div class="h-28 flex items-center justify-between px-6 shrink-0 border-b border-transparent">
                <a href="{{ route('admin.dashboard') }}" class="text-[3.5rem] font-black tracking-tight text-[#1C1C1C] hover:text-[#1A73E8] transition-colors leading-none">
                    Logo
                </a>
                <button @click="mobileMenuOpen = false" class="p-2 text-slate-400 hover:text-slate-600 focus:outline-none bg-slate-50 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            @include('layouts.partials.admin-sidebar-nav')

            <!-- Logout Form -->
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
                
                <!-- Hamburger Menu (Mobile Only) -->
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
                    <button class="relative bg-white border border-slate-200 rounded-full flex items-center justify-center text-[#6F6F6F] hover:text-[#1A73E8] hover:border-[#1A73E8] transition-all focus:outline-none" style="width: 44px; height: 44px; min-width: 44px;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-2.5 right-3 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- User Profile -->
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 sm:gap-3 focus:outline-none group">
                            <div class="text-left desktop-only" style="display: block;">
                                <p class="text-sm font-bold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors" style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name ?? 'أحمد محمد' }}</p>
                                <p class="text-xs font-semibold text-[#6F6F6F] text-left">أدمن</p>
                            </div>
                            <div class="rounded-full bg-[#1A73E8] text-white flex items-center justify-center font-black text-base shadow-lg shadow-blue-500/30" style="width: 44px; height: 44px; min-width: 44px;">
                                {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                            </div>
                        </button>
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition.opacity class="absolute left-0 mt-3 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-2 z-50" x-cloak>
                            <a href="#" class="block px-4 py-2 text-sm font-semibold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">الملف الشخصي</a>
                            <a href="#" class="block px-4 py-2 text-sm font-semibold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">الإعدادات</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Inner Canvas Content Page -->
            <div class="flex-1 p-4 sm:p-8 lg:p-12 pt-4 sm:pt-0 pb-20">
                <!-- Page Content -->
                @yield('content')
            </div>
            
        </main>
    </div>

    @stack('scripts')
</body>
</html>
