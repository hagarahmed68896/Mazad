            <!-- Navigation Links -->
            <nav class="flex-1 px-5 py-6 space-y-1.5 overflow-y-auto font-bold text-[15px]">
                
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
                        <a href="{{ route('admin.accounts.individuals') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts.individuals*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الأفراد</a>
                        <a href="{{ route('admin.accounts.companies') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts.companies*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الشركات</a>
                        <a href="{{ route('admin.accounts.partners') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts.partners*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">المندوبين</a>
                        <a href="{{ route('admin.accounts.associates') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts.associates*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الشركاء</a>
                        <a href="{{ route('admin.accounts.blocked-accounts') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts.blocked*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الحسابات المحظورة</a>
                    </div>
                </div>

                <!-- Ads Management -->
                <div x-data="{ open: {{ request()->routeIs('admin.ads.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.ads.*') ? 'text-[#1C1C1C] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            إدارة الإعلانات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.ads.incomplete') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.ads.incomplete*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الإعلانات الغير مكتملة</a>
                        <a href="{{ route('admin.ads.auctions') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.ads.auctions*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">المزادات</a>
                        <a href="{{ route('admin.ads.direct_purchase') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.ads.direct_purchase*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الشراء الفوري</a>
                        <a href="{{ route('admin.ads.tenders') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.ads.tenders*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">المناقصات</a>
                        <a href="{{ route('admin.ads.services') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.ads.services*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الخدمات</a>
                    </div>
                </div>

                <!-- Purchases Management -->
                <div x-data="{ open: {{ request()->routeIs('admin.purchases.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.purchases.*') ? 'text-[#1C1C1C] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all font-bold">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            إدارة المشتريات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.purchases.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.purchases.index') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">المشتريات</a>
                    </div>
                </div>

                <!-- Payments Management -->
                <div x-data="{ open: {{ request()->routeIs('admin.payments.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.payments.*') ? 'text-[#1C1C1C] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all font-bold">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            إدارة المدفوعات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.payments.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.payments.index') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">المدفوعات</a>
                        <a href="{{ route('admin.payments.commissions.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.payments.commissions.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">إدارة العمولات</a>
                        <a href="{{ route('admin.payments.settlements.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.payments.settlements.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">التسوية المالية</a>
                    </div>
                </div>

                <!-- Content Management -->
                <div x-data="{ open: {{ request()->routeIs('admin.categories.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.categories.*') ? 'text-[#1A73E8] bg-[#E8F0FE] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                            إدارة المحتوى
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">إدارة الفئات</a>
                        <a href="{{ route('admin.cities.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.cities.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">إدارة المدن والمناطق</a>
                        <a href="{{ route('admin.banners.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.banners.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">البانرات الإعلانية</a>
                    </div>
                </div>

                <!-- Reviews -->
                <a href="{{ route('admin.reviews.index') }}" class="flex items-center px-4 py-3.5 {{ request()->routeIs('admin.reviews.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    التقييمات
                </a>

                <!-- Reports & Analytics -->
                <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-3.5 {{ request()->routeIs('admin.reports.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    التقارير والتحليلات
                </a>

                <!-- Notifications Management -->
                <a href="{{ route('admin.notifications.index') }}" class="flex items-center px-4 py-3.5 {{ request()->routeIs('admin.notifications.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    إدارة الإشعارات
                </a>

                <!-- Settings -->
                <div x-data="{ open: {{ request()->routeIs('admin.profile.*') || request()->is('admin/settings*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.profile.*') ? 'text-[#1C1C1C] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            الإعدادات
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.profile.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.profile.index') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الملف الشخصي</a>
                        <a href="{{ route('admin.settings.contacts') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.settings.contacts') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">وسائل التواصل</a>
                        <a href="{{ route('admin.settings.ads') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.settings.ads') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">إعدادات الإعلانات</a>
                        <a href="{{ route('admin.settings.financial') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.settings.financial') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">إعدادات النظام المالي</a>
                        <a href="{{ route('admin.policies.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.policies.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">السياسات والشروط</a>
                    </div>
                </div>

                <!-- Support -->
                <div x-data="{ open: {{ request()->routeIs('admin.faqs.*') || request()->routeIs('admin.tickets.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5 {{ request()->routeIs('admin.faqs.*') || request()->routeIs('admin.tickets.*') ? 'text-[#1C1C1C] font-bold' : 'text-[#6F6F6F] hover:bg-slate-50 hover:text-[#1C1C1C]' }} rounded-xl transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            الدعم
                        </div>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 pr-10 py-2 space-y-1 text-[#6F6F6F]">
                        <a href="{{ route('admin.faqs.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.faqs.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الأسئلة الشائعة</a>
                        <a href="{{ route('admin.tickets.index') }}" class="block px-4 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.tickets.*') ? 'bg-[#E8F0FE] text-[#1A73E8] font-bold' : 'hover:text-[#1A73E8] hover:bg-slate-50' }}">الاستفسارات و الشكاوي</a>
                    </div>
                </div>

            </nav>
