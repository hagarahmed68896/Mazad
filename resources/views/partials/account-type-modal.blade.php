<!-- Account Type Modal -->
<div id="accountTypeModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="modal-content account-modal" style="background: white; padding: 2.5rem; border-radius: 24px; width: 90%; max-width: 500px; position: relative; text-align: right; direction: rtl; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
        <button class="modal-close" onclick="closeAccountModal()" style="position: absolute; top: 1.5rem; left: 1.5rem; right: auto !important; background: none; border: none; font-size: 1.5rem; color: #94A3B8; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        
        <div class="account-modal-header" style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #1E293B; margin-bottom: 0.5rem;">اختر نوع حسابك</h2>
            <p style="font-size: 0.95rem; color: #64748B;">حدد نوع الحساب المناسب لك لتجربة تناسب استخدامك للمنصة.</p>
        </div>

        <div class="account-types" style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
            <label class="account-type-card active" onclick="selectAccountType(this, 'individual')" style="display: flex; align-items: center; justify-content: space-between; background: #F0F4FF; border: 2px solid #1A73E8; padding: 1rem 1.25rem; border-radius: 16px; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-content" style="display: flex; align-items: center; gap: 1rem;">
                    <div class="card-icon" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                        <span style="font-size: 2.5rem;">👨🏻‍💻</span>
                    </div>
                    <div class="card-text" style="text-align: right;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem;">حساب فرد</h3>
                        <p style="font-size: 0.85rem; color: #64748B; line-height: 1.4; margin: 0;">للاستخدام الشخصي والمشاركة في المزادات بكل سهولة.</p>
                    </div>
                </div>
                <div class="radio-btn" style="font-size: 1.25rem; color: #1A73E8;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <input type="radio" name="account_type" value="individual" checked hidden>
            </label>

            <label class="account-type-card" onclick="selectAccountType(this, 'company')" style="display: flex; align-items: center; justify-content: space-between; background: #F8FAFC; border: 2px solid transparent; padding: 1rem 1.25rem; border-radius: 16px; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-content" style="display: flex; align-items: center; gap: 1rem;">
                    <div class="card-icon" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                        <span style="font-size: 2.5rem;">👨🏻‍💼</span>
                    </div>
                    <div class="card-text" style="text-align: right;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem;">حساب شركة</h3>
                        <p style="font-size: 0.85rem; color: #64748B; line-height: 1.4; margin: 0;">مناسب للشركات اللي تدير أكثر من مندوب وحساب مالي موحد.</p>
                    </div>
                </div>
                <div class="radio-btn" style="font-size: 1.25rem; color: #CBD5E1;">
                    <i class="fa-regular fa-circle"></i>
                </div>
                <input type="radio" name="account_type" value="company" hidden>
            </label>

            <label class="account-type-card" onclick="selectAccountType(this, 'partner')" style="display: flex; align-items: center; justify-content: space-between; background: #F8FAFC; border: 2px solid transparent; padding: 1rem 1.25rem; border-radius: 16px; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-content" style="display: flex; align-items: center; gap: 1rem;">
                    <div class="card-icon" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                        <span style="font-size: 2.5rem;">🤝🏼</span>
                    </div>
                    <div class="card-text" style="text-align: right;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem;">شريك</h3>
                        <p style="font-size: 0.85rem; color: #64748B; line-height: 1.4; margin: 0;">للجهات المقدمة لخدمات مرتبطة بالمزادات مثل فحص السيارات.</p>
                    </div>
                </div>
                <div class="radio-btn" style="font-size: 1.25rem; color: #CBD5E1;">
                    <i class="fa-regular fa-circle"></i>
                </div>
                <input type="radio" name="account_type" value="partner" hidden>
            </label>
        </div>

        <div class="account-modal-actions" style="display: flex; gap: 1rem;">
            <button onclick="proceedToRegister()" class="btn btn-primary" style="flex: 1; padding: 0.8rem; background: #1A73E8; color: white; border: none; border-radius: 12px; font-size: 1.2rem; font-weight: 700; cursor: pointer;">إنشاء الحساب</button>
            <a href="{{ route('login') }}" class="btn btn-outline-primary" style="flex: 1; padding: 0.8rem; background: white; border: 2px solid #1A73E8; color: #1A73E8; border-radius: 12px; font-size: 1rem; font-weight: 700; text-align: center; text-decoration: none;">تسجيل الدخول</a>
        </div>
    </div>
</div>

<script>
    let selectedAccountType = 'individual';

    function openAccountModal() {
        document.getElementById('accountTypeModal').style.display = 'flex';
    }

    function closeAccountModal() {
        document.getElementById('accountTypeModal').style.display = 'none';
    }

    function selectAccountType(element, type) {
        // Remove active from all
        document.querySelectorAll('.account-type-card').forEach(card => {
            card.style.background = '#F8FAFC';
            card.style.borderColor = 'transparent';
            const radio = card.querySelector('.radio-btn i');
            radio.classList.replace('fa-circle-check', 'fa-circle');
            radio.classList.replace('fa-solid', 'fa-regular');
            radio.style.color = '#CBD5E1';
            card.querySelector('input').checked = false;
        });

        // Add active to selected
        element.style.background = '#F0F4FF';
        element.style.borderColor = '#1A73E8';
        const radio = element.querySelector('.radio-btn i');
        radio.classList.replace('fa-circle', 'fa-circle-check');
        radio.classList.replace('fa-regular', 'fa-solid');
        radio.style.color = '#1A73E8';
        element.querySelector('input').checked = true;

        selectedAccountType = type;
    }

    function proceedToRegister() {
        if (selectedAccountType === 'individual') {
            window.location.href = "{{ route('register.individual') }}";
        } else if (selectedAccountType === 'company') {
            window.location.href = "{{ route('register.company') }}";
        } else {
            // For partner, you can add its specific route or use generic
            window.location.href = "{{ route('register.type') }}";
        }
    }

    // Close on overlay click
    document.getElementById('accountTypeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAccountModal();
        }
    });
</script>
