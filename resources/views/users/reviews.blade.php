@extends('layouts.main')

@section('title', 'تقييماتي | تمت')

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
                <p class="text-gray-500 text-xs md:text-sm font-medium">التقييمات والآراء التي كتبتها للمستخدمين والبائعين الآخرين.</p>
            </div>

            <!-- Reviews Container -->
            <div id="reviews-container" class="space-y-4">
                @if($reviews->count() > 0)
                    @include('users.partials.reviews-list', ['reviews' => $reviews])
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <i class="fa-regular fa-star text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 mb-1">لم تقم بكتابة أي تقييمات بعد</h3>
                        <p class="text-slate-400 text-sm font-semibold">بمجرد قيامك بتقييم البائعين بعد إتمام الصفقات ستظهر هنا.</p>
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

    window.editReview = function(id, rating, comment) {
        let currentRating = rating;
        
        Swal.fire({
            title: '<span style="font-size: 1.3rem; font-weight: 900;">تعديل التقييم</span>',
            html: `
                <div class="text-center" style="direction: rtl;">
                    <p class="text-xs font-bold text-slate-400 mb-4">قم بتعديل تقييمك وتجربتك:</p>
                    <div class="swal2-stars mb-4" id="edit-star-rating-container" style="display: flex; justify-content: center; gap: 0.5rem; font-size: 1.5rem; cursor: pointer;">
                        <i class="fa-${rating >= 1 ? 'solid' : 'regular'} fa-star edit-star-item" data-index="1" style="color: ${rating >= 1 ? '#FFB800' : '#CBD5E1'};"></i>
                        <i class="fa-${rating >= 2 ? 'solid' : 'regular'} fa-star edit-star-item" data-index="2" style="color: ${rating >= 2 ? '#FFB800' : '#CBD5E1'};"></i>
                        <i class="fa-${rating >= 3 ? 'solid' : 'regular'} fa-star edit-star-item" data-index="3" style="color: ${rating >= 3 ? '#FFB800' : '#CBD5E1'};"></i>
                        <i class="fa-${rating >= 4 ? 'solid' : 'regular'} fa-star edit-star-item" data-index="4" style="color: ${rating >= 4 ? '#FFB800' : '#CBD5E1'};"></i>
                        <i class="fa-${rating >= 5 ? 'solid' : 'regular'} fa-star edit-star-item" data-index="5" style="color: ${rating >= 5 ? '#FFB800' : '#CBD5E1'};"></i>
                    </div>
                    <textarea id="edit-swal-rating-comment" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 focus:outline-none focus:border-blue-500 text-sm font-bold" rows="3" placeholder="اكتب تعليقك المحدث هنا...">${comment}</textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'حفظ التعديلات',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#F1F5F9',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full text-right',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold text-sm bg-primary border-0',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold text-sm text-slate-500 border-0'
            },
            didOpen: () => {
                const starItems = document.querySelectorAll('.edit-star-item');
                starItems.forEach(star => {
                    star.addEventListener('click', (e) => {
                        const index = parseInt(star.getAttribute('data-index'));
                        currentRating = index;
                        starItems.forEach(s => {
                            const idx = parseInt(s.getAttribute('data-index'));
                            if (idx <= index) {
                                s.classList.remove('fa-regular');
                                s.classList.add('fa-solid');
                                s.style.color = '#FFB800';
                            } else {
                                s.classList.remove('fa-solid');
                                s.classList.add('fa-regular');
                                s.style.color = '#CBD5E1';
                            }
                        });
                    });
                });
            },
            preConfirm: () => {
                const updatedComment = document.getElementById('edit-swal-rating-comment').value.trim();
                return { rating: currentRating, comment: updatedComment };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري تعديل التقييم...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(`/reviews/${id}?_method=PUT`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(result.value)
                })
                .then(r => {
                    if (!r.ok) {
                        return r.text().then(text => {
                            throw new Error(`Server error ${r.status}: ${text}`);
                        });
                    }
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التعديل!',
                            text: 'تم تعديل تقييمك بنجاح.',
                            confirmButtonText: 'حسناً',
                            confirmButtonColor: '#1A73E8',
                            customClass: { popup: 'rounded-[32px]' }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: data.message || 'حدث خطأ غير متوقع.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire({ icon: 'error', title: 'خطأ الاتصال', text: 'تعذر الاتصال بالخادم. يرجى مراجعة Console للتفاصيل.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
                });
            }
        });
    };

    window.deleteReview = function(id) {
        Swal.fire({
            title: '<span style="font-size: 1.3rem; font-weight: 900;">حذف التقييم</span>',
            text: 'هل أنت متأكد من رغبتك في حذف هذا التقييم نهائياً؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#E11D48',
            cancelButtonColor: '#F1F5F9',
            customClass: {
                popup: 'rounded-[32px] p-8 max-w-[460px] w-full text-right',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold text-sm bg-rose-600 border-0',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold text-sm text-slate-500 border-0'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري حذف التقييم...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(`/reviews/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) {
                        return r.text().then(text => {
                            throw new Error(`Server error ${r.status}: ${text}`);
                        });
                    }
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الحذف!',
                            text: 'تم حذف التقييم بنجاح.',
                            confirmButtonText: 'حسناً',
                            confirmButtonColor: '#1A73E8',
                            customClass: { popup: 'rounded-[32px]' }
                        }).then(() => {
                            const card = document.getElementById(`review-card-${id}`);
                            if (card) {
                                card.style.transition = 'all 0.5s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'translateY(20px)';
                                setTimeout(() => {
                                    card.remove();
                                    if (document.querySelectorAll('.review-card').length === 0) {
                                        location.reload();
                                    }
                                }, 500);
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: data.message || 'حدث خطأ غير متوقع.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire({ icon: 'error', title: 'خطأ الاتصال', text: 'تعذر الاتصال بالخادم. يرجى مراجعة Console للتفاصيل.', confirmButtonColor: '#1A73E8', customClass: { popup: 'rounded-[32px]' } });
                });
            }
        });
    };
</script>
@endpush
