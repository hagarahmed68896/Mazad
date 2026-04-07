<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نسيت كلمة المرور؟ - لوحة تحكم مزاد</title>
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
        
        <!-- Left Side: Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 md:p-12 relative overflow-y-auto">
            
            <!-- Logo -->
            <div class="flex items-center justify-end w-full lg:px-4">
                <a href="/" class="text-3xl font-bold text-[#1A73E8]">Logo</a>
            </div>

            <!-- Form Content -->
            <div class="max-w-md mx-auto w-full flex-grow flex flex-col justify-center my-auto">
                <div class="text-right mb-10">
                    <h1 class="text-3xl font-black text-[#1C1C1C] mb-2">نسيت كلمة المرور؟</h1>
                    <p class="text-[#6F6F6F] text-sm font-medium">أدخل رقم الجوال المسجل لدينا وسنقوم بإرسال رمز تحقق لك.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold mb-6 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 font-bold mb-6 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.post-forgot-password') }}" method="POST" class="space-y-6">
                    @csrf
                    <!-- Phone Input -->
                    <div class="space-y-2 relative text-right">
                        <label class="block text-sm font-bold text-[#1C1C1C] mb-3" for="phone">رقم الجوال</label>
                        <div class="relative flex items-center bg-[#EEEEEE] border border-[#B0B0B0] rounded-md overflow-hidden focus-within:border-[#1A73E8] focus-within:ring-1 focus-within:ring-[#1A73E8] transition-all">
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="9" pattern="^5[0-9]{8}$" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length > 0 && this.value[0] !== '5') this.value = '';" placeholder="أدخل رقم الجوال (مثال: 5XXXXXXXX)" class="w-full bg-transparent border-none focus:ring-0 px-4 py-3 placeholder:text-[#B0B0B0] font-semibold" dir="rtl">
                            <span class="px-4 py-3 text-[#B0B0B0] font-bold text-sm bg-transparent border-r border-[#B0B0B0]" dir="ltr">+966</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 mt-8 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-lg font-bold transition-colors shadow-lg shadow-blue-600/30">
                        إرسال رمز التحقق
                    </button>
                </form>

                <div class="mt-10 text-center flex items-center justify-center gap-2">
                    <p class="text-[#6F6F6F] font-semibold text-sm">تذكرت كلمة المرور؟</p>
                    <a href="{{ route('admin.login') }}" class="text-[#1A73E8] hover:text-blue-700 transition-colors font-black text-sm">تسجيل الدخول</a>
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
</body>
</html>
