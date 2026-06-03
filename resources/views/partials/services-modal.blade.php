<!-- Services Selection Modal for Partners -->
<div id="servicesModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 12000; align-items: center; justify-content: center; backdrop-filter: blur(8px); direction: rtl;">
    <div class="modal-content" style="background: white; padding: 2.5rem; border-radius: 32px; width: 95%; max-width: 580px; position: relative; text-align: right; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <!-- Modal Top Logo -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
            @include('partials.logo')
        </div>

        <!-- Back & Title -->
        <div class="services-header" style="margin-bottom: 2rem; display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: #1E293B;">
                <h2 style="font-size: 1.65rem; font-weight: 900; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-arrow-right" style="font-size: 1.25rem; cursor: pointer;" onclick="closeServicesModal()"></i>
                    <span>حدد خدماتك</span>
                </h2>
            </div>
            <p style="font-size: 0.95rem; color: #64748B; font-weight: 700; margin: 0; padding-right: 2rem;">تقدر تختار أكثر من خدمة حسب نطاق عملك.</p>
        </div>

        <form id="servicesForm" action="{{ route('save-services.web') }}" method="POST">
            @csrf
            
            <div class="form-group" style="margin-bottom: 2.5rem;">
                <label style="display: block; font-size: 0.95rem; font-weight: 800; color: #1E293B; margin-bottom: 0.75rem;">الخدمات</label>
                
                <!-- Fake select dropdown header -->
                <div style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #E2E8F0; padding: 1rem 1.25rem; border-radius: 16px; background: #F8FAFC; color: #94A3B8; font-weight: 700; font-size: 0.95rem; margin-bottom: 1rem;">
                    <span>حدد الخدمات</span>
                    <i class="fa-solid fa-chevron-down text-sm"></i>
                </div>

                <!-- Custom Checkboxes Container -->
                <div class="services-list-container" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 20px; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    
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
            </div>

            <!-- Modal Action -->
            <div style="margin-bottom: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 16px; font-weight: 800; font-size: 1.15rem; background: #1A73E8; border: none; color: white; cursor: pointer; transition: background 0.3s;">حفظ و متابعة</button>
            </div>
        </form>

        <!-- Modal Footer -->
        <div style="display: flex; align-items: center; justify-content: space-between; border-t: 1px solid #F1F5F9; padding-top: 1.25rem; font-size: 0.8rem; font-weight: 700; color: #94A3B8;">
            <div style="display: flex; gap: 1rem;">
                <a href="/terms-conditions" style="color: #94A3B8; text-decoration: none;" class="hover:text-primary transition-colors">الشروط والأحكام</a>
                <a href="/privacy-policy" style="color: #94A3B8; text-decoration: none;" class="hover:text-primary transition-colors">سياسة الخصوصية</a>
            </div>
            <span dir="ltr">©2025 copyright</span>
        </div>

    </div>
</div>

<style>
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

<script>
    function updateServiceCard(checkbox) {
        const card = checkbox.closest('.service-checkbox-card');
        if (checkbox.checked) {
            card.classList.add('checked');
        } else {
            card.classList.remove('checked');
        }
    }

    function openServicesModal() {
        document.getElementById('servicesModal').style.display = 'flex';
    }

    function closeServicesModal() {
        document.getElementById('servicesModal').style.display = 'none';
    }

    function checkForServicesModal() {
        const servicesModal = document.getElementById('servicesModal');
        if (servicesModal) {
            servicesModal.style.display = 'flex';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const interestsModal = document.getElementById('interestsModal');
        // If interests selection is NOT currently displayed, show services modal immediately
        if (!interestsModal || interestsModal.style.display === 'none') {
            openServicesModal();
        }
    });
</script>
