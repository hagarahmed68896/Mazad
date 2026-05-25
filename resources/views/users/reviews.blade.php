@extends('layouts.main')

@section('title', 'تقييماتي | مزاد')

@push('styles')
<style>
    /* Premium reviews elements */
    .review-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.06);
    }
    
    /* Load more button transition */
    .load-more-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .load-more-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(26, 115, 232, 0.2);
    }
    .load-more-btn:active {
        transform: translateY(0);
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
            <span class="text-white bg-primary px-3 py-1 rounded-full text-xs">تقييماتي</span>
        </div>

        <!-- Main Wrapper Container -->
        <div class="bg-white rounded-[32px] p-6 md:p-10 shadow-sm border border-gray-100" dir="rtl">
            
            <!-- Page Header -->
            <div class="pb-6 border-b border-gray-100 mb-8 text-right">
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">تقييماتي</h1>
                <p class="text-gray-500 text-xs md:text-sm font-medium">التقييمات والآراء التي تلقيتها من المزايدين والمشترين الآخرين.</p>
            </div>

            <!-- Reviews Container -->
            <div id="reviews-container" class="space-y-4">
                @if($reviews->count() > 0)
                    @include('users.partials.reviews-list', ['reviews' => $reviews])
                @else
                    <!-- Empty State (Fallback, seeded on controller level as well) -->
                    <div class="text-center py-16">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <i class="fa-regular fa-star text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 mb-1">لا توجد تقييمات حتى الآن</h3>
                        <p class="text-slate-400 text-sm font-semibold">بمجرد قيام الأعضاء الآخرين بتقييم تعاملاتك ستظهر هنا.</p>
                    </div>
                @endif
            </div>

            <!-- Load More Section -->
            @if($reviews->hasMorePages())
                <div class="flex justify-center mt-10 pt-6 border-t border-gray-50" id="load-more-section">
                    <button type="button" id="load-more-btn" data-next-page="2" class="load-more-btn flex items-center justify-center gap-3 bg-primary hover:bg-blue-700 text-white font-black py-4 px-10 rounded-2xl text-sm md:text-base shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-spinner fa-spin hidden" id="spinner-icon"></i>
                        <i class="fa-solid fa-wand-magic-sparkles" id="btn-icon"></i>
                        <span>عرض المزيد</span>
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreBtn = document.getElementById('load-more-btn');
        const spinnerIcon = document.getElementById('spinner-icon');
        const btnIcon = document.getElementById('btn-icon');
        const reviewsContainer = document.getElementById('reviews-container');
        const loadMoreSection = document.getElementById('load-more-section');

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const nextPage = this.getAttribute('data-next-page');
                
                // Show spinner, hide default icon
                spinnerIcon.classList.remove('hidden');
                btnIcon.classList.add('hidden');
                this.disabled = true;

                // Build AJAX URL
                const url = new URL(window.location.href);
                url.searchParams.set('page', nextPage);

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Append new reviews HTML
                        reviewsContainer.insertAdjacentHTML('beforeend', data.html);
                        
                        // Increment page count
                        const newPage = parseInt(nextPage) + 1;
                        this.setAttribute('data-next-page', newPage);

                        // If no more pages, hide the entire load more button section
                        if (!data.hasMore) {
                            loadMoreSection.style.display = 'none';
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء تحميل التقييمات الإضافية.',
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
                        text: 'فشل الاتصال بالخادم لمتابعة التحميل.',
                        customClass: {
                            popup: 'rounded-[32px]'
                        }
                    });
                })
                .finally(() => {
                    // Reset icons and button status
                    spinnerIcon.classList.add('hidden');
                    btnIcon.classList.remove('hidden');
                    this.disabled = false;
                });
            });
        }
    });
</script>
@endpush
