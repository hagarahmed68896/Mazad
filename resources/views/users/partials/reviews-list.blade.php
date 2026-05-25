@foreach($reviews as $review)
    <div class="review-card bg-white rounded-3xl p-6 border border-slate-100 hover:shadow-md hover:border-slate-200 transition-all flex items-start justify-between gap-4 text-right mb-4" dir="rtl">
        <!-- Right side: Avatar and review content -->
        <div class="flex items-start gap-4 flex-1">
            <!-- Reviewer Avatar -->
            <div class="flex-shrink-0">
                @if($review->reviewer->avatar)
                    <img src="{{ asset($review->reviewer->avatar) }}" alt="{{ $review->reviewer->name }}" class="w-12 h-12 rounded-full object-cover border border-slate-100">
                @else
                    <div class="w-12 h-12 rounded-full bg-primary text-white font-extrabold flex items-center justify-center text-lg select-none">
                        {{ strtoupper(substr($review->reviewer->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Reviewer name, stars, comment -->
            <div class="flex-1">
                <h4 class="text-base font-extrabold text-slate-800 mb-1">
                    {{ $review->reviewer->name }}
                </h4>
                
                <!-- Stars -->
                <div class="flex items-center gap-1 mb-3 text-[#FFB800]">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->rating)
                            <i class="fa-solid fa-star text-sm md:text-base"></i>
                        @else
                            <i class="fa-regular fa-star text-sm md:text-base text-slate-200"></i>
                        @endif
                    @endfor
                </div>

                <!-- Comment -->
                <p class="text-slate-600 text-xs md:text-sm font-semibold leading-relaxed">
                    {{ $review->comment ?? 'لم يكتب تعليقاً.' }}
                </p>

                <!-- Optional reply from the owner -->
                @if($review->reply)
                    <div class="mt-4 bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                        <span class="text-xs font-black text-primary block mb-1">ردي:</span>
                        <p class="text-slate-600 text-xs font-semibold">{{ $review->reply }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Left side: Time -->
        <div class="text-left flex-shrink-0">
            <span class="text-slate-400 text-[10px] md:text-xs font-bold whitespace-nowrap">
                {{ $review->created_at->diffForHumans() }}
            </span>
        </div>
    </div>
@endforeach
