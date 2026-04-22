<div class="p-ad-card">
    <div class="p-card-image">
        @php
            $image = $ad->image ? asset($ad->image) : asset('images/laptop.png');
        @endphp
        <img src="{{ $image }}" alt="{{ $ad->title }}">
        
        <div class="p-wishlist">
            <i class="fa-regular fa-heart"></i>
        </div>

        <div class="p-badges">
            <span class="p-badge-pill featured"><i class="fa-solid fa-fire"></i> مميز</span>
            <span class="p-badge-pill type">
                @if($ad->type == 'electronic_auction') إلكتروني 
                @elseif($ad->type == 'hybrid_auction') هجين
                @elseif($ad->type == 'direct_purchase') شراء فوري
                @else مناقصة @endif
            </span>
            <span class="p-badge-pill status">
                @if($ad->status == 'active') قائم
                @elseif($ad->status == 'upcoming') قادم
                @else منتهي @endif
            </span>
        </div>

        <div class="p-countdown" data-end="{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->toIso8601String() : '' }}">
            <i class="fa-regular fa-clock"></i>
            <span class="countdown-timer">جاري الحساب...</span>
        </div>
    </div>

    <div class="p-card-body">
        <span class="p-category-pill">{{ $ad->category->name ?? 'غير مصنف' }}</span>
        <h3 class="p-ad-title">{{ $ad->title }}</h3>

        <div class="p-price-row">
            <span class="p-price-label">السعر الحالي</span>
            <div class="p-price-pill">
                <i class="fa-solid fa-gavel"></i>
                <span>{{ number_format($ad->price) }} ر.س</span>
            </div>
        </div>

        <div class="p-stats-row">
            <div class="p-stat-box">
                <i class="fa-solid fa-eye"></i>
                <span>50 مشاهدة</span>
            </div>
            <div class="p-stat-box">
                <i class="fa-solid fa-gavel"></i>
                <span>{{ $ad->bids_count }} مزايدة</span>
            </div>
        </div>

        <div class="p-card-footer">
            @if($ad->type == 'direct_purchase')
                <button class="btn btn-p-primary">شراء الآن</button>
                <button class="btn btn-p-outline">تواصل</button>
            @else
                <button class="btn btn-p-primary">زايد الآن</button>
            @endif
        </div>
    </div>
</div>
