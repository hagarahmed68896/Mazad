<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم التحقق بنجاح - لوحة تحكم مزاد</title>
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
        
        <!-- Left Side: Success Message -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 md:p-12 relative overflow-y-auto">
            
            <!-- Logo -->
            <div class="flex items-center justify-end w-full lg:px-4">
                <a href="/" class="text-3xl font-bold text-[#1A73E8]">Logo</a>
            </div>

            <!-- Content -->
            <div class="max-w-md mx-auto w-full flex-grow flex flex-col justify-center items-center text-center my-auto">
                <!-- Checkmark Overlay -->
                <div class="relative w-32 h-32 mx-auto mb-8 flex items-center justify-center">
                    <div class="absolute inset-0 bg-blue-100 rounded-full animate-ping duration-1000"></div>
                    <div class="absolute inset-2 bg-blue-200 rounded-full animate-pulse"></div>
                    <div class="relative w-24 h-24 bg-[#1A73E8] rounded-full flex items-center justify-center shadow-2xl shadow-blue-500/40 animate-in zoom-in spin-in-90 duration-700">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h1 class="text-3xl font-black text-[#1C1C1C] mb-3 tracking-tight leading-tight">
                    تم التحقق بنجاح
                </h1>
                <p class="text-[#6F6F6F] text-base font-medium leading-relaxed mb-10">
                    يمكنك الآن الوصول إلى لوحة تحكم وإدارة النظام.
                </p>

                <a href="{{ route('admin.dashboard') }}" class="w-full py-4 bg-[#1A73E8] text-white rounded-xl text-xl font-bold flex items-center justify-center hover:bg-blue-700 transform active:scale-[0.98] transition-all shadow-lg shadow-blue-500/30 group gap-3">
                    ابدأ الآن
                    <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
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
                <div class="w-full bg-white/10 p-3 rounded-2xl backdrop-blur-md shadow-2xl relative border border-white/10 group" style="min-height: 250px;">
                    <!-- Added subtle hover effect mirroring the success state bounce -->
                    <div class="absolute -inset-2 bg-gradient-to-tr from-blue-400/30 via-white/10 to-blue-300/30 rounded-3xl blur-xl opacity-30 group-hover:opacity-60 transition duration-1000"></div>
                    
                    <div class="w-full bg-[#f8fafc] rounded-xl shadow-inner flex p-3 gap-3 relative z-10">
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
