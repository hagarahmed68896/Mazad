<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من رقم الجوال - منصة مزاد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #F1F5F9; }
        .otp-input {
            width: 70px;
            height: 70px;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #1C1C1C;
            background: white;
            transition: all 0.3s ease;
        }
        .otp-input:focus {
            border-color: #1A73E8;
            box-shadow: 0 0 0 1px #1A73E8;
            outline: none;
        }
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
                <img src="{{ asset('images/login-illustration.png') }}" alt="OTP Illustration" class="max-w-[90%] max-h-[90%] object-contain z-10" id="otpIllustration">
                <script>
                    document.getElementById('otpIllustration').onerror = function() {
                        this.style.display = 'none';
                        this.insertAdjacentHTML('afterend', '<div class="text-white/50 text-center border-2 border-dashed border-white/30 rounded-xl w-3/4 h-3/4 flex flex-col items-center justify-center"><p class="text-xl font-bold mb-2">صورة توضيحية</p><p class="text-sm">أضف الصورة باسم login-illustration.png</p></div>');
                    }
                </script>
            </div>

            <!-- Form Section -->
            <div class="w-full bg-white lg:w-1/2 p-8 lg:px-16 flex flex-col justify-center text-center">
                <div class="mb-8">
                    <h1 class="text-[2rem] font-black text-[#1C1C1C] mb-4">التحقق من رقم الجوال</h1>
                    <p class="text-[#64748B] text-[0.95rem] font-semibold leading-loose max-w-[90%] mx-auto">
                        أرسلنا رمز تحقق مكون من 4 أرقام إلى جوالك: <span dir="ltr" class="text-[#1A73E8]">+966 5X XXX XXXX</span> يرجى إدخال الرمز أدناه لإكمال العملية.
                    </p>
                </div>

                <form id="otpForm" action="{{ route('admin.post-verify') }}" method="POST" class="space-y-8">
                    @csrf
                    <div class="flex justify-center gap-4 dir-ltr" dir="ltr">
                        <input type="text" name="otp[]" maxlength="1" autofocus class="otp-input" inputmode="numeric">
                        <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric">
                    </div>

                    <div class="flex flex-col items-center gap-6">
                        <div id="timerContainer" class="bg-[#E2E8F0] px-6 py-2 rounded-full">
                            <p class="text-sm font-bold text-[#64748B]">إعادة إرسال الرمز خلال : <span id="timer" class="text-[#1C1C1C]">00:45</span></p>
                        </div>
                        <button type="button" id="resendBtn" class="hidden text-[#1A73E8] font-bold text-sm hover:underline transition-all">إعادة إرسال الرمز</button>

                        <button type="submit" id="submitBtn" disabled class="w-full py-4 bg-[#E2E8F0] text-[#94A3B8] rounded-xl text-lg font-bold transition-all cursor-not-allowed">
                            تحقق
                        </button>
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
        const inputs = document.querySelectorAll('.otp-input');
        const submitBtn = document.getElementById('submitBtn');
        const resendBtn = document.getElementById('resendBtn');
        const timerContainer = document.getElementById('timerContainer');
        const form = document.getElementById('otpForm');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                checkInputs();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        function checkInputs() {
            const allFilled = Array.from(inputs).every(input => input.value.length === 1);
            if (allFilled) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-[#E2E8F0]', 'text-[#94A3B8]', 'cursor-not-allowed');
                submitBtn.classList.add('bg-[#1A73E8]', 'text-white', 'hover:bg-blue-700');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-[#E2E8F0]', 'text-[#94A3B8]', 'cursor-not-allowed');
                submitBtn.classList.remove('bg-[#1A73E8]', 'text-white', 'hover:bg-blue-700');
            }
        }

        // Timer Logic
        let timeLeft = 45;
        let countdown;
        const timerEl = document.getElementById('timer');

        function startTimer() {
            timeLeft = 45;
            timerContainer.classList.remove('hidden');
            resendBtn.classList.add('hidden');
            
            if (countdown) clearInterval(countdown);
            
            countdown = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerContainer.classList.add('hidden');
                    resendBtn.classList.remove('hidden');
                }
            }, 1000);
        }

        // Start timer on page load
        startTimer();

        resendBtn.addEventListener('click', () => {
            // Add your actual resend API logic here
            // For now, we just restart the UI timer
            startTimer();
        });
    </script>
</body>
</html>
