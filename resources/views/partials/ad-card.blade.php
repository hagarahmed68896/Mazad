<div class="p-ad-card">
    <div class="p-card-image">
        @php
            $image = $ad->image ? (Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/')) : null;
        @endphp
        <a href="{{ route('ads.show', $ad->id) }}" class="h-full w-full block bg-gray-50">
            @if($image)
                <img src="{{ $image }}" alt="{{ $ad->title }}">
            @elseif($ad->isCars())
                <img src="{{ asset('car_placeholder.svg') }}" alt="{{ $ad->title }}" style="width: 100%; height: 100%; object-fit: contain; padding: 1.5rem;">
            @else
                <div class="flex items-center justify-center h-full w-full text-gray-300">
                    <i class="fa-solid fa-image text-4xl"></i>
                </div>
            @endif
        </a>
        
        @if(isset($showSelectionCheckbox) && $showSelectionCheckbox)
            <div class="ad-select-checkbox-container" onclick="event.stopPropagation();" style="position: absolute; top: 1rem; right: 1rem; z-index: 50; background: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1.5px solid #E2E8F0; cursor: pointer; transition: all 0.2s ease;">
                <input type="checkbox" name="ad_ids[]" value="{{ $ad->id }}" class="ad-bulk-checkbox" style="width: 18px; height: 18px; cursor: pointer; accent-color: #1A73E8;">
            </div>
        @endif

        @if((!Auth::check() || Auth::id() != $ad->user_id) && (!Auth::check() || Auth::user()->account_type !== 'partner'))
            <div class="p-wishlist cursor-pointer" onclick="event.preventDefault(); event.stopPropagation(); requireAuth(() => toggleGlobalWishlist(null, {{ $ad->id }}))" title="أضف للمفضلة">
                @php
                    $is_favorited_card = Auth::check() && Auth::user()->favorites()->where('ad_id', $ad->id)->exists();
                @endphp
                <i class="{{ $is_favorited_card ? 'fa-solid' : 'fa-regular' }} fa-heart wishlist-icon-{{ $ad->id }}" style="{{ $is_favorited_card ? 'color: #EF4444 !important;' : '' }}"></i>
            </div>

            <div class="p-wishlist cursor-pointer" onclick="event.preventDefault(); event.stopPropagation(); requireAuth(() => toggleGlobalLookLater(null, {{ $ad->id }}))" title="حفظ للاطلاع لاحقاً" style="left: 4.5rem;">
                @php
                    $is_look_later_card = Auth::check() && Auth::user()->lookLaters()->where('ad_id', $ad->id)->exists();
                @endphp
                <i class="{{ $is_look_later_card ? 'fa-solid' : 'fa-regular' }} fa-bookmark look-later-icon-{{ $ad->id }}" style="{{ $is_look_later_card ? 'color: #1A73E8 !important;' : '' }}"></i>
            </div>
        @endif

        <div class="p-badges">
            @if($ad->is_featured)
                <span class="p-badge-pill featured"><i class="fa-solid fa-fire"></i> مميز</span>
            @endif
            <span class="p-badge-pill type">
                @if($ad->type == 'electronic_auction') إلكتروني 
                @elseif($ad->type == 'hybrid_auction') هجين
                @elseif($ad->type == 'direct_purchase') شراء فوري
                @else مناقصة @endif
            </span>
            <span class="p-badge-pill status">
                @php
                    $status_label = 'قائم';
                    if($ad->type == 'hybrid_auction') {
                        $b_status = $ad->bid_status ?? $ad->status;
                        if($b_status == 'upcoming') $status_label = 'لم تبدأ المزايدة';
                        elseif($b_status == 'active') $status_label = 'جاري';
                        elseif($b_status == 'postponed') $status_label = 'مؤجل';
                        elseif($b_status == 'not_sold') $status_label = 'لم يتم البيع';
                        elseif($b_status == 'sold') $status_label = 'تم البيع';
                        elseif($b_status == 'finished') $status_label = 'منتهي';
                    } else {
                        if($ad->status == 'active') $status_label = 'قائم';
                        elseif($ad->status == 'upcoming') $status_label = 'قادم';
                        else $status_label = 'منتهي';
                    }
                @endphp
                {{ $status_label }}
            </span>
        </div>

        <div class="p-countdown" data-end="{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->toIso8601String() : '' }}">
            <i class="fa-regular fa-clock"></i>
            <span class="countdown-timer">جاري الحساب...</span>
        </div>
    </div>

    <div class="p-card-body">
        <span class="p-category-pill">{{ $ad->category->name ?? 'غير مصنف' }}</span>
        <a href="{{ route('ads.show', $ad->id) }}" style="text-decoration: none; color: inherit;">
            <h3 class="p-ad-title">{{ $ad->title }}</h3>
        </a>
    

        <div class="p-price-row" style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem;">
            @php
                $price_label = 'السعر الحالي';
                if($ad->type == 'direct_purchase') {
                    $price_label = 'سعر الشراء';
                } elseif($ad->type == 'hybrid_auction') {
                    $price_label = 'السعر الابتدائي';
                }
            @endphp
            <span class="p-price-label" style="font-size: 0.85rem; color: #64748B; font-weight: 700;">{{ $price_label }}</span>
            <div class="flex items-center gap-1 text-primary">
                <span class="text-xl font-black">{{ number_format($ad->price) }}</span>
                <x-riyal-icon class="w-5 h-5" />
            </div>
        </div>

        <div class="p-stats-row">
            <div class="p-stat-box">
                <i class="fa-solid fa-eye"></i>
                <span>{{ $ad->views_count ?? 0 }} مشاهدة</span>
            </div>
            <div class="p-stat-box">
                <i class="fa-solid fa-gavel"></i>
                <span>
                    {{ $ad->bids_count }} 
                    @if($ad->type == 'direct_purchase')
                        عرض
                    @elseif($ad->type == 'tender')
                        مناقصة
                    @else
                        مزايدة
                    @endif
                </span>
            </div>
        </div>

        <div class="p-card-footer">
            @if(Auth::check() && Auth::id() == $ad->user_id)
                @if(in_array($ad->status, ['incomplete', 'pending']))
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-p-outline" style="flex: 1; border-color: #1A73E8; color: #1A73E8; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px;">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>إكمال</span>
                        </a>
                        <form id="delete-form-{{ $ad->id }}" action="{{ route('ads.destroy', $ad->id) }}" method="POST" style="flex: 1; display: flex;">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDelete({{ $ad->id }})" class="btn btn-p-outline" style="width: 100%; border-color: #EF4444; color: #EF4444; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px;">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>حذف</span>
                            </button>
                        </form>
                    </div>
                @elseif($ad->status == 'active')
                    <div style="display: flex; width: 100%;">
                        <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-p-outline" style="width: 100%; border-color: #1A73E8; color: #1A73E8; text-align: center; text-decoration: none; justify-content: center; display: flex; align-items: center; gap: 0.5rem; border-radius: 12px; height: 40px;">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>تعديل</span>
                        </a>
                    </div>
                @elseif($ad->status == 'finished' || $ad->status == 'sold')
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-p-outline" style="flex: 1; border-color: #1A73E8; color: #1A73E8; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.25rem; border-radius: 12px; height: 40px; font-size: 0.85rem; white-space: nowrap; padding: 0 0.25rem;">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                            <span>تعديل</span>
                        </a>
                        <form action="{{ route('ads.repost', $ad->id) }}" method="POST" style="flex: 1; display: flex;">
                            @csrf
                            <button type="submit" class="btn btn-p-primary" style="width: 100%; background-color: #1A73E8; border-color: #1A73E8; color: white; display: flex; align-items: center; justify-content: center; gap: 0.25rem; border-radius: 12px; height: 40px; cursor: pointer; text-decoration: none; font-size: 0.85rem; white-space: nowrap; padding: 0 0.25rem;">
                                <i class="fa-solid fa-rotate text-sm"></i>
                                <span>إعادة النشر</span>
                            </button>
                        </form>
                    </div>
                @elseif($ad->status == 'upcoming')
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-p-outline" style="flex: 1; border-color: #1A73E8; color: #1A73E8; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px;">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>تعديل</span>
                        </a>
                        <form id="delete-form-{{ $ad->id }}" action="{{ route('ads.destroy', $ad->id) }}" method="POST" style="flex: 1; display: flex;">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDelete({{ $ad->id }})" class="btn btn-p-outline" style="width: 100%; border-color: #EF4444; color: #EF4444; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px;">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>حذف</span>
                            </button>
                        </form>
                    </div>
                @endif
            @else
                @if($ad->status !== 'active')
                    @if($ad->status == 'upcoming')
                        <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-outline w-full text-center" style="border-color: #CBD5E1; color: #64748B; width: 100%; display: flex; align-items: center; justify-content: center; height: 40px; border-radius: 12px; font-weight: 700; text-decoration: none; background: #F8FAFC;">تبدأ قريباً</a>
                    @else
                        @php
                            $pendingOrder = null;
                            if (Auth::check()) {
                                $pendingOrder = \App\Models\Order::where('ad_id', $ad->id)
                                    ->where('buyer_id', Auth::id())
                                    ->where('status', 'pending_payment')
                                    ->first();
                            }
                        @endphp
                        @if ($pendingOrder)
                            <a href="{{ route('purchases.my') }}" class="btn btn-p-primary w-full text-center" style="background-color: #1A73E8; border-color: #1A73E8; color: white; width: 100%; display: flex; align-items: center; justify-content: center; height: 40px; border-radius: 12px; font-weight: 700; text-decoration: none;">إتمام الدفع</a>
                        @else
                            <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-outline w-full text-center" style="border-color: #E2E8F0; color: #94A3B8; width: 100%; display: flex; align-items: center; justify-content: center; height: 40px; border-radius: 12px; font-weight: 700; text-decoration: none; background: #F1F5F9;">عرض التفاصيل (منتهي)</a>
                        @endif
                    @endif
                @else
                    @if($ad->type == 'direct_purchase')
                        <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-primary">شراء</a>
                        <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-outline">قدم عرضك</a>
                    @elseif($ad->type == 'hybrid_auction')
                        <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-primary">المشاركة في المزاد</a>
                    @elseif($ad->type == 'tender')
                        <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-primary">ناقص الآن</a>
                    @else
                        <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-p-primary">زايد</a>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    if (typeof confirmDelete !== 'function') {
        window.confirmDelete = function(adId) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن حذف هذا الإعلان!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'نعم، احذفه!',
                cancelButtonText: 'إلغاء',
                customClass: {
                    popup: 'rounded-[32px]',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + adId).submit();
                }
            });
        }
    }
</script>
