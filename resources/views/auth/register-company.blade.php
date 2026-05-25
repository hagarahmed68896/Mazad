<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد كشركة - منصة مزاد</title>
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
        .login-footer p {
            position: relative;
            z-index: 2;
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
        <div class="bg-[#F7F7F7] rounded-md shadow-[0_10px_40px_rgba(0,0,0,0.08)] flex flex-col lg:flex-row w-full max-w-[1050px] min-h-[750px] overflow-hidden mx-auto">
            
            <!-- Illustration Section -->
            <div class="hidden lg:flex w-1/2 bg-[#1A73E8] relative flex-col items-center justify-center p-8 order-last lg:order-none">
                <img src="{{ asset('images/login-illustration.png') }}" alt="Register Illustration" class="max-w-[90%] max-h-[90%] object-contain z-10" id="registerIllustration">
                <script>
                    document.getElementById('registerIllustration').onerror = function() {
                        this.style.display = 'none';
                        this.insertAdjacentHTML('afterend', '<div class="text-white/50 text-center border-2 border-dashed border-white/30 rounded-xl w-3/4 h-3/4 flex flex-col items-center justify-center"><p class="text-xl font-bold mb-2">صورة توضيحية</p><p class="text-sm">أضف الصورة باسم login-illustration.png</p></div>');
                    }
                </script>
            </div>

            <!-- Form Section -->
            <div class="w-full bg-white lg:w-1/2 p-8 lg:px-16 flex flex-col justify-center">
                <div class="text-right mb-6">
                    <h1 class="text-2xl font-black text-[#1C1C1C] mb-2">إنشاء حساب جديد كشركة</h1>
                    <p class="text-[#64748B] text-[0.95rem] font-semibold leading-loose">
                        أنشئ حسابك وشارك في المزادات او اشتري فوري.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold text-sm text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('register.post') }}" method="POST" class="space-y-3">
                    @csrf
                    <!-- Name Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="name">الاسم</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="أدخل الاسم الذي سيظهر لمستخدمي البرنامج" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#94A3B8] font-semibold text-[0.9rem]" dir="rtl">
                        </div>
                    </div>

                    <!-- Company Name Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="company_name">اسم الشركة</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required placeholder="أدخل اسم الشركة" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#94A3B8] font-semibold text-[0.9rem]" dir="rtl">
                        </div>
                    </div>

                    <!-- Phone Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="phone">رقم الجوال</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="9" pattern="^5[0-9]{8}$" oninput="let val = this.value.replace(/[^0-9]/g, ''); if(val.startsWith('0')) { val = val.substring(1); } this.value = val.slice(0,9);" placeholder="أدخل رقم الجوال" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#94A3B8] font-semibold text-[0.9rem]" dir="rtl">
                            <span class="px-4 py-3 text-[#94A3B8] font-semibold text-sm bg-transparent border-r border-[#E2E8F0]" dir="ltr">+966</span>
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="email">البريد الإلكتروني</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="email" name="email" id="email" required placeholder="أدخل البريد الإلكتروني" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#94A3B8] font-semibold text-[0.9rem]" dir="rtl">
                        </div>
                    </div>

                    <!-- Commercial Registry Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="cr_number">رقم السجل التجاري</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <input type="text" name="cr_number" id="cr_number" required placeholder="أدخل رقم السجل التجاري" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#94A3B8] font-semibold text-[0.9rem]" dir="rtl">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1 text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="password">كلمة المرور</label>
                        <div class="relative flex items-center border border-[#E2E8F0] rounded-xl overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all bg-white">
                            <button type="button" id="togglePassword" class="px-4 py-3 text-[#94A3B8] hover:text-[#1A73E8] focus:outline-none transition-colors border-l border-[#E2E8F0] bg-transparent">
                                <svg id="eyeIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg id="eyeSlashIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                            <input type="password" name="password" id="password" required placeholder="أدخل كلمة المرور" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#94A3B8] font-semibold text-[0.9rem]" dir="rtl">
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <label class="flex items-center justify-end gap-2 pt-1 cursor-pointer group">
                        <span class="text-sm font-semibold text-[#475569]">أوافق على <a href="#" class="text-[#1A73E8] hover:underline" onclick="event.stopPropagation()">الشروط والأحكام</a> و <a href="#" class="text-[#1A73E8] hover:underline" onclick="event.stopPropagation()">سياسة الخصوصية</a></span>
                        <div class="relative w-5 h-5 rounded overflow-hidden">
                            <input type="checkbox" name="terms" class="peer sr-only" required>
                            <div class="w-5 h-5 bg-white border-2 border-[#CBD5E1] rounded peer-checked:bg-[#1A73E8] peer-checked:border-[#1A73E8] group-hover:border-[#1A73E8] transition-all flex items-center justify-center">
                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                    </label>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 mt-2 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-lg font-bold transition-colors">
                        تفعيل عبر نفاذ
                    </button>
                    
                    <div class="text-center mt-4">
                        <p class="text-sm font-medium text-[#1C1C1C]">
                            هل لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-[#1A73E8] font-bold hover:underline underline-offset-4">تسجيل الدخول</a>
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="login-footer py-6 text-center mt-auto w-full">
        <p class="text-white text-sm font-medium" dir="ltr">©2025 copyright</p>
    </footer>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function (e) {
                e.preventDefault();
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    eyeIcon.classList.remove('hidden');
                    eyeSlashIcon.classList.add('hidden');
                } else {
                    eyeIcon.classList.add('hidden');
                    eyeSlashIcon.classList.remove('hidden');
                }
            });
        }
    </script>
</body>
</html>
