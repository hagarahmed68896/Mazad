<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم التحقق بنجاح - منصة مزاد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #F1F5F9; }
        .login-footer {
            background-color: #155B9A;
            background-image: linear-gradient(135deg, #155B9A 0%, #0D3E76 100%);
        }
        .success-modal {
            background: white;
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            z-index: 50;
        }
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 40;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Header -->
    <header class="bg-[#F8FAFC] w-full relative z-10" style="box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <div class="container px-8 py-6 flex justify-between items-center">
            <a href="/" style="text-decoration: none;">
                @include('partials.logo')
            </a>
            <a href="/" class="px-6 py-2 border border-[#E2E8F0] rounded-lg text-sm font-bold text-[#64748B] hover:bg-white transition-all">تخطي</a>
        </div>
    </header>

    <!-- Main Content (Darkened Background) -->
    <main class="flex-grow flex items-center justify-center px-8 lg:px-16 py-10 mb-8 relative">
        <!-- Mock background (Similar to OTP page) -->
        <div class="bg-white/50 rounded-md flex flex-col lg:flex-row w-full max-w-[1050px] min-h-[650px] overflow-hidden mx-auto blur-[2px] pointer-events-none">
            <div class="hidden lg:flex w-1/2 bg-[#1A73E8]/50 p-8 order-last lg:order-none"></div>
            <div class="w-full lg:w-1/2 p-8 lg:px-16 flex flex-col justify-center"></div>
        </div>

        <!-- Success Modal Overlay -->
        <div class="overlay">
            <div class="success-modal">
                <div style="width: 96px; height: 96px; background-color: #22C55E; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);">
                    <svg style="width: 48px; height: 48px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </div>

                <h1 class="text-2xl font-black text-[#1C1C1C] mb-3">تم التحقق بنجاح</h1>

                @if(Auth::check() && Auth::user()->account_type === 'partner')
                    @if(Auth::user()->interests()->exists())
                        <p class="text-[#64748B] text-sm font-semibold leading-loose mb-8">
                            مرحباً بك مجدداً، يمكنك الآن الانتقال إلى لوحة تحكم خدماتك لإدارتها بكل سهولة.
                        </p>

                        <a href="{{ route('ads.my') }}" class="block w-full py-4 bg-[#1A73E8] hover:bg-blue-700 text-white rounded-xl text-lg font-bold transition-all shadow-lg shadow-blue-500/25">
                            الذهاب للوحة التحكم
                        </a>
                    @else
                        <p class="text-[#64748B] text-sm font-semibold leading-loose mb-8">
                            مرحبا بك، يمكنك الآن إعداد خدماتك والبدء في استقبال الطلبات.
                        </p>

                        <a href="{{ route('register.partner.interests') }}" class="block w-full py-4 bg-[#1A73E8] hover:bg-blue-700 text-white rounded-xl text-lg font-bold transition-all shadow-lg shadow-blue-500/25">
                            أضف خدماتك
                        </a>
                    @endif
                @else
                    <p class="text-[#64748B] text-sm font-semibold leading-loose mb-8">
                        تقدر الحين تبدأ تتصفح وتشارك في المزادات.
                    </p>

                    <a href="{{ $redirectUrl }}" class="block w-full py-4 bg-[#1A73E8] hover:bg-blue-700 text-white rounded-xl text-lg font-bold transition-all shadow-lg shadow-blue-500/25">
                        {{ $redirectText }}
                    </a>
                @endif
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="login-footer py-6 text-center mt-auto w-full">
        <p class="text-white text-sm font-medium" dir="ltr">©2025 copyright</p>
    </footer>

</body>
</html>
