@extends('layouts.admin')

@section('title', 'تعديل بانر إعلاني')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Header -->
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.banners.index') }}" class="text-slate-500 hover:text-[#1A73E8] transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        <h1 class="text-3xl font-black text-[#1C1C1C]">تعديل بانر إعلاني</h1>
    </div>

    <!-- Form Container -->
    <div class="bg-[#F8FAFC] border border-slate-100 rounded-[2rem] p-6 lg:p-10 mb-8 max-w-5xl">
        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data" id="bannerForm">
            @csrf
            @method('PUT')

            <!-- Section 1: بيانات البانر -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-[#1A73E8] rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">بيانات البانر</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Global title mapping to banner title -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">العنوان</label>
                        <input type="text" name="name" value="{{ old('name', $banner->title) }}" placeholder="أدخل العنوان"
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">الحالة</label>
                        <div class="relative">
                            <select name="status" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none appearance-none shadow-sm">
                                <option value="1" {{ old('status', $banner->status) == '1' ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ old('status', $banner->status) == '0' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-slate-200/60 mb-8">

            <!-- Section 2: المحتوى -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-[#1A73E8] rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">المحتوى</h3>
                </div>

                <div class="space-y-5" id="contentFields">
                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">الصورة</label>
                        <div class="w-full bg-white border-2 border-dashed border-[#1A73E8]/20 rounded-2xl p-8 flex flex-col items-center justify-center hover:bg-[#1A73E8]/5 transition-colors cursor-pointer group relative">
                            <input type="file" name="items[0][image]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="file-upload-0" onchange="previewImage(event, 0)">
                            <div id="upload-placeholder-0" class="{{ $banner->image ? 'hidden' : 'flex' }} flex-col items-center">
                                <div class="w-14 h-14 bg-[#1A73E8] text-white rounded-2xl flex items-center justify-center mb-3 shadow-lg shadow-[#1A73E8]/20 group-hover:-translate-y-1 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                </div>
                                <h4 class="text-sm font-black text-[#1C1C1C] mb-1">اسحب الملف هنا</h4>
                                <p class="text-xs text-slate-400 mb-3">أو</p>
                                <button type="button" class="px-5 py-2 bg-white border-2 border-[#1A73E8] text-[#1A73E8] rounded-xl font-black text-xs group-hover:bg-[#1A73E8] group-hover:text-white transition-all">
                                    تصفح الملفات
                                </button>
                            </div>
                            <div id="image-preview-0" class="{{ $banner->image ? 'flex' : 'hidden' }} flex-col items-center">
                                <img id="preview-img-0" class="max-h-32 rounded-xl mb-3 shadow-sm"
                                     src="{{ $banner->image ? asset('storage/' . $banner->image) : '#' }}" alt="Preview">
                                <p class="text-xs font-bold text-[#1A73E8] cursor-pointer">انقر لتغيير الصورة</p>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">العنوان</label>
                        <input type="text" name="items[0][title]" value="{{ old('items.0.title', $banner->title) }}" placeholder="أدخل العنوان" required
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">الوصف</label>
                        <textarea name="items[0][description]" placeholder="أدخل الوصف" rows="3"
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm resize-none">{{ old('items.0.description', $banner->description) }}</textarea>
                    </div>

                    <!-- Button Text -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">اسم الزر</label>
                        <div class="flex flex-row flex-wrap gap-4">
                            <input type="text" name="items[0][button_text]" value="{{ old('items.0.button_text', $banner->button_text) }}" placeholder="أدخل اسم الزر"
                                   class="flex-1 px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Add Content Button -->
                <button type="button" class="mt-6 w-full py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-sm hover:bg-[#1557b0] hover:shadow-lg hover:shadow-[#1A73E8]/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    إضافة للمحتوى
                </button>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="{{ $banner->image || $banner->title ? '' : 'hidden' }} mb-8">
                <hr class="border-slate-200/60 mb-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-1 h-6 bg-slate-300 rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">عرض المحتوى</h3>
                </div>
                <div id="previewCards" class="flex gap-4 overflow-x-auto pb-2">
                    @if($banner->image || $banner->title)
                    <div class="relative flex-shrink-0 w-48 bg-slate-700 rounded-2xl overflow-hidden shadow-md" id="existingCard">
                        @if($banner->image)
                        <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-28 object-cover" alt="">
                        @else
                        <div class="w-full h-28 bg-slate-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-3">
                            @if($banner->title)
                            <p class="text-white text-xs font-bold leading-tight mb-1 line-clamp-1">{{ $banner->title }}</p>
                            @endif
                            @if($banner->description)
                            <p class="text-white/70 text-[10px] leading-tight line-clamp-2">{{ $banner->description }}</p>
                            @endif
                            @if($banner->button_text)
                            <span class="inline-block mt-1 px-2 py-0.5 bg-[#1A73E8] text-white text-[10px] font-bold rounded-lg">{{ $banner->button_text }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer Buttons -->
            <hr class="border-slate-200/60 mb-6">
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('admin.banners.index') }}" class="px-8 py-4 bg-[#E2E8F0] text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-300 transition-all text-center">إلغاء</a>
                <button type="submit" class="px-12 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-sm hover:shadow-xl hover:shadow-[#1A73E8]/20 transition-all hover:-translate-y-0.5">حفظ التعديلات</button>
            </div>

        </form>
    </div>
</div>

<script>
    function previewImage(event, index) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const placeholder = document.getElementById('upload-placeholder-' + index);
                const preview = document.getElementById('image-preview-' + index);
                const img = document.getElementById('preview-img-' + index);
                if (placeholder) { placeholder.classList.add('hidden'); placeholder.classList.remove('flex'); }
                if (preview) { preview.classList.remove('hidden'); preview.classList.add('flex'); }
                if (img) img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
