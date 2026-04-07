<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق - لوحة تحكم مزاد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .otp-input {
            width: 3.5rem;
            height: 4rem;
            flex-shrink: 0;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 900;
            border-radius: 0.75rem;
            background-color: #F8FAFC;
            border: 1px solid #e2e8f0;
            transition: all .2s;
            outline: none;
            color: #1e293b;
        }
        .otp-input:focus {
            border-color: #1A73E8;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(20, 106, 245, 0.2);
        }
        @media (min-width: 768px) {
            .otp-input {
                width: 4rem;
                height: 4.5rem;
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body class="bg-white overflow-hidden antialiased h-screen">

    <div class="flex lg:flex-row-reverse h-screen bg-[#F7F7F7]">
        
        <!-- Left Side: Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 md:p-12 relative overflow-y-auto">
            
            <!-- Logo -->
            <div class="flex items-center justify-end w-full lg:px-4">
                <a href="/" class="text-3xl font-bold text-[#1A73E8]">Logo</a>
            </div>

            <!-- Form Content -->
            <div class="max-w-md mx-auto w-full flex-grow flex flex-col justify-center my-auto">
                <div class="mb-10 text-center">
                    <div class="w-24 h-24 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-6 text-[#1A73E8]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                            <path d="m10.273 2.513-.921-.944.715-.698.622.637.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636a2.89 2.89 0 0 1 4.134 0l-.715.698a1.89 1.89 0 0 0-2.704 0l-.92.944-1.32-.016a1.89 1.89 0 0 0-1.911 1.912l.016 1.318-.944.921a1.89 1.89 0 0 0 0 2.704l.944.92-.016 1.32a1.89 1.89 0 0 0 1.912 1.911l1.318-.016.921.944a1.89 1.89 0 0 0 2.704 0l.92-.944 1.32.016a1.89 1.89 0 0 0 1.911-1.912l-.016-1.318.944-.921a1.89 1.89 0 0 0 0-2.704l-.944-.92.016-1.32a1.89 1.89 0 0 0-1.912-1.911z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-black text-[#1C1C1C] mb-3">التحقق</h1>
                    <p class="text-[#6F6F6F] text-sm font-medium px-4">
                        الرجاء إدخال رمز التحقق المرسل إلى رقم جوالك
                    </p>
                </div>

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold mb-6 text-sm text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="otpForm" action="{{ route('admin.post-verify') }}" method="POST" class="space-y-8">
                    @csrf
                    <div class="flex justify-center gap-3 dir-ltr" id="otpContainer" dir="ltr">
                        <input type="text" name="otp[]" maxlength="1" autofocus class="otp-input" inputmode="numeric" placeholder="0">
                        <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" placeholder="0">
                        <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" placeholder="0">
                        <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" placeholder="0">
                    </div>

                    <div class="space-y-6">
                        <button type="submit" class="w-full py-4 bg-[#1A73E8] text-white rounded-xl text-lg font-bold hover:bg-blue-700 active:scale-[0.98] transition-all shadow-lg shadow-blue-600/30">
                            تحقق
                        </button>

                        <div class="text-center">
                            <button type="button" id="resendBtn" disabled class="text-[#B0B0B0] bg-slate-100 px-6 py-2.5 rounded-lg text-sm font-bold cursor-not-allowed transition-all">
                                إعادة إرسال الرمز <span id="timer" class="text-[#1A73E8] font-black ml-1">60s</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-8 text-center">
                    <a href="{{ route('admin.login') }}" class="text-[#B0B0B0] hover:text-[#1A73E8] transition-colors font-bold text-sm">العودة لتسجيل الدخول</a>
                </div>
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

                <!-- Mock Dashboard UI -->
                <div class="w-full bg-white/10 p-3 rounded-2xl backdrop-blur-md shadow-2xl relative border border-white/10" style="min-height: 250px;">
                    <div class="w-full bg-[#f8fafc] rounded-xl shadow-inner flex p-3 gap-3">
                        <div class="flex flex-col gap-3 flex-grow">
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
                            <div class="flex gap-3 h-24">
                                <div class="bg-white p-2 rounded shadow-sm w-1/2 h-full flex items-end pb-2 justify-around">
                                    <div class="w-3 bg-blue-500 h-[80%]"></div>
                                    <div class="w-3 bg-blue-400 h-[60%]"></div>
                                    <div class="w-3 bg-blue-300 h-[40%]"></div>
                                    <div class="w-3 bg-blue-600 h-[90%]"></div>
                                </div>
                                <div class="bg-white p-2 rounded shadow-sm w-1/2 h-full flex justify-center items-center">
                                    <div class="w-16 h-16 rounded-full border-[6px] border-l-blue-400 border-t-yellow-400 border-r-gray-300 border-b-green-500"></div>
                                </div>
                            </div>
                        </div>
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
        // OTP Input Logic
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otpForm');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Auto-submit when all 4 are filled
                const allFilled = Array.from(inputs).every(i => i.value.length === 1);
                if (allFilled) {
                    setTimeout(() => form.submit(), 200);
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Allow only numbers
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // Timer Logic
        let seconds = 60;
        const timerSpan = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        const countdown = setInterval(() => {
            seconds--;
            if (timerSpan) timerSpan.textContent = `${seconds}s`;
            if (seconds <= 0) {
                clearInterval(countdown);
                resendBtn.disabled = false;
                resendBtn.classList.remove('cursor-not-allowed', 'text-[#B0B0B0]', 'bg-slate-100');
                resendBtn.classList.add('text-[#1A73E8]', 'cursor-pointer', 'bg-blue-50');
                if (timerSpan) timerSpan.textContent = '';
                resendBtn.innerHTML = 'إعادة إرسال الرمز';
            }
        }, 1000);
    </script>
</body>
</html>
