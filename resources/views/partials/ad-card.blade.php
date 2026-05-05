<div class="p-ad-card">
    <div class="p-card-image">
        @php
            $image = $ad->image ? asset($ad->image) : asset('images/laptop.png');
        @endphp
        <a href="{{ route('ads.show', $ad->id) }}">
            <img src="{{ $image }}" alt="{{ $ad->title }}">
        </a>
        
        <div class="p-wishlist cursor-pointer" onclick="event.preventDefault(); event.stopPropagation(); requireAuth(() => toggleGlobalWishlist(null, {{ $ad->id }}))">
            @php
                $is_favorited_card = Auth::check() && Auth::user()->favorites()->where('ad_id', $ad->id)->exists();
            @endphp
            <i class="{{ $is_favorited_card ? 'fa-solid' : 'fa-regular' }} fa-heart wishlist-icon-{{ $ad->id }}" style="{{ $is_favorited_card ? 'color: #EF4444 !important;' : '' }}"></i>
        </div>

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
        <div class="p-location">
            <i class="fa-solid fa-location-dot"></i>
            <span>{{ $ad->address ?? ($ad->city->name ?? 'السعودية') }}</span>
        </div>

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
                <x-riyal-icon class="w-8 h-8" />
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
        </div>
    </div>
</div>
