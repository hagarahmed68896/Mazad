<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حدد خدماتك كشريك - منصة تمت</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .service-checkbox-card.checked {
            border-color: #1A73E8 !important;
            background-color: #F0F7FF !important;
        }
        .service-checkbox-card.checked .custom-checkbox {
            background-color: #1A73E8 !important;
            border-color: #1A73E8 !important;
        }
        .service-checkbox-card.checked .custom-checkbox i {
            display: block !important;
        }
        .service-checkbox-card.checked span {
            color: #1A73E8 !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Header -->
    <header class="bg-[#F8FAFC] w-full relative z-10" style="box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <div class="container px-8 py-6 flex justify-between items-center mx-auto">
            <a href="/" style="text-decoration: none;">
                @include('partials.logo')
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-4 md:px-8 py-10 mb-8">
        <div class="bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] w-full max-w-[600px] min-h-[500px] overflow-hidden mx-auto">
            
            <!-- Form Section -->
            <div class="w-full p-8 md:p-12 flex flex-col justify-center relative">
                
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold text-sm text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="partnerServicesForm" action="{{ route('register.partner.services.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="text-right mb-6">
                        <h2 class="text-2xl font-black text-[#1C1C1C] mb-2">← حدد خدماتك</h2>
                        <p class="text-[#64748B] text-[0.95rem] font-semibold leading-relaxed">
                            تقدر تختار أكثر من خدمة حسب نطاق عملك.
                        </p>
                    </div>


                    <!-- Services Custom Checkbox cards -->
                    <div class="services-list-container space-y-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 20px; padding: 1.25rem;">
                        
                        @forelse($services as $service)
                            <label class="service-checkbox-card" style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #E2E8F0; padding: 1rem 1.25rem; border-radius: 14px; cursor: pointer; transition: all 0.3s ease;">
                                <span style="font-size: 0.95rem; font-weight: 700; color: #475569;">{{ $service->title }}</span>
                                <div class="checkbox-wrapper" style="position: relative; width: 22px; height: 22px;">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}" class="service-input" style="display: none;" onchange="updateServiceCard(this)">
                                    <div class="custom-checkbox" style="width: 22px; height: 22px; border: 2px solid #CBD5E1; border-radius: 6px; background: white; transition: all 0.2s; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-check text-white text-xs" style="display: none;"></i>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <p class="text-[#64748B] font-bold text-sm text-center py-4">لا توجد خدمات متاحة لاهتماماتك المحددة حالياً.</p>
                        @endforelse

                    </div>

                    <!-- Submit & Previous buttons -->
                    <div style="display: flex; gap: 1rem; width: 100%;">
                        <button type="submit" id="submitBtn" disabled class="w-full py-4 mt-4 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-lg font-bold transition-all" style="flex: 2; opacity: 0.5; cursor: not-allowed;">
                            حفظ ومتابعة
                        </button>
                        <a href="{{ route('register.partner.interests') }}" class="btn btn-outline" style="flex: 1; padding: 1.1rem; border-radius: 16px; font-weight: 700; background: white; border: 2px solid #E2E8F0; color: #64748B; cursor: pointer; margin-top: 1rem; text-align: center; text-decoration: none;">
                            السابق
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="login-footer py-6 text-center mt-auto w-full">
        <div class="container px-8 flex flex-col md:flex-row justify-between items-center mx-auto text-white text-sm font-medium">
            <p class="mb-2 md:mb-0" dir="ltr">©2025 copyright</p>
            <div style="display: flex; gap: 1.5rem;">
                <a href="/terms-conditions" target="_blank" class="hover:underline text-white">الشروط والأحكام</a>
                <a href="/privacy-policy" target="_blank" class="hover:underline text-white">سياسة الخصوصية</a>
            </div>
        </div>
    </footer>

    <script>

        function updateServiceCard(checkbox) {
            const card = checkbox.closest('.service-checkbox-card');
            if (checkbox.checked) {
                card.classList.add('checked');
            } else {
                card.classList.remove('checked');
            }
            updateSubmitButton();
        }

        function updateSubmitButton() {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                const checkedCount = document.querySelectorAll('input[name="services[]"]:checked').length;
                if (checkedCount > 0) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                } else {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                }
            }
        }
    </script>
</body>
</html>
