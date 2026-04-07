<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة تحكم مزاد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-white overflow-hidden antialiased h-screen">

    <div class="flex lg:flex-row-reverse h-screen bg-[#F7F7F7]">
        
        <!-- Left Side: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 md:p-12 relative overflow-y-auto">
            
            <!-- Logo -->
            <div class="flex items-center justify-end w-full lg:px-4">
                <a href="/" class="text-3xl font-bold text-[#1A73E8]">Logo</a>
            </div>

            <!-- Form Content -->
            <div class="max-w-md mx-auto w-full flex-grow flex flex-col justify-center my-auto">
                <div class="text-right mb-10">
                    <h1 class="text-3xl font-black text-[#1C1C1C] mb-2">تسجيل الدخول</h1>
                    <p class="text-[#6F6F6F] text-sm font-medium">أدخل بياناتك للوصول إلى لوحة تحكم وإدارة النظام بكفاءة.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold mb-6 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('admin.post-login') }}" method="POST" class="space-y-6">
                    @csrf
                    <!-- Phone Input -->
                    <div class="space-y-2 relative text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-3" for="phone">رقم الجوال</label>
                        <div class="relative flex items-center border border-[#B0B0B0] rounded-md overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all">
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="9" pattern="^5[0-9]{8}$" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length > 0 && this.value[0] !== '5') this.value = '';" placeholder="أدخل رقم الجوال (مثال: 5XXXXXXXX)" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#B0B0B0] font-semibold" dir="rtl">
                            <span class="px-4 py-3 text-[#B0B0B0] font-bold text-sm bg-transparent border-r border-[#B0B0B0]" dir="ltr">+966</span>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2 relative text-right mt-5">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-3" for="password">كلمة المرور</label>
                        <div class="relative flex items-center border border-[#B0B0B0] rounded-md overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all">
                            <button type="button" id="togglePassword" class="px-4 py-3 text-[#B0B0B0] hover:text-[#1A73E8] focus:outline-none transition-colors border-l border-[#B0B0B0] bg-transparent">
                                <svg id="eyeIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg id="eyeSlashIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                            <input type="password" name="password" id="password" required placeholder="أدخل كلمة المرور" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#B0B0B0] font-semibold" dir="rtl">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('admin.forgot-password') }}" class="text-sm font-semibold text-[#1A73E8] hover:text-blue-700 underline underline-offset-4 decoration-blue-200 transition-colors">نسيت كلمة المرور؟</a>
                        
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <span class="text-sm font-semibold text-[#1C1C1C] group-hover:text-[#1A73E8] transition-colors">تذكرني</span>
                            <div class="relative w-5 h-5 rounded overflow-hidden">
                                <input type="checkbox" name="remember" class="peer sr-only">
                                <div class="w-5 h-5 bg-[#EEEEEE] border-2 border-slate-300 rounded peer-checked:bg-[#1A73E8] peer-checked:border-[#1A73E8] transition-all flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 mt-8 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg text-lg font-bold transition-colors">
                        تسجيل الدخول
                    </button>
                </form>
            </div>
            
            <div class="h-8"></div>
        </div>

        <!-- Right Side: Custom Branded Gradient -->
        <div class="hidden lg:flex w-1/2 relative overflow-hidden flex-col items-center justify-center p-16 z-0" style="background: radial-gradient(73.08% 188.96% at 56.27% 73.08%, #1A73E8 0%, #0E3E76 100%);">
            
            <div class="relative z-10 flex flex-col items-center text-center w-full max-w-lg mx-auto">
                <h2 class="text-4xl font-black text-white mb-3 tracking-wide">لوحة تحكم مزاد</h2>
                <p class="text-blue-100 text-lg mb-12 max-w-sm mx-auto font-medium">
                    نظام متكامل لإدارة الإعلانات، الحسابات والمنتجات بكفاءة.
                </p>

                <!-- Mock Dashboard UI in image -->
                <div class="w-full bg-white/10 p-3 rounded-2xl backdrop-blur-md shadow-2xl relative border border-white/10" style="min-height: 250px;">
                    <!-- A CSS-drawn mockup matching the one in the center -->
                    <div class="w-full bg-[#f8fafc] rounded-xl shadow-inner flex p-3 gap-3">
                        
                        <!-- Details container -->
                        <div class="flex flex-col gap-3 flex-grow">
                            <!-- Top row of small widgets -->
                            <div class="flex gap-3">
                                <div class="bg-white p-2 rounded shadow-sm w-1/2 flex flex-col h-20">
                                    <div class="w-1/3 h-2 bg-gray-200 rounded mb-auto"></div>
                                    <div class="w-full h-10 flex items-end justify-between px-1">
                                        <div class="w-2 bg-blue-300 h-[60%]"></div>
                                        <div class="w-2 bg-blue-400 h-[70%]"></div>
                                        <div class="w-2 bg-blue-500 h-[100%]"></div>
                                        <div class="w-2 bg-blue-400 h-[80%]"></div>
                                        <div class="w-2 bg-blue-300 h-[50%]"></div>
                                    </div>
                                </div>
                                <div class="bg-white p-2 rounded shadow-sm w-1/2 flex flex-col h-20">
                                    <div class="w-1/3 h-2 bg-gray-200 rounded mb-auto"></div>
                                    <div class="w-full h-8 flex items-end justify-around px-1 gap-1">
                                        <div class="w-3 bg-blue-400 h-[40%]"></div>
                                        <div class="w-3 bg-blue-600 h-[90%]"></div>
                                        <div class="w-3 bg-blue-400 h-[60%]"></div>
                                        <div class="w-3 bg-blue-500 h-[75%]"></div>
                                        <div class="w-3 bg-blue-600 h-[100%]"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom row charts -->
                            <div class="flex gap-3 h-24">
                                <div class="bg-white p-2 rounded shadow-sm w-1/2 h-full flex items-end pb-2 justify-around">
                                    <div class="w-3 bg-blue-500 h-[80%]"></div>
                                    <div class="w-3 bg-blue-400 h-[60%]"></div>
                                    <div class="w-3 bg-blue-300 h-[40%]"></div>
                                    <div class="w-3 bg-blue-600 h-[90%]"></div>
                                </div>
                                <div class="bg-white p-2 rounded shadow-sm w-1/2 h-full flex justify-center items-center">
                                    <!-- simple css donut representation -->
                                    <div class="w-16 h-16 rounded-full border-[6px] border-l-blue-400 border-t-yellow-400 border-r-gray-300 border-b-green-500"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar mock -->
                        <div class="w-16 h-auto bg-white rounded flex flex-col items-center py-4 gap-3 shadow-sm border border-gray-100">
                            <div class="w-8 h-2 bg-gray-200 rounded"></div>
                            <div class="w-6 h-2 bg-blue-200 rounded"></div>
                            <div class="w-8 h-2 bg-gray-200 rounded"></div>
                            <div class="w-7 h-2 bg-gray-200 rounded"></div>
                            <div class="w-6 h-2 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

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
