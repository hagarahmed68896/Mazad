<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>وش الفئات اللي تهمك؟ - منصة مزاد</title>
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
        .interest-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #F1F5F9;
            border-radius: 20px;
            padding: 1rem;
            position: relative;
            background: #F8FAFC;
        }
        .interest-item.selected {
            border-color: #1A73E8 !important;
            background-color: #F0F7FF !important;
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.1);
        }
        .interest-item.selected .check-badge {
            opacity: 1 !important;
        }
        .interest-item.selected span {
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

                <form id="partnerInterestsForm" action="{{ route('register.partner.interests.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="text-right mb-6">
                        <h2 class="text-2xl font-black text-[#1C1C1C] mb-2">← حدد الفئة</h2>
                        <p class="text-[#64748B] text-[0.95rem] font-semibold leading-relaxed">
                            حدد نوع السلع التي تقدم لها خدماتك لتبدأ في استقبال الطلبات.
                        </p>
                    </div>

                    <!-- Categories Grid -->
                    <div class="interests-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2.5rem;">
                        @foreach($categories as $category)
                            <div class="interest-item" onclick="toggleInterest(this)">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" style="display: none;" onchange="updateSubmitButton()">
                                <div class="interest-icon" style="height: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                                    <img src="{{ $category->image }}" alt="{{ $category->name }}" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                                <span style="font-size: 0.85rem; font-weight: 800; color: #475569;">{{ $category->name }}</span>
                                <div class="check-badge" style="position: absolute; top: 0.5rem; left: 0.5rem; color: #1A73E8; opacity: 0; transition: opacity 0.2s;">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" disabled style="width: 100%; padding: 1rem; border-radius: 16px; font-weight: 800; font-size: 1.1rem; background: #1A73E8; border: none; color: white; cursor: not-allowed; opacity: 0.5; pointer-events: none; transition: all 0.3s ease;">
                        متابعة
                    </button>
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
        function toggleInterest(element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('selected');
            
            const badge = element.querySelector('.check-badge');
            if (checkbox.checked) {
                badge.style.opacity = '1';
            } else {
                badge.style.opacity = '0';
            }

            updateSubmitButton();
        }

        function updateSubmitButton() {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                const checkedCount = document.querySelectorAll('input[name="category_ids[]"]:checked').length;
                if (checkedCount > 0) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.style.pointerEvents = 'auto';
                } else {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                    btn.style.pointerEvents = 'none';
                }
            }
        }
    </script>
</body>
</html>
