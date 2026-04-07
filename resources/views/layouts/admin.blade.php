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
    </style>
    @stack('styles')
</head>
<body class="bg-[#F7F7F7] text-[#1C1C1C] overflow-hidden">

    <div class="flex h-screen w-full">
        
        <!-- Sidebar -->
        <aside class="w-[280px] bg-white border-l border-slate-200 shadow-[0_0_15px_rgba(0,0,0,0.02)] flex flex-col h-full flex-shrink-0 z-40 transition-all duration-300 overflow-y-auto">
            
            <!-- Logo Area -->
            <div class="h-28 flex items-center justify-center border-b border-transparent px-6 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="text-[3.5rem] font-black tracking-tight text-[#1C1C1C] hover:text-[#1A73E8] transition-colors leading-none">
                    Logo
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-5 py-6 space-y-1.5 overflow-hidden font-bold text-[15px]">
                
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3.5 {{ request()->routeIs('admin.dashboard') ? 'bg-[#E8F0FE] text-[#1A73E8]' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    لوحة التحكم
                </a>

                <!-- Accounts Management -->
                <div x-data="{ open: {{ request()->routeIs('admin.accounts.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.accounts.*') ? 'text-[#1C1C1C] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            إدارة الحسابات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.accounts.individuals') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts.individuals') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الأفراد</a>
                        <a href="#" class="block px-4 py-2.5 rounded-xl transition-colors hover:text-[#1A73E8] hover:bg-slate-50">الشركات</a>
                        <a href="#" class="block px-4 py-2.5 rounded-xl transition-colors hover:text-[#1A73E8] hover:bg-slate-50">المندوبين</a>
                        <a href="#" class="block px-4 py-2.5 rounded-xl transition-colors hover:text-[#1A73E8] hover:bg-slate-50">الشركاء</a>
                        <a href="#" class="block px-4 py-2.5 rounded-xl transition-colors hover:text-[#1A73E8] hover:bg-slate-50">الحسابات المحظورة</a>
                    </div>
                </div>

                <!-- Ads Management -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            إدارة الإعلانات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <!-- Expandable content can go here -->
                </div>

                <!-- Purchases Management -->
                <a href="#" class="flex items-center px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    إدارة المشتريات
                </a>

                <!-- Payments Management -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            إدارة المدفوعات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>

                <!-- Content Management -->
                <a href="#" class="flex items-center px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    إدارة المحتوى
                </a>

                <!-- Reviews -->
                <a href="#" class="flex items-center px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    التقييمات
                </a>

                <!-- Reports -->
                <a href="#" class="flex items-center px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    التقارير والتحليلات
                </a>

                <!-- Notifications Management -->
                <a href="#" class="flex items-center px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    إدارة الإشعارات
                </a>

                <!-- Settings -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            الإعدادات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>

                <!-- Support -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C] rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            الدعم
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>

            </nav>
            
            <div class="p-6">
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
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto relative">
            
            <!-- Top Header -->
            <header class="bg-white h-24 flex items-center justify-between px-8 lg:px-12 shrink-0 z-30 sticky top-0 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] border-b border-slate-100">
                
                <!-- Welcome Title (Right side) -->
                <div>
                    <h1 class="text-[1.75rem] font-black text-[#1C1C1C] mb-1 tracking-tight">مرحباً، {{ auth()->user()->name ?? 'أحمد' }}</h1>
                    <p class="text-sm font-semibold text-[#6F6F6F]">يمكنك هنا متابعة أداء المنصة وإدارة المستخدمين بسهولة.</p>
                </div>

                <!-- Profile Components (Left side) -->
                <div class="flex items-center gap-6">
                    <!-- Notification Bell -->
                    <button class="relative w-11 h-11 bg-white border border-slate-200 rounded-full flex items-center justify-center text-[#6F6F6F] hover:text-[#1A73E8] hover:border-[#1A73E8] transition-all focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-2.5 right-3 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-3 focus:outline-none group">
                            <div class="text-left hidden sm:block">
                                <p class="text-sm font-bold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors">{{ auth()->user()->name ?? 'أحمد محمد' }}</p>
                                <p class="text-xs font-semibold text-[#6F6F6F] text-left">أدمن</p>
                            </div>
                            <div class="w-11 h-11 rounded-full bg-[#1A73E8] text-white flex items-center justify-center font-black text-lg shadow-lg shadow-blue-500/30">
                                {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                            </div>
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition.opacity
                             class="absolute left-0 mt-3 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-2 z-50" x-cloak>
                            <a href="#" class="block px-4 py-2 text-sm font-semibold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">الملف الشخصي</a>
                            <a href="#" class="block px-4 py-2 text-sm font-semibold text-[#1C1C1C] hover:bg-slate-50 hover:text-[#1A73E8]">الإعدادات</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Inner Canvas Content Page -->
            <div class="flex-1 p-8 lg:p-12 pt-0 pb-20">
                <!-- Page Title Area (usually inside content) -->
                @yield('content')
            </div>
            
        </main>
    </div>

    @stack('scripts')
</body>
</html>
