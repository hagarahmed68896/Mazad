@extends('layouts.main')

@section('title', 'إعدادات الإشعارات | تمت')

@push('styles')
<style>
    /* Modern Custom Switch Toggles */
    .switch-container {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
    }
    
    .switch-container input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #E2E8F0;
        transition: .3s;
        border-radius: 34px;
    }
    
    .switch-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    input:checked + .switch-slider {
        background-color: #1A73E8;
    }
    
    input:checked + .switch-slider:before {
        transform: translateX(22px);
    }

    /* Percentage selectors */
    .percentage-btn {
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #E2E8F0;
        background-color: #F8FAFC;
    }
    
    .percentage-btn:hover {
        border-color: #CBD5E1;
        background-color: #F1F5F9;
    }
    
    .percentage-btn.active {
        border-color: #1A73E8 !important;
        background-color: #E8F0FE !important;
        color: #1A73E8 !important;
        font-weight: 800;
        box-shadow: 0 4px 10px -2px rgba(26, 115, 232, 0.15);
    }

    /* Elegant setting card hover */
    .setting-card {
        transition: all 0.3s ease;
    }
    .setting-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04);
        border-color: #E2E8F0;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="container mx-auto px-4 lg:px-12 max-w-4xl">
        
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-sm font-bold text-gray-500 mb-6 justify-start" dir="rtl">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span class="text-white bg-primary px-3 py-1 rounded-full text-xs">إعدادات الإشعارات</span>
        </div>

        <!-- Main Container -->
        <div class="bg-white rounded-[32px] p-6 md:p-10 shadow-sm border border-gray-100" dir="rtl">
            
            <!-- Page Header with Master Toggle -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-gray-100 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">إعدادات الإشعارات</h1>
                    <p class="text-gray-500 text-xs md:text-sm font-medium">تحكم في التنبيهات التي ترغب في تلقيها على بريدك وجوالك.</p>
                </div>
                
                <!-- Master Switch -->
                <div class="flex items-center gap-3 bg-gray-50 px-5 py-3 rounded-2xl border border-gray-100 select-none">
                    <span class="text-sm font-extrabold text-gray-700">تحكم في جميع الإشعارات</span>
                    <label class="switch-container">
                        <input type="checkbox" id="master-toggle" {{ $user->all_notifications ? 'checked' : '' }}>
                        <span class="switch-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Form -->
            <form id="notifications-form" action="{{ route('profile.notifications.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Hidden inputs to submit values -->
                <input type="hidden" name="all_notifications" id="all_notifications_val" value="{{ $user->all_notifications ? 'on' : 'off' }}">
                <input type="hidden" name="bid_approaching_limit_percentage" id="bid_approaching_limit_percentage_val" value="{{ $user->bid_approaching_limit_percentage ?? 70 }}">

                <!-- Setting Cards List -->
                <div class="space-y-4 mb-8">
                    
                    <!-- 1. إشعارات المزايدات -->
                    <div class="setting-card bg-white rounded-2xl p-5 border border-gray-100 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-extrabold text-gray-900 mb-1">إشعارات المزايدات</h3>
                            <p class="text-gray-500 text-xs md:text-sm font-medium">تنبيه عند وجود عروض جديدة على مزادات تتابعها</p>
                        </div>
                        <label class="switch-container">
                            <input type="checkbox" class="child-toggle" name="bid_notifications" {{ $user->bid_notifications ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 2. إشعار عند اقتراب المزايدة من الحد الأعلى -->
                    <div class="setting-card bg-white rounded-2xl p-5 border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-extrabold text-gray-900 mb-1">إشعار عند اقتراب المزايدة من الحد الأعلى</h3>
                            <p class="text-gray-500 text-xs md:text-sm font-medium mb-3 md:mb-0">يمكنك تحديد النسبة اللي ينبهك عندها (%)</p>
                            
                            <!-- Percentage button list -->
                            <div class="flex items-center gap-2 mt-2">
                                <button type="button" class="percentage-btn px-4 py-2 rounded-xl text-xs md:text-sm font-bold text-gray-600 rounded-xl transition-all {{ ($user->bid_approaching_limit_percentage ?? 70) == 70 ? 'active' : '' }}" data-value="70">70%</button>
                                <button type="button" class="percentage-btn px-4 py-2 rounded-xl text-xs md:text-sm font-bold text-gray-600 rounded-xl transition-all {{ ($user->bid_approaching_limit_percentage ?? 70) == 80 ? 'active' : '' }}" data-value="80">80%</button>
                                <button type="button" class="percentage-btn px-4 py-2 rounded-xl text-xs md:text-sm font-bold text-gray-600 rounded-xl transition-all {{ ($user->bid_approaching_limit_percentage ?? 70) == 90 ? 'active' : '' }}" data-value="90">90%</button>
                            </div>
                        </div>
                        <div class="flex justify-end items-center">
                            <label class="switch-container">
                                <input type="checkbox" class="child-toggle" name="bid_approaching_limit_notifications" {{ $user->bid_approaching_limit_notifications ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- 3. إشعار عند الوصول إلى الحد الأعلى -->
                    <div class="setting-card bg-white rounded-2xl p-5 border border-gray-100 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-extrabold text-gray-900 mb-1">إشعار عند الوصول إلى الحد الأعلى</h3>
                            <p class="text-gray-500 text-xs md:text-sm font-medium">يعلمك إذا وصلت المزايدة لحدك الأقصى للحد.</p>
                        </div>
                        <label class="switch-container">
                            <input type="checkbox" class="child-toggle" name="bid_reached_limit_notifications" {{ $user->bid_reached_limit_notifications ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 4. إشعارات متنوعة لتحديثات المزادات -->
                    <div class="setting-card bg-white rounded-2xl p-5 border border-gray-100 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-extrabold text-gray-900 mb-1">إشعارات متنوعة لتحديثات المزادات</h3>
                            <p class="text-gray-500 text-xs md:text-sm font-medium">بداية المزاد، انتهاء المزاد، أو تحديث في التفاصيل.</p>
                        </div>
                        <label class="switch-container">
                            <input type="checkbox" class="child-toggle" name="auction_update_notifications" {{ $user->auction_update_notifications ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 5. إشعارات للمزادات للمفضلة -->
                    <div class="setting-card bg-white rounded-2xl p-5 border border-gray-100 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-extrabold text-gray-900 mb-1">إشعارات للمزادات للمفضلة</h3>
                            <p class="text-gray-500 text-xs md:text-sm font-medium">تنبيه عند بداية أو قرب انتهاء المزادات للمفضلة.</p>
                        </div>
                        <label class="switch-container">
                            <input type="checkbox" class="child-toggle" name="favorite_auction_notifications" {{ $user->favorite_auction_notifications ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 6. إشعارات للمزادات الفائزة -->
                    <div class="setting-card bg-white rounded-2xl p-5 border border-gray-100 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-extrabold text-gray-900 mb-1">إشعارات للمزادات الفائزة</h3>
                            <p class="text-gray-500 text-xs md:text-sm font-medium">إعلامك فوراً إذا فزت باللإعلان أو المزاد.</p>
                        </div>
                        <label class="switch-container">
                            <input type="checkbox" class="child-toggle" name="winning_bid_notifications" {{ $user->winning_bid_notifications ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                </div>

                <!-- Submit and Cancel buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 pt-6 border-t border-gray-100 max-w-md mx-auto">
                    <button type="submit" class="w-full sm:flex-1 bg-primary hover:bg-blue-700 text-white font-black py-4 px-12 rounded-2xl transition-all text-sm md:text-base shadow-md shadow-blue-100">
                        حفظ التغييرات
                    </button>
                    <a href="{{ route('profile.index') }}" class="w-full sm:flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-4 px-8 rounded-2xl transition-all text-sm md:text-base flex items-center justify-center">
                        إلغاء
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const masterToggle = document.getElementById('master-toggle');
        const childToggles = document.querySelectorAll('.child-toggle');
        const allNotificationsVal = document.getElementById('all_notifications_val');
        const form = document.getElementById('notifications-form');
        
        // 1. Master Toggle behavior
        masterToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            allNotificationsVal.value = isChecked ? 'on' : 'off';
            
            childToggles.forEach(toggle => {
                toggle.checked = isChecked;
                // Trigger change event to keep UI updated if any listeners exist
                toggle.dispatchEvent(new Event('change'));
            });
        });

        // 2. Child Toggle behavior (updates master checkbox state)
        childToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                if (!this.checked) {
                    masterToggle.checked = false;
                    allNotificationsVal.value = 'off';
                } else {
                    // Check if all children are now checked
                    const allChecked = Array.from(childToggles).every(c => c.checked);
                    if (allChecked) {
                        masterToggle.checked = true;
                        allNotificationsVal.value = 'on';
                    }
                }
            });
        });

        // 3. Percentage buttons selection
        const percentageButtons = document.querySelectorAll('.percentage-btn');
        const percentageInput = document.getElementById('bid_approaching_limit_percentage_val');

        percentageButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                percentageButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                percentageInput.value = this.getAttribute('data-value');
            });
        });

        // 4. Smooth AJAX Saving (optional, fallback to form submit is preserved!)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Serialize form data
            const formData = new FormData(form);
            
            // Make sure explicit inputs for checkboxes are set
            childToggles.forEach(toggle => {
                if (toggle.checked) {
                    formData.set(toggle.name, 'on');
                } else {
                    formData.delete(toggle.name);
                }
            });

            // Show saving loading indicator
            Swal.fire({
                title: 'جاري الحفظ...',
                text: 'يرجى الانتظار للحظات',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: {
                    popup: 'rounded-[32px]'
                }
            });

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'نجاح',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rounded-[32px]'
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: data.message || 'حدث خطأ غير متوقع أثناء الحفظ.',
                        customClass: {
                            popup: 'rounded-[32px]'
                        }
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ السيرفر',
                    text: 'تعذر الاتصال بالخادم. يرجى المحاولة مرة أخرى.',
                    customClass: {
                        popup: 'rounded-[32px]'
                    }
                });
            });
        });
    });
</script>
@endpush
