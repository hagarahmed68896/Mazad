<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نسيت كلمة المرور؟ - منصة مزاد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #F1F5F9; }
        .login-footer {
            background-color: #1557B0;
            background-image: linear-gradient(135deg, #1557B0 0%, #0a3066 100%);
            position: relative;
            overflow: hidden;
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
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-8 lg:px-16 py-10 mb-8">
        <div class="bg-[#F7F7F7] rounded-md shadow-[0_10px_40px_rgba(0,0,0,0.08)] flex flex-col lg:flex-row w-full max-w-[1050px] min-h-[650px] overflow-hidden mx-auto">
            
            <!-- Illustration Section -->
            <div class="hidden lg:flex w-1/2 bg-[#1A73E8] relative flex-col items-center justify-center p-8 order-last lg:order-none">
                <img src="{{ asset('images/login-illustration.png') }}" alt="Forgot Password Illustration" class="max-w-[90%] max-h-[90%] object-contain z-10" id="forgotIllustration">
                <script>
                    document.getElementById('forgotIllustration').onerror = function() {
                        this.style.display = 'none';
                        this.insertAdjacentHTML('afterend', '<div class="text-white/50 text-center border-2 border-dashed border-white/30 rounded-xl w-3/4 h-3/4 flex flex-col items-center justify-center"><p class="text-xl font-bold mb-2">صورة توضيحية</p><p class="text-sm">أضف الصورة باسم login-illustration.png</p></div>');
                    }
                </script>
            </div>

            <!-- Form Section -->
            <div class="w-full bg-white lg:w-1/2 p-8 lg:px-16 flex flex-col justify-center">
                <div class="text-right mb-10">
                    <h1 class="text-[2rem] font-black text-[#1C1C1C] mb-4">نسيت كلمة المرور؟</h1>
                    <p class="text-[#64748B] text-[0.95rem] font-semibold leading-loose">
                        أدخل رقم جوالك المسجل وسنرسل لك رمز تحقق لإعادة تعيين كلمة المرور.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold text-sm text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('post-forgot-password') }}" method="POST" class="space-y-6">
                    @csrf
                    <!-- Phone Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="phone">رقم الجوال</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="9" pattern="^5[0-9]{8}$" oninput="let val = this.value.replace(/[^0-9]/g, ''); if(val.startsWith('0')) { val = val.substring(1); } this.value = val.slice(0,9);" placeholder="أدخل رقم الجوال" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3.5 placeholder:text-[#94A3B8] font-semibold text-[0.95rem]" dir="rtl">
                            <span class="px-4 py-3.5 text-[#94A3B8] font-semibold text-sm bg-transparent border-r border-[#E2E8F0]" dir="ltr">+966</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-lg font-bold transition-all shadow-lg shadow-blue-500/25">
                        إرسال
                    </button>
                </form>

                <div class="mt-10 text-center">
                    <p class="text-[#64748B] font-semibold text-[0.95rem]">
                        تذكرت كلمة المرور؟ <a href="{{ route('login') }}" class="text-[#1A73E8] hover:underline">تسجيل الدخول</a>
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="login-footer py-6 text-center mt-auto w-full">
        <p class="text-white text-sm font-medium" dir="ltr">©2025 copyright</p>
    </footer>

</body>
</html>
