<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور - منصة مزاد</title>
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
                <img src="{{ asset('images/login-illustration.png') }}" alt="Reset Password Illustration" class="max-w-[90%] max-h-[90%] object-contain z-10" id="resetIllustration">
                <script>
                    document.getElementById('resetIllustration').onerror = function() {
                        this.style.display = 'none';
                        this.insertAdjacentHTML('afterend', '<div class="text-white/50 text-center border-2 border-dashed border-white/30 rounded-xl w-3/4 h-3/4 flex flex-col items-center justify-center"><p class="text-xl font-bold mb-2">صورة توضيحية</p><p class="text-sm">أضف الصورة باسم login-illustration.png</p></div>');
                    }
                </script>
            </div>

            <!-- Form Section -->
            <div class="w-full bg-white lg:w-1/2 p-8 lg:px-16 flex flex-col justify-center">
                <div class="text-right mb-10">
                    <h1 class="text-[2rem] font-black text-[#1C1C1C] mb-4">إعادة تعيين كلمة المرور</h1>
                    <p class="text-[#64748B] text-[0.95rem] font-semibold leading-loose">
                        أدخل كلمة مرور جديدة لحماية حسابك.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold text-sm text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('post-reset-password') }}" method="POST" class="space-y-6">
                    @csrf
                    <!-- New Password Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="password">كلمة المرور الجديدة</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="password" name="password" id="password" required placeholder="أدخل كلمة المرور الجديدة" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3.5 placeholder:text-[#94A3B8] font-semibold text-[0.95rem]" dir="rtl">
                            <button type="button" onclick="togglePassword('password', this)" class="px-4 text-[#94A3B8] hover:text-[#1A73E8] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="password_confirmation">تأكيد كلمة المرور</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="أعد إدخال كلمة المرور" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3.5 placeholder:text-[#94A3B8] font-semibold text-[0.95rem]" dir="rtl">
                            <button type="button" onclick="togglePassword('password_confirmation', this)" class="px-4 text-[#94A3B8] hover:text-[#1A73E8] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-lg font-bold transition-all shadow-lg shadow-blue-500/25">
                        تعيين كلمة المرور
                    </button>
                </form>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="login-footer py-6 text-center mt-auto w-full">
        <p class="text-white text-sm font-medium" dir="ltr">©2025 copyright</p>
    </footer>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('svg');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }
    </script>
</body>
</html>
