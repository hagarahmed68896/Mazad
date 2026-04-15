@extends('layouts.admin')

@section('title', 'تعديل الخاصية - ' . $category->name)

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Title & Back -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.categories.properties.index', $category->id) }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-[#1C1C1C]">تعديل الخاصية</h1>
            <p class="text-sm text-slate-400 font-bold mt-0.5">{{ $category->name }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.categories.properties.update', [$category->id, $property->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm space-y-6">
            <h2 class="text-lg font-black text-[#1C1C1C] border-b border-slate-50 pb-4">بيانات الخاصية</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Label -->
                <div class="space-y-3">
                    <label class="text-sm font-black text-[#6F6F6F]">اسم الخاصية <span class="text-red-500">*</span></label>
                    <input type="text" name="label" value="{{ old('label', $property->label) }}" required
                           placeholder="مثال: المساحة، السعر، اللون..."
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                </div>

                <!-- Placeholder -->
                <div class="space-y-3">
                    <label class="text-sm font-black text-[#6F6F6F]">النص التوضيحي</label>
                    <input type="text" name="placeholder" value="{{ old('placeholder', $property->placeholder) }}"
                           placeholder="مثال: أدخل المساحة بالمتر المربع..."
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                </div>
            </div>

            <!-- Input Type -->
            <div class="space-y-3">
                <label class="text-sm font-black text-[#6F6F6F]">نوع الإدخال <span class="text-red-500">*</span></label>
                <select name="input_type" required
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all appearance-none">
                    <option value="">-- اختر نوع الإدخال --</option>
                    <option value="text"     {{ old('input_type', $property->input_type) == 'text'     ? 'selected' : '' }}>نص (Text)</option>
                    <option value="number"   {{ old('input_type', $property->input_type) == 'number'   ? 'selected' : '' }}>رقم (Number)</option>
                    <option value="select"   {{ old('input_type', $property->input_type) == 'select'   ? 'selected' : '' }}>قائمة منسدلة (Select)</option>
                    <option value="checkbox" {{ old('input_type', $property->input_type) == 'checkbox' ? 'selected' : '' }}>مربع اختيار (Checkbox)</option>
                    <option value="radio"    {{ old('input_type', $property->input_type) == 'radio'    ? 'selected' : '' }}>زر راديو (Radio)</option>
                    <option value="textarea" {{ old('input_type', $property->input_type) == 'textarea' ? 'selected' : '' }}>نص طويل (Textarea)</option>
                    <option value="date"     {{ old('input_type', $property->input_type) == 'date'     ? 'selected' : '' }}>تاريخ (Date)</option>
                </select>
            </div>

            <!-- Options -->
            <div class="space-y-3">
                <label class="text-sm font-black text-[#6F6F6F]">الخيارات <span class="text-xs font-bold text-slate-400">(للقائمة المنسدلة والراديو فقط — افصل بين الخيارات بفاصلة)</span></label>
                <input type="text" name="options"
                       value="{{ old('options', $property->options ? implode(', ', $property->options) : '') }}"
                       placeholder="مثال: أحمر, أزرق, أخضر"
                       class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
            </div>

            <!-- Required Toggle -->
            <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-slate-100">
                <div>
                    <p class="text-sm font-black text-[#1C1C1C]">إلزامي</p>
                    <p class="text-xs font-bold text-slate-400 mt-0.5">هل هذه الخاصية مطلوبة عند نشر الإعلان؟</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" class="sr-only peer" {{ old('is_required', $property->is_required) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1A73E8]"></div>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 mt-6 pb-12">
            <a href="{{ route('admin.categories.properties.index', $category->id) }}"
               class="inline-flex items-center justify-center px-8 py-4 bg-slate-200 text-slate-600 rounded-2xl font-black hover:bg-slate-300 transition-all">إلغاء</a>
            <button type="submit"
                    class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-[0_10px_30px_-10px_rgba(26,115,232,0.5)] hover:bg-blue-700 transition-all active:scale-95">
                حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection
