<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد حساب الشريك - منصة تمت</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome for beautiful icons -->
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
        .step-section {
            transition: all 0.4s ease;
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
        .step-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #E2E8F0;
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background: #1A73E8;
            transform: scale(1.2);
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
    <main class="flex-grow flex items-center justify-center px-8 lg:px-16 py-10 mb-8">
        <div class="bg-[#F7F7F7] rounded-md shadow-[0_10px_40px_rgba(0,0,0,0.08)] flex flex-col lg:flex-row w-full max-w-[1050px] min-h-[650px] overflow-hidden mx-auto">
            
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
            <div class="w-full bg-white lg:w-1/2 p-8 lg:px-16 flex flex-col justify-center relative">
                
                <!-- Steps Progress Indicator -->
                <div style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 2rem;">
                    <div class="step-indicator" id="ind-2"></div>
                    <div class="step-indicator active" id="ind-1"></div>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-bold text-sm text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="partnerSetupForm" action="{{ route('register.partner.setup.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- ============================================== -->
                    <!-- STEP 1: Categories / Interests Selection -->
                    <!-- ============================================== -->
                    <div id="step-1" class="step-section space-y-4">
                        <div class="text-right mb-6">
                            <h2 class="text-2xl font-black text-[#1C1C1C] mb-2">وش الفئات اللي تهمك؟</h2>
                            <p class="text-[#64748B] text-[0.95rem] font-semibold leading-relaxed">
                                اختر الفئات التي ترغب في العمل بها وتقديم خدماتك فيها. (يجب اختيار فئة واحدة على الأقل).
                            </p>
                        </div>

                        <!-- Categories Grid -->
                        <div class="interests-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem;">
                            @foreach($categories as $category)
                                <div class="interest-item" onclick="toggleInterest(this)">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" style="display: none;" onchange="updateStep1Button()">
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

                        <!-- Next Button -->
                        <button type="button" id="step1NextBtn" onclick="goToStep(2)" disabled style="width: 100%; padding: 1rem; border-radius: 16px; font-weight: 800; font-size: 1.1rem; background: #1A73E8; border: none; color: white; cursor: not-allowed; opacity: 0.5; pointer-events: none; transition: all 0.3s ease;">
                            الخطوة التالية: تحديد الخدمات
                        </button>
                    </div>

                    <!-- ============================================== -->
                    <!-- STEP 2: Services Selection -->
                    <!-- ============================================== -->
                    <div id="step-2" class="step-section space-y-4" style="display: none;">
                        <div class="text-right mb-6">
                            <h2 class="text-2xl font-black text-[#1C1C1C] mb-2">حدد خدماتك كشريك</h2>
                            <p class="text-[#64748B] text-[0.95rem] font-semibold leading-relaxed">
                                تقدر تختار أكثر من خدمة حسب نطاق عملك.
                            </p>
                        </div>

                        <!-- Dropdown Select for Services -->
                        <div class="relative text-right mb-4">
                            <label class="block text-sm font-bold text-[#1C1C1C] mb-2" for="servicesDropdown">حدد الخدمات</label>
                            <div class="relative">
                                <select id="servicesDropdown" class="w-full p-4 border border-[#E2E8F0] rounded-xl font-bold bg-[#F8FAFC] focus:outline-none focus:border-[#1A73E8] focus:ring-1 focus:ring-[#1A73E8] text-[#475569] appearance-none" style="padding-left: 2.5rem;" onchange="selectServiceFromDropdown(this.value)">
                                    <option value="" disabled selected>حدد الخدمات</option>
                                    <option value="خدمة الفحص">خدمة الفحص</option>
                                    <option value="خدمة الشحن">خدمة الشحن</option>
                                    <option value="خدمة الشراء">خدمة الشراء</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-[#94A3B8]" style="left: 0.5rem; right: auto;">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Services Custom Checkbox cards -->
                        <div class="services-list-container space-y-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 20px; padding: 1.25rem;">
                            
                            <!-- Service 1: Inspection -->
                            <label class="service-checkbox-card" style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #E2E8F0; padding: 1rem 1.25rem; border-radius: 14px; cursor: pointer; transition: all 0.3s ease;">
                                <span style="font-size: 0.95rem; font-weight: 700; color: #475569;">خدمة الفحص</span>
                                <div class="checkbox-wrapper" style="position: relative; width: 22px; height: 22px;">
                                    <input type="checkbox" name="services[]" value="خدمة الفحص" class="service-input" style="display: none;" onchange="updateServiceCard(this)">
                                    <div class="custom-checkbox" style="width: 22px; height: 22px; border: 2px solid #CBD5E1; border-radius: 6px; background: white; transition: all 0.2s; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-check text-white text-xs" style="display: none;"></i>
                                    </div>
                                </div>
                            </label>

                            <!-- Service 2: Shipping -->
                            <label class="service-checkbox-card" style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #E2E8F0; padding: 1rem 1.25rem; border-radius: 14px; cursor: pointer; transition: all 0.3s ease;">
                                <span style="font-size: 0.95rem; font-weight: 700; color: #475569;">خدمة الشحن</span>
                                <div class="checkbox-wrapper" style="position: relative; width: 22px; height: 22px;">
                                    <input type="checkbox" name="services[]" value="خدمة الشحن" class="service-input" style="display: none;" onchange="updateServiceCard(this)">
                                    <div class="custom-checkbox" style="width: 22px; height: 22px; border: 2px solid #CBD5E1; border-radius: 6px; background: white; transition: all 0.2s; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-check text-white text-xs" style="display: none;"></i>
                                    </div>
                                </div>
                            </label>

                            <!-- Service 3: Purchase -->
                            <label class="service-checkbox-card" style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #E2E8F0; padding: 1rem 1.25rem; border-radius: 14px; cursor: pointer; transition: all 0.3s ease;">
                                <span style="font-size: 0.95rem; font-weight: 700; color: #475569;">خدمة الشراء</span>
                                <div class="checkbox-wrapper" style="position: relative; width: 22px; height: 22px;">
                                    <input type="checkbox" name="services[]" value="خدمة الشراء" class="service-input" style="display: none;" onchange="updateServiceCard(this)">
                                    <div class="custom-checkbox" style="width: 22px; height: 22px; border: 2px solid #CBD5E1; border-radius: 6px; background: white; transition: all 0.2s; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-check text-white text-xs" style="display: none;"></i>
                                    </div>
                                </div>
                            </label>

                        </div>

                        <!-- Submit & Previous buttons -->
                        <div style="display: flex; gap: 1rem; width: 100%;">
                            <button type="submit" id="setupSubmitBtn" disabled class="w-full py-4 mt-4 bg-[#1A73E8] hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-lg font-bold transition-all" style="flex: 2; opacity: 0.5; cursor: not-allowed;">
                                تفعيل عبر نفاذ
                            </button>
                            <button type="button" onclick="goToStep(1)" style="flex: 1; padding: 1.1rem; border-radius: 16px; font-weight: 700; background: white; border: 2px solid #E2E8F0; color: #64748B; cursor: pointer; margin-top: 1rem;">
                                السابق
                            </button>
                        </div>
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
        // Multi-Step Form Logic
        let activeStep = 1;

        function goToStep(step) {
            // Validation before proceeding
            if (step === 2 && activeStep === 1) {
                const checkedCount = document.querySelectorAll('input[name="category_ids[]"]:checked').length;
                if (checkedCount === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'تنبيه',
                        text: 'يجب اختيار فئة واحدة على الأقل لتتمكن من تقديم خدماتك فيها.',
                        confirmButtonColor: '#1A73E8',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }
            }

            // Perform Transition
            document.getElementById(`step-${activeStep}`).style.display = 'none';
            document.getElementById(`ind-${activeStep}`).classList.remove('active');
            
            activeStep = step;
            
            document.getElementById(`step-${activeStep}`).style.display = 'block';
            document.getElementById(`ind-${activeStep}`).classList.add('active');
        }

        // Interests Toggle in Step 1
        function toggleInterest(element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('selected');
            
            // Toggle opacity of check icon
            const badge = element.querySelector('.check-badge');
            if (checkbox.checked) {
                badge.style.opacity = '1';
            } else {
                badge.style.opacity = '0';
            }

            updateStep1Button();
        }

        function updateStep1Button() {
            const nextBtn = document.getElementById('step1NextBtn');
            if (nextBtn) {
                const checkedCount = document.querySelectorAll('input[name="category_ids[]"]:checked').length;
                if (checkedCount > 0) {
                    nextBtn.disabled = false;
                    nextBtn.style.opacity = '1';
                    nextBtn.style.cursor = 'pointer';
                    nextBtn.style.pointerEvents = 'auto';
                } else {
                    nextBtn.disabled = true;
                    nextBtn.style.opacity = '0.5';
                    nextBtn.style.cursor = 'not-allowed';
                    nextBtn.style.pointerEvents = 'none';
                }
            }
        }

        function selectServiceFromDropdown(serviceValue) {
            if (!serviceValue) return;
            
            const checkboxes = document.querySelectorAll('input[name="services[]"]');
            checkboxes.forEach(checkbox => {
                if (checkbox.value === serviceValue) {
                    checkbox.checked = !checkbox.checked;
                    updateServiceCard(checkbox);
                }
            });
            
            document.getElementById('servicesDropdown').value = "";
        }

        // Services Toggle in Step 2
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
            const submitBtn = document.getElementById('setupSubmitBtn');
            if (submitBtn) {
                const checkedCount = document.querySelectorAll('input[name="services[]"]:checked').length;
                if (checkedCount > 0) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                }
            }
        }
    </script>
</body>
</html>
