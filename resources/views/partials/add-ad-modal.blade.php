<!-- Add Ad Modal -->
<div id="addAdModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="modal-content add-ad-modal" style="background: white; padding: 2.5rem; border-radius: 24px; width: 90%; max-width: 550px; position: relative; text-align: right; direction: rtl; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
        <button class="modal-close" onclick="closeAddAdModal()" style="position: absolute; top: 1.5rem; left: 1.5rem; right: auto !important; background: none; border: none; font-size: 1.5rem; color: #94A3B8; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        
        <div class="modal-header" style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #1E293B; margin-bottom: 0.5rem;">ماذا تريد أن تضيف؟</h2>
            <p style="font-size: 0.95rem; color: #64748B;">اختر نوع الإعلان الذي ترغب في نشره على المنصة.</p>
        </div>

        <div class="ad-types" style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
            <!-- New Ad -->
            <div class="ad-type-card" onclick="selectAdType('standard')" style="display: flex; align-items: center; justify-content: space-between; background: #F8FAFC; border: 2px solid transparent; padding: 1.25rem; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-content" style="display: flex; align-items: center; gap: 1.25rem;">
                    <div class="card-icon" style="width: 56px; height: 56px; background: #F0F9FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                        <span>📢</span>
                    </div>
                    <div class="card-text" style="text-align: right;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem;">إعلان جديد</h3>
                        <p style="font-size: 0.85rem; color: #64748B; line-height: 1.5; margin: 0;">أضف إعلانك كبيع مباشر أو مزاد، وحدد التفاصيل لاحقاً.</p>
                    </div>
                </div>
                <div class="arrow-icon" style="color: #CBD5E1; font-size: 1.2rem;">
                    <i class="fa-solid fa-chevron-left"></i>
                </div>
            </div>

            <!-- Tender Ad -->
            <div class="ad-type-card" onclick="selectAdType('tender')" style="display: flex; align-items: center; justify-content: space-between; background: #F8FAFC; border: 2px solid transparent; padding: 1.25rem; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-content" style="display: flex; align-items: center; gap: 1.25rem;">
                    <div class="card-icon" style="width: 56px; height: 56px; background: #F0FDF4; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                        <span>📋</span>
                    </div>
                    <div class="card-text" style="text-align: right;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem;">إضافة مناقصة</h3>
                        <p style="font-size: 0.85rem; color: #64748B; line-height: 1.5; margin: 0;">أضف مناقصة جديدة لاستقبال عروض الأسعار من الموردين.</p>
                    </div>
                </div>
                <div class="arrow-icon" style="color: #CBD5E1; font-size: 1.2rem;">
                    <i class="fa-solid fa-chevron-left"></i>
                </div>
            </div>
        </div>

        <div style="text-align: center;">
            <p style="font-size: 0.85rem; color: #94A3B8;">بنشرك للإعلان، أنت توافق على <a href="#" style="color: #1A73E8; font-weight: 700; text-decoration: none;">شروط وأحكام</a> المنصة.</p>
        </div>
    </div>
</div>

<style>
    .ad-type-card:hover {
        background: white !important;
        border-color: #1A73E8 !important;
        box-shadow: 0 10px 20px rgba(26, 115, 232, 0.08);
        transform: translateY(-2px);
    }
    .ad-type-card:hover .arrow-icon {
        color: #1A73E8 !important;
        transform: translateX(-5px);
    }
    .ad-type-card .arrow-icon {
        transition: all 0.3s ease;
    }
</style>

<script>
    function openAddAdModal() {
        if (!{{ auth()->check() ? 'true' : 'false' }}) {
            showAuthModal();
            return;
        }
        document.getElementById('addAdModal').style.display = 'flex';
    }

    function closeAddAdModal() {
        document.getElementById('addAdModal').style.display = 'none';
    }

    function selectAdType(type) {
        // Here you redirect to the specific page based on choice
        if (type === 'standard') {
            window.location.href = "/ads/create?type=standard";
        } else if (type === 'tender') {
            window.location.href = "/ads/create?type=tender";
        }
        closeAddAdModal();
    }

    // Close on overlay click
    document.getElementById('addAdModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddAdModal();
        }
    });
</script>
