<div class="service-card service-card-component bg-white rounded-[32px] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-50 flex flex-col h-full group">
    <!-- Card Header: User Info -->
    <div class="service-header p-5 flex items-center justify-between border-b border-gray-50 bg-gray-50/30">
        <div class="flex items-center gap-3">
            <div class="relative">
                <img src="{{ $service->user->avatar ? (Str::startsWith($service->user->avatar, 'http') ? $service->user->avatar : '/' . ltrim($service->user->avatar, '/')) : 'https://ui-avatars.com/api/?name='.urlencode($service->user->name).'&background=1A73E8&color=fff' }}" 
                     class="w-10 h-10 rounded-xl object-cover ring-2 ring-white shadow-sm">
            </div>
            <div>
                <h4 class="text-sm font-black text-gray-900 leading-tight">{{ $service->user->name }}</h4>
                <p class="text-[10px] text-gray-400 font-bold mt-0.5">متخصص في {{ $service->category->name ?? 'الخدمات' }}</p>
            </div>
        </div>
       
    </div>

    <!-- Card Body: Image & Details -->
    <a href="{{ route('services.show', $service->id) }}" class="service-image relative group-hover:scale-[1.02] transition-transform duration-500 overflow-hidden block h-48 bg-gray-50 flex items-center justify-center border-b border-gray-100">
        @if($service->image)
            <img src="{{ Str::startsWith($service->image, 'http') ? $service->image : '/' . ltrim($service->image, '/') }}" 
                 class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gray-50 flex items-center justify-center text-gray-300">
                <i class="fa-solid fa-image text-4xl"></i>
            </div>
        @endif
        
        <!-- Price Badge Over Image -->
        <div class="absolute bottom-4 left-4 bg-blue-600/90 backdrop-blur text-white px-3 py-1.5 rounded-xl font-black text-xs shadow-lg flex items-center gap-1">
            <span>{{ number_format($service->price) }}</span>
            <x-riyal-icon class="w-3.5 h-3.5" />
        </div>
    </a>

    <div class="service-body p-6 flex-1 flex flex-col">
        <div class="mb-4">
            <a href="{{ route('services.show', $service->id) }}">
                <h3 class="text-lg font-black text-gray-900 group-hover:text-primary transition-colors duration-300 leading-snug">{{ $service->title }}</h3>
            </a>
            <p class="text-gray-400 text-sm font-bold mt-2 line-clamp-2 leading-relaxed">
                {{ $service->description ?? 'لوريم إيبسوم دولار سيت آميت، كونسيكتيتور أديبيسيسينغ إيليت.' }}
            </p>
        </div>

        <div class="service-footer mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
            <form action="{{ route('chats.start') }}" method="POST" class="w-full">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">
                <button type="submit" class="w-full bg-primary text-white py-3.5 rounded-2xl font-black text-sm shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                    اطلب الخدمة
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                </button>
            </form>
        </div>
    </div>
</div>
