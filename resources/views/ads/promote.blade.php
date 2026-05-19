@extends('layouts.main')

@section('title', 'ميز إعلانك | مزاد')

@push('styles')
<style>
    .promote-ads-page {
        background-color: #ffffff;
        min-height: 80vh;
        padding: 3rem 0;
        direction: rtl;
        text-align: right;
    }

    .breadcrumbs-pill {
        background-color: #1A73E8;
        border-radius: 12px;
        color: white !important;
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none !important;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
        transition: all 0.2s ease;
    }
    .breadcrumbs-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26, 115, 232, 0.25);
    }

    .promote-header-card {
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
    }

    .select-all-wrapper {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: #1E293B;
        cursor: pointer;
    }

    .select-all-checkbox {
        width: 22px;
        height: 22px;
        cursor: pointer;
        accent-color: #1A73E8;
    }

    .selection-counter {
        font-size: 0.95rem;
        font-weight: 700;
        color: #64748B;
    }

    /* Selected state outline for cards */
    .p-ad-card {
        border: 1.5px solid #E2E8F0 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .p-ad-card.selected {
        border-color: #1A73E8 !important;
        box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.15) !important;
        background-color: #F0F7FF !important;
    }

    /* Floating checkout footer */
    .promote-action-footer {
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 1.5rem;
        margin-top: 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    }

    .promote-submit-btn {
        background-color: #1A73E8;
        color: white;
        border: none;
        border-radius: 14px;
        padding: 0.8rem 2rem;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .promote-submit-btn:hover {
        background-color: #1557B0;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(26, 115, 232, 0.3);
    }
    .promote-submit-btn:active {
        transform: translateY(0);
    }

    .promote-price-summary {
        text-align: right;
    }
    .promote-price-summary span {
        display: block;
    }
    .promote-price-summary .summary-label {
        font-size: 0.85rem;
        color: #64748B;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .promote-price-summary .summary-total {
        font-size: 1.5rem;
        font-weight: 900;
        color: #1A73E8;
    }

    @media (max-width: 768px) {
        .promote-header-card, .promote-action-footer {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            text-align: center;
        }
        .select-all-wrapper {
            justify-content: center;
        }
        .promote-price-summary {
            text-align: center;
        }
        .promote-submit-btn {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="promote-ads-page">
    <div class="container mx-auto px-4 lg:px-16">
        
        <!-- Breadcrumbs -->
        <a href="{{ route('ads.my') }}" class="breadcrumbs-pill">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة إلى إعلاناتي</span>
        </a>

        <!-- Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-[#1E293B] mb-2">ميز إعلانك</h1>
            <p class="text-gray-500 font-semibold">اختر الإعلانات التي ترغب في تمييزها للظهور في المقدمة وزيادة نسبة المبيعات.</p>
        </div>

        @if($ads->count() > 0)
            <form id="bulkPromoteForm" action="{{ route('ads.promote-bulk') }}" method="POST">
                @csrf
                
                <!-- Promote Header Card -->
                <div class="promote-header-card">
                    <label class="select-all-wrapper">
                        <input type="checkbox" id="selectAllAds" class="select-all-checkbox" onchange="toggleSelectAll(this)">
                        <span>تحديد الكل</span>
                    </label>
                    <span class="selection-counter" id="selectionStatus">
                        تم تحديد <strong class="text-primary font-black" id="selectedCount">0</strong> من <strong class="font-black">{{ $ads->count() }}</strong> إعلان
                    </span>
                </div>

                <!-- Ads Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($ads as $ad)
                        <div class="ad-card-wrapper" data-ad-id="{{ $ad->id }}">
                            @include('partials.ad-card', ['ad' => $ad, 'showSelectionCheckbox' => true])
                        </div>
                    @endforeach
                </div>

                <!-- Floating Checkout Footer -->
                <div class="promote-action-footer">
                    <div class="promote-price-summary">
                        <span class="summary-label">التكلفة الإجمالية</span>
                        <span class="summary-total flex items-center gap-1">
                            <span id="totalAmount">0</span>
                            <img src="{{ asset('images/Vector-blue.svg') }}" class="w-5 h-5">
                        </span>
                    </div>
                    
                    <button type="submit" class="promote-submit-btn">
                        <i class="fa-solid fa-rectangle-ad text-lg"></i>
                        <span>ميز إعلاناتك</span>
                    </button>
                </div>
            </form>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4 bg-slate-50 rounded-[32px] border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-rectangle-ad text-4xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">لا توجد إعلانات مؤهلة للتمييز حالياً</h3>
                <p class="text-slate-500 font-medium max-w-md mx-auto mb-6">جميع إعلاناتك الحالية مميزة بالفعل، أو لا تملك إعلانات قائمة أو مستقبلية نشطة حالياً.</p>
                <a href="{{ route('ads.create') }}" class="btn btn-p-primary inline-flex items-center gap-2" style="background-color: #1A73E8; color: white; border-radius: 12px; padding: 0.6rem 1.2rem; font-weight: 700; text-decoration: none;">
                    <i class="fa-solid fa-plus"></i>
                    <span>أنشئ إعلانًا جديدًا</span>
                </a>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    const promotionPricePerAd = {{ $promotionPrice }};

    document.addEventListener('DOMContentLoaded', function() {
        // Setup direct click on checkbox labels or card toggles
        const checkboxes = document.querySelectorAll('.ad-bulk-checkbox');
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const card = this.closest('.p-ad-card');
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
                updateCounters();
            });
            
            // If the card is clicked anywhere, do NOT trigger checkbox unless specified, but let's allow clicking the checkbox directly
            const selectContainer = cb.closest('.ad-select-checkbox-container');
            if (selectContainer) {
                selectContainer.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        // Form Submit Handler
        const form = document.getElementById('bulkPromoteForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const selectedCount = document.querySelectorAll('.ad-bulk-checkbox:checked').length;
                if (selectedCount === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'تنبيه',
                        text: 'يرجى تحديد إعلان واحد على الأقل لتمييزه!',
                        confirmButtonText: 'حسناً',
                        confirmButtonColor: '#1A73E8'
                    });
                }
            });
        }
    });

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.ad-bulk-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
            const card = cb.closest('.p-ad-card');
            if (masterCheckbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
        updateCounters();
    }

    function updateCounters() {
        const checkboxes = document.querySelectorAll('.ad-bulk-checkbox');
        const checkedCount = document.querySelectorAll('.ad-bulk-checkbox:checked').length;
        
        // Update Count
        const countSpan = document.getElementById('selectedCount');
        if (countSpan) countSpan.innerText = checkedCount;

        // Update Total Price
        const totalSpan = document.getElementById('totalAmount');
        if (totalSpan) {
            const total = checkedCount * promotionPricePerAd;
            totalSpan.innerText = total.toLocaleString('en-US');
        }

        // Sync Master Checkbox
        const masterCb = document.getElementById('selectAllAds');
        if (masterCb) {
            masterCb.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
        }
    }
</script>
@endpush
