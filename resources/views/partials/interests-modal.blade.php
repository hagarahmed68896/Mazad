<!-- Interests Selection Modal -->
<div id="interestsModal" class="modal-overlay" style="display: {{ session('show_interests') ? 'flex' : 'none' }}; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); z-index: 11000; align-items: center; justify-content: center; backdrop-filter: blur(8px); direction: rtl;">
    <div class="modal-content" style="background: white; padding: 2rem; border-radius: 32px; width: 95%; max-width: 600px; max-height: 90vh; overflow-y: auto; position: relative; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <div class="interests-header" style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: #1E293B; margin-bottom: 0.75rem;">وش الفئات اللي تهمك؟</h2>
            <p style="font-size: 1rem; color: #64748B; line-height: 1.6;">اختر المزادات اللي حاب تتابعها أول بأول، ونخلي باقي الفئات متاحة لك متى ما حبيت تبحث عنها.</p>
        </div>

        <form id="interestsForm" action="{{ route('update-interests.web') }}" method="POST">
            @csrf
            <div class="interests-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2.5rem;">
                @foreach($categories as $category)
                    <div class="interest-item" onclick="toggleInterest(this, {{ $category->id }})" style="cursor: pointer; transition: all 0.3s ease; border: 2px solid #F1F5F9; border-radius: 20px; padding: 1rem; position: relative; background: #F8FAFC;">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" style="display: none;">
                        <div class="interest-icon" style="height: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                            <img src="{{ $category->image ? (Str::startsWith($category->image, 'uploads/') ? asset($category->image) : asset('uploads/' . $category->image)) : 'https://mazad-admin.com/uploads/categories/placeholder.png' }}" alt="{{ $category->name }}" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                        <span style="font-size: 0.9rem; font-weight: 700; color: #475569;">{{ $category->name }}</span>
                        <div class="check-badge" style="position: absolute; top: 0.5rem; left: 0.5rem; color: #1A73E8; opacity: 0; transition: opacity 0.2s;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="interests-actions" style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 1rem; border-radius: 16px; font-weight: 800; font-size: 1.1rem; background: #1A73E8; border: none; color: white; cursor: pointer;">متابعة</button>
                <button type="button" onclick="closeInterestsModal()" class="btn btn-outline" style="flex: 1; padding: 1rem; border-radius: 16px; font-weight: 700; background: white; border: 2px solid #E2E8F0; color: #64748B; cursor: pointer;">تخطي</button>
            </div>
        </form>
    </div>
</div>

<style>
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

<script>
    function toggleInterest(element, id) {
        const checkbox = element.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        element.classList.toggle('selected');
    }

    function closeInterestsModal() {
        fetch('{{ route("skip-interests.web") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).finally(() => {
            document.getElementById('interestsModal').style.display = 'none';
        });
    }

    // Auto-close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeInterestsModal();
    });
</script>
