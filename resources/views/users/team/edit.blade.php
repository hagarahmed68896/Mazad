@extends('layouts.main')

@section('title', $type === 'representative' ? 'تعديل بيانات المندوب | تمت' : 'تعديل بيانات المدير | تمت')

@push('styles')
<style>
    .create-card {
        border-radius: 32px;
        background: white;
    }
    .file-upload-box {
        border: 2px dashed #E2E8F0;
        transition: all 0.3s ease;
        border-radius: 24px;
    }
    .file-upload-box:hover {
        border-color: #1A73E8;
        background-color: #F8FAFC;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10" dir="rtl">
    <div class="container mx-auto px-4 lg:px-24">

        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-sm font-bold text-gray-500 mb-6 justify-start">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <a href="{{ route('team.index', ['tab' => $type]) }}" class="hover:text-primary transition-colors">أعضاء الفريق</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span class="text-white bg-primary px-4 py-1.5 rounded-full text-xs">
                @if($type === 'representative') تعديل بيانات المندوب @else تعديل بيانات المدير @endif
            </span>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white create-card p-8 shadow-sm border border-gray-100 text-right">
                
                <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
                    <h2 class="text-2xl font-black text-gray-900">
                        @if($type === 'representative') تعديل المندوب: {{ $member->name }} @else تعديل المدير: {{ $member->name }} @endif
                    </h2>
                    <a href="{{ route('team.index', ['tab' => $type]) }}" class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full bg-gray-50">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>

                <form action="{{ route('team.update', $member->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- 1. Avatar Upload -->
                    <div>
                        <label class="block text-gray-700 font-bold mb-3 text-sm">
                            @if($type === 'representative') صورة المندوب @else صورة المدير @endif
                        </label>
                        <input type="file" id="avatar-upload" name="avatar" class="hidden" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewAvatar(this)">
                        
                        <div onclick="document.getElementById('avatar-upload').click()" class="file-upload-box cursor-pointer p-6 flex flex-col items-center justify-center text-center relative overflow-hidden min-h-[160px]">
                            
                            <!-- Display current avatar -->
                            <div id="avatar-preview-wrap" class="w-24 h-24 rounded-full overflow-hidden mb-2 border border-gray-200 shadow-inner flex items-center justify-center bg-gray-50">
                                @if($member->avatar)
                                    <img id="avatar-preview-img" src="{{ $member->avatar }}" class="w-full h-full object-cover">
                                @else
                                    <div id="avatar-preview-text" class="w-full h-full bg-[#1A73E8]/10 text-[#1A73E8] flex items-center justify-center font-bold text-2xl uppercase">
                                        {{ mb_substr($member->name, 0, 1) }}
                                    </div>
                                    <img id="avatar-preview-img" class="w-full h-full object-cover hidden">
                                @endif
                            </div>
                            
                            <div id="avatar-placeholder" class="hidden flex flex-col items-center">
                                <div class="w-12 h-12 bg-blue-50 text-primary rounded-[16px] flex items-center justify-center text-2xl mb-3">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <p class="text-gray-500 text-sm font-bold mb-1">
                                    @if($type === 'representative') صورة المندوب / اسحب الملف هنا @else صورة المدير / اسحب الملف هنا @endif
                                </p>
                                <button type="button" class="border border-primary text-primary hover:bg-primary hover:text-white px-6 py-1.5 rounded-xl text-xs font-bold transition-all pointer-events-none">
                                    تصفح الملفات
                                </button>
                            </div>
                        </div>
                        @error('avatar') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- 2. Full Name -->
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ old('name', $member->name) }}" placeholder="أدخل الاسم كامل" class="w-full bg-white border border-gray-200 rounded-2xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required>
                        @error('name') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- 3. Phone Number -->
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">رقم الجوال</label>
                        <div class="relative flex items-center">
                            <div class="absolute right-0 top-0 bottom-0 px-4 bg-gray-50 border-l border-gray-200 flex items-center justify-center rounded-r-2xl font-bold text-gray-500" dir="ltr">
                                +966
                            </div>
                            <input type="text" name="phone" value="{{ old('phone', $member->phone) }}" placeholder="5XXXXXXXX" class="w-full bg-white border border-gray-200 rounded-2xl py-3.5 pr-20 pl-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm" dir="ltr" maxlength="9" inputmode="numeric" pattern="5[0-9]{8}" oninput="let val = this.value.replace(/[^0-9]/g, ''); if(val.startsWith('0')) { val = val.substring(1); } this.value = val.slice(0,9);" required>
                        </div>
                        @error('phone') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- 4. Email Address -->
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', $member->email) }}" placeholder="أدخل البريد الإلكتروني" class="w-full bg-white border border-gray-200 rounded-2xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required>
                        @error('email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($type === 'representative')
                    <!-- 5. Commission Rate -->
                    <div id="commission-wrap">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">نسبة العموله</label>
                        <div class="relative flex items-center">
                            <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $member->commission_rate) }}" placeholder="أدخل نسبة العموله" class="w-full bg-white border border-gray-200 rounded-2xl py-3.5 pr-4 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                            <div class="absolute left-0 top-0 bottom-0 px-4 bg-gray-50 border-r border-gray-200 flex items-center justify-center rounded-l-2xl font-bold text-gray-500">
                                %
                            </div>
                        </div>
                        @error('commission_rate') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- Action buttons -->
                    <div class="flex justify-start gap-4 pt-6 border-t border-gray-100">
                        <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-black py-3.5 px-10 rounded-2xl transition-all text-sm shadow-md shadow-blue-100">
                            حفظ التعديلات
                        </button>
                        <a href="{{ route('team.index', ['tab' => $type]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-3.5 px-10 rounded-2xl transition-all text-sm">
                            إلغاء
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatar-preview-img');
                const text = document.getElementById('avatar-preview-text');
                img.src = e.target.result;
                img.classList.remove('hidden');
                if (text) {
                    text.classList.add('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
