<!-- Interests Modal -->
<div id="interestsModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 10001; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="modal-content" style="background: white; padding: 2rem; border-radius: 24px; width: 90%; max-width: 550px; position: relative; text-align: center; direction: rtl; box-shadow: 0 20px 50px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;">
        <button class="modal-close" onclick="closeInterestsModal()" style="position: absolute; top: 1.2rem; left: 1.2rem; background: none; border: none; font-size: 1.5rem; color: #94A3B8; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        
        <div style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #1E293B; margin-bottom: 0.5rem;">وش الفئات اللي تهمك؟</h2>
            <p style="font-size: 0.95rem; color: #64748B; line-height: 1.6;">اختر الفئات اللي حاب تتابعها أول بأول، ونخلي باقي الفئات متاحة لك متى ما حبيت تبحث عنها.</p>
        </div>

        <div class="interests-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2.5rem;">
            @foreach($discoveryCategories as $category)
                <div class="interest-item" onclick="toggleInterest(this)" data-id="{{ $category->id }}" style="cursor: pointer; border: 2px solid transparent; border-radius: 16px; padding: 0.75rem; background: #F8FAFC; transition: all 0.2s ease;">
                    <div style="width: 100%; aspect-ratio: 1; background: white; border-radius: 12px; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                        <img src="{{ $category->image ? asset($category->image) : asset('images/category-placeholder.png') }}" alt="{{ $category->name }}" style="width: 80%; height: 80%; object-fit: contain;">
                    </div>
                    <span style="font-size: 0.875rem; font-weight: 700; color: #1E293B;">{{ $category->name }}</span>
                    <input type="checkbox" name="interests[]" value="{{ $category->id }}" hidden>
                </div>
            @endforeach
        </div>

        <button onclick="saveInterests()" class="btn-primary" style="width: 100%; padding: 1rem; border-radius: 12px; font-weight: 800; font-size: 1.125rem; border: none; cursor: pointer; background: #1A73E8; color: white; transition: all 0.3s shadow-lg shadow-blue-500/25;">متابعة</button>
    </div>
</div>

<style>
    .interest-item.active {
        background: #F0F4FF !important;
        border-color: #1A73E8 !important;
    }
    .interest-item.active span {
        color: #1A73E8 !important;
    }
</style>

<script>
    function openInterestsModal() {
        document.getElementById('interestsModal').style.display = 'flex';
    }

    function closeInterestsModal() {
        document.getElementById('interestsModal').style.display = 'none';
    }

    function toggleInterest(element) {
        element.classList.toggle('active');
        const checkbox = element.querySelector('input');
        checkbox.checked = !checkbox.checked;
    }

    function saveInterests() {
        // Here you would normally send an AJAX request to save interests
        // For now, we just close the modal
        closeInterestsModal();
    }

    // Automatically open if needed (e.g. after registration)
    @if(session()->pull('show_interests'))
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(openInterestsModal, 1000);
        });
    @endif
</script>
