<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختر نوع الحساب - منصة مزاد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Header -->
    <header class="bg-white w-full relative z-10" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div class="container px-8 py-6 flex justify-between items-center mx-auto">
            <a href="/" style="text-decoration: none;">
                @include('partials.logo')
            </a>
            <a href="{{ route('login') }}" class="text-sm font-bold text-[#64748B] hover:text-[#1A73E8] transition-colors">هل لديك حساب؟ تسجيل دخول</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="max-w-4xl w-full text-center">
            <h1 class="text-3xl md:text-4xl font-black text-[#1E293B] mb-4">اختر نوع حسابك</h1>
            <p class="text-[#64748B] font-semibold mb-12">اختر نوع الحساب الذي ترغب في إنشائه لتبدأ تجربتك معنا</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Individual Card -->
                <a href="{{ route('register.individual') }}" class="group relative bg-white p-10 rounded-[2.5rem] border-2 border-transparent hover:border-[#1A73E8] hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 flex flex-col items-center text-center">
                    <div class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center text-[#1A73E8] mb-8 group-hover:scale-110 group-hover:bg-[#1A73E8] group-hover:text-white transition-all duration-500 shadow-inner">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-[#1E293B] mb-4">حساب فرد</h2>
                    <p class="text-sm font-bold text-[#64748B] leading-relaxed mb-8">للمزايدة على المنتجات، الشراء الفوري، وإضافة إعلاناتك الخاصة كفرد.</p>
                    <div class="mt-auto px-8 py-3 bg-[#F1F5F9] text-[#1E293B] group-hover:bg-[#1A73E8] group-hover:text-white rounded-xl font-bold transition-all duration-500">
                        إنشاء حساب فرد
                    </div>
                </a>

                <!-- Company Card -->
                <a href="{{ route('register.company') }}" class="group relative bg-white p-10 rounded-[2.5rem] border-2 border-transparent hover:border-[#1A73E8] hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 flex flex-col items-center text-center">
                    <div class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center text-[#1A73E8] mb-8 group-hover:scale-110 group-hover:bg-[#1A73E8] group-hover:text-white transition-all duration-500 shadow-inner">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-[#1E293B] mb-4">حساب شركة</h2>
                    <p class="text-sm font-bold text-[#64748B] leading-relaxed mb-8">للشركات والمؤسسات التجارية التي ترغب في عرض خدماتها ومنتجاتها في المنصة.</p>
                    <div class="mt-auto px-8 py-3 bg-[#F1F5F9] text-[#1E293B] group-hover:bg-[#1A73E8] group-hover:text-white rounded-xl font-bold transition-all duration-500">
                        إنشاء حساب شركة
                    </div>
                </a>

                <!-- Partner Card -->
                <a href="{{ route('register.partner') }}" class="group relative bg-white p-10 rounded-[2.5rem] border-2 border-transparent hover:border-[#1A73E8] hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 flex flex-col items-center text-center">
                    <div class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center text-[#1A73E8] mb-8 group-hover:scale-110 group-hover:bg-[#1A73E8] group-hover:text-white transition-all duration-500 shadow-inner">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-[#1E293B] mb-4">حساب شريك</h2>
                    <p class="text-sm font-bold text-[#64748B] leading-relaxed mb-8">للجهات المقدمة لخدمات مرتبطة بالمزادات مثل فحص السيارات وغيرها.</p>
                    <div class="mt-auto px-8 py-3 bg-[#F1F5F9] text-[#1E293B] group-hover:bg-[#1A73E8] group-hover:text-white rounded-xl font-bold transition-all duration-500">
                        إنشاء حساب شريك
                    </div>
                </a>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center bg-white border-t border-slate-100">
        <p class="text-slate-400 text-sm font-medium">© 2025 منصة مزاد. جميع الحقوق محفوظة</p>
    </footer>

</body>
</html>
