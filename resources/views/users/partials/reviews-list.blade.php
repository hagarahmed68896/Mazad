@foreach($reviews as $review)
    @php
        $displayUser = $review->ratedUser;
    @endphp
    
    @if($displayUser)
        <div id="review-card-{{ $review->id }}" class="review-card bg-white rounded-3xl p-6 border border-slate-100 hover:shadow-md hover:border-slate-200 transition-all flex items-start justify-between gap-4 text-right mb-4" dir="rtl">
            <!-- Right side: Avatar and review content -->
            <div class="flex items-start gap-4 flex-1">
                <!-- User Avatar -->
                <div class="flex-shrink-0">
                    @if($displayUser->avatar)
                        <img src="{{ asset($displayUser->avatar) }}" alt="{{ $displayUser->name }}" class="w-12 h-12 rounded-full object-cover border border-slate-100">
                    @else
                        <div class="w-12 h-12 rounded-full bg-primary text-white font-extrabold flex items-center justify-center text-lg select-none">
                            {{ strtoupper(substr($displayUser->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <!-- User name, stars, comment -->
                <div class="flex-1">
                    <h4 class="text-base font-extrabold text-slate-800 mb-1">
                        {{ $displayUser->name }}
                        <span class="text-xs font-semibold text-slate-400 mr-1.5">(قيمته كبائع)</span>
                    </h4>
                    
                    <!-- Stars -->
                    <div class="flex items-center gap-1 mb-2 text-[#FFB800]">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating)
                                <i class="fa-solid fa-star text-sm md:text-base"></i>
                            @else
                                <i class="fa-regular fa-star text-sm md:text-base text-slate-200"></i>
                            @endif
                        @endfor
                    </div>

                    <!-- Associated Ad (if any) -->
                    @if($review->ad)
                        <div class="mb-3 flex items-center gap-1.5 bg-slate-50 border border-slate-100/80 px-3 py-1.5 rounded-xl w-fit">
                            <span class="text-slate-400 text-[11px] font-bold">على إعلان:</span>
                            <a href="{{ route('ads.show', $review->ad->id) }}" class="text-[11px] font-black text-primary hover:underline hover:text-blue-700 transition-colors">
                                {{ $review->ad->title }}
                            </a>
                        </div>
                    @endif

                    <!-- Comment -->
                    <p class="text-slate-600 text-xs md:text-sm font-semibold leading-relaxed">
                        {{ $review->comment ?? 'لم يكتب تعليقاً.' }}
                    </p>
                </div>
            </div>

            <!-- Left side: Time and Action buttons -->
            <div class="text-left flex-shrink-0 flex flex-col items-end gap-3">
                <span class="text-slate-400 text-[10px] md:text-xs font-bold whitespace-nowrap">
                    {{ $review->created_at->diffForHumans() }}
                </span>
                
                <div class="flex items-center gap-2 mt-2">
                    <button type="button" onclick="editReview('{{ $review->id }}', {{ $review->rating }}, '{{ addslashes($review->comment ?? '') }}')" class="text-slate-500 hover:text-primary transition-colors text-xs font-extrabold flex items-center gap-1 border border-slate-200 px-2.5 py-1 rounded-xl bg-slate-50/50 hover:bg-blue-50 hover:border-blue-200 cursor-pointer">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <span>تعديل</span>
                    </button>
                    <button type="button" onclick="deleteReview('{{ $review->id }}')" class="text-rose-500 hover:text-rose-700 transition-colors text-xs font-extrabold flex items-center gap-1 border border-rose-100 px-2.5 py-1 rounded-xl bg-rose-50/20 hover:bg-rose-50 hover:border-rose-200 cursor-pointer">
                        <i class="fa-regular fa-trash-can"></i>
                        <span>حذف</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
@endforeach
